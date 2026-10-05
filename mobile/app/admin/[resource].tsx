import { useMemo, useState } from 'react';
import { Alert, Pressable, ScrollView, StyleSheet, Switch, Text, TextInput, View } from 'react-native';
import { Stack, useLocalSearchParams } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api, apiErrorMessage } from '@/api/client';
import { EmptyState } from '@/components/EmptyState';
import { ScreenLoader } from '@/components/ScreenLoader';
import { colors } from '@/theme/colors';

const resources = ['suppliers', 'brands', 'generic-names', 'customers'] as const;
type Resource = (typeof resources)[number];

type MasterItem = {
  id: number;
  name: string;
  phone?: string | null;
  email?: string | null;
  customer_type?: 'retail' | 'wholesale';
  is_active: boolean;
};

const labels: Record<Resource, string> = {
  suppliers: 'Suppliers',
  brands: 'Brands',
  'generic-names': 'Generic Names',
  customers: 'Customers',
};

function isResource(value: string | string[] | undefined): value is Resource {
  return typeof value === 'string' && resources.includes(value as Resource);
}

export default function MasterDataScreen() {
  const params = useLocalSearchParams<{ resource?: string }>();
  const resource = isResource(params.resource) ? params.resource : null;
  const queryClient = useQueryClient();
  const [editingId, setEditingId] = useState<number | null>(null);
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [customerType, setCustomerType] = useState<'retail' | 'wholesale'>('retail');
  const [isActive, setIsActive] = useState(true);
  const [formError, setFormError] = useState('');

  const queryKey = useMemo(() => ['admin-master-data', resource], [resource]);
  const items = useQuery({
    queryKey,
    enabled: resource !== null,
    queryFn: async () => (await api.get<{ data: MasterItem[] }>(`/admin/master-data/${resource}`)).data.data,
  });

  function resetForm() {
    setEditingId(null);
    setName('');
    setPhone('');
    setEmail('');
    setCustomerType('retail');
    setIsActive(true);
    setFormError('');
  }

  function edit(item: MasterItem) {
    setEditingId(item.id);
    setName(item.name);
    setPhone(item.phone ?? '');
    setEmail(item.email ?? '');
    setCustomerType(item.customer_type ?? 'retail');
    setIsActive(item.is_active);
    setFormError('');
  }

  const save = useMutation({
    mutationFn: async () => {
      if (!resource) throw new Error('Invalid module.');
      const payload = {
        name: name.trim(),
        phone: phone.trim() || null,
        email: email.trim() || null,
        customer_type: customerType,
        is_active: isActive,
      };
      if (editingId) return api.put(`/admin/master-data/${resource}/${editingId}`, payload);
      return api.post(`/admin/master-data/${resource}`, payload);
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey });
      resetForm();
    },
    onError: (error) => setFormError(apiErrorMessage(error)),
  });

  const remove = useMutation({
    mutationFn: async (id: number) => api.delete(`/admin/master-data/${resource}/${id}`),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey });
      if (editingId) resetForm();
    },
    onError: (error) => Alert.alert('Could not delete', apiErrorMessage(error)),
  });

  function confirmDelete(item: MasterItem) {
    Alert.alert(
      `Delete ${item.name}?`,
      'This action cannot be undone. Records already used in sales or purchases may be protected.',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Delete', style: 'destructive', onPress: () => remove.mutate(item.id) },
      ],
    );
  }

  if (!resource) {
    return <EmptyState title="Module not found" message="Open a valid module from the Admin Workspace." icon="alert-circle-outline" />;
  }

  if (items.isLoading) return <ScreenLoader label={`Loading ${labels[resource].toLowerCase()}…`} />;

  return (
    <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
      <Stack.Screen options={{ title: labels[resource] }} />

      <View style={styles.formCard}>
        <View style={styles.formHeading}>
          <View>
            <Text style={styles.title}>{editingId ? `Edit ${labels[resource].slice(0, -1)}` : `Add ${labels[resource].slice(0, -1)}`}</Text>
            <Text style={styles.subtitle}>{editingId ? 'Update the selected record.' : 'Create a new active record.'}</Text>
          </View>
          {editingId ? <Pressable onPress={resetForm}><Text style={styles.cancelText}>Cancel edit</Text></Pressable> : null}
        </View>

        <Text style={styles.label}>Name *</Text>
        <TextInput value={name} onChangeText={setName} style={styles.input} placeholder="Enter name" placeholderTextColor={colors.muted} />

        {(resource === 'suppliers' || resource === 'customers') && (
          <>
            <Text style={styles.label}>Phone</Text>
            <TextInput value={phone} onChangeText={setPhone} style={styles.input} placeholder="Enter phone number" keyboardType="phone-pad" placeholderTextColor={colors.muted} />
            <Text style={styles.label}>Email</Text>
            <TextInput value={email} onChangeText={setEmail} style={styles.input} placeholder="Enter email address" keyboardType="email-address" autoCapitalize="none" placeholderTextColor={colors.muted} />
          </>
        )}

        {resource === 'customers' && (
          <>
            <Text style={styles.label}>Customer type</Text>
            <View style={styles.segment}>
              {(['retail', 'wholesale'] as const).map((type) => (
                <Pressable key={type} style={[styles.segmentButton, customerType === type && styles.segmentButtonActive]} onPress={() => setCustomerType(type)}>
                  <Text style={[styles.segmentText, customerType === type && styles.segmentTextActive]}>{type}</Text>
                </Pressable>
              ))}
            </View>
          </>
        )}

        <View style={styles.activeRow}>
          <View><Text style={styles.activeTitle}>Active</Text><Text style={styles.activeHint}>Available for pharmacy transactions</Text></View>
          <Switch value={isActive} onValueChange={setIsActive} trackColor={{ false: colors.border, true: colors.primarySoft }} thumbColor={isActive ? colors.primary : colors.muted} />
        </View>

        {formError ? <Text style={styles.error}>{formError}</Text> : null}
        <Pressable disabled={!name.trim() || save.isPending} onPress={() => save.mutate()} style={[styles.saveButton, (!name.trim() || save.isPending) && styles.buttonDisabled]}>
          <Text style={styles.saveText}>{save.isPending ? 'Saving…' : editingId ? 'Update' : 'Save'}</Text>
        </Pressable>
      </View>

      <View style={styles.listHeading}>
        <View><Text style={styles.listTitle}>All {labels[resource]}</Text><Text style={styles.listCount}>{items.data?.length ?? 0} records</Text></View>
        <Pressable onPress={() => items.refetch()}><Text style={styles.refresh}>Refresh</Text></Pressable>
      </View>

      {items.isError ? (
        <EmptyState title={`Could not load ${labels[resource].toLowerCase()}`} message={apiErrorMessage(items.error)} icon="cloud-offline-outline" />
      ) : items.data?.length ? items.data.map((item) => (
        <View key={item.id} style={styles.itemCard}>
          <View style={styles.itemMain}>
            <View style={styles.itemTitleRow}><Text style={styles.itemName}>{item.name}</Text><View style={[styles.status, !item.is_active && styles.statusInactive]}><Text style={[styles.statusText, !item.is_active && styles.statusTextInactive]}>{item.is_active ? 'Active' : 'Inactive'}</Text></View></View>
            {item.customer_type ? <Text style={styles.itemMeta}>{item.customer_type} customer</Text> : null}
            {item.phone ? <Text style={styles.itemMeta}>{item.phone}</Text> : null}
            {item.email ? <Text style={styles.itemMeta}>{item.email}</Text> : null}
          </View>
          <View style={styles.actions}>
            <Pressable style={styles.editButton} onPress={() => edit(item)}><Text style={styles.editText}>Edit</Text></Pressable>
            <Pressable style={styles.deleteButton} onPress={() => confirmDelete(item)}><Text style={styles.deleteText}>Delete</Text></Pressable>
          </View>
        </View>
      )) : (
        <EmptyState title={`No ${labels[resource].toLowerCase()} yet`} message="Use the form above to add the first record." icon="file-tray-outline" />
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  content: { padding: 14, paddingBottom: 36, gap: 12 },
  formCard: { backgroundColor: colors.surface, borderRadius: 18, borderWidth: 1, borderColor: colors.border, padding: 16 },
  formHeading: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 15 },
  title: { color: colors.text, fontWeight: '900', fontSize: 19 },
  subtitle: { color: colors.muted, fontSize: 11, marginTop: 3 },
  cancelText: { color: colors.danger, fontWeight: '800', fontSize: 11 },
  label: { color: colors.text, fontWeight: '800', fontSize: 12, marginBottom: 6, marginTop: 10 },
  input: { borderWidth: 1, borderColor: colors.border, borderRadius: 12, backgroundColor: colors.background, color: colors.text, paddingHorizontal: 13, paddingVertical: 11 },
  segment: { flexDirection: 'row', borderWidth: 1, borderColor: colors.border, borderRadius: 12, padding: 3, backgroundColor: colors.background },
  segmentButton: { flex: 1, alignItems: 'center', paddingVertical: 9, borderRadius: 9 },
  segmentButtonActive: { backgroundColor: colors.primary },
  segmentText: { color: colors.muted, fontWeight: '800', textTransform: 'capitalize', fontSize: 12 },
  segmentTextActive: { color: '#FFFFFF' },
  activeRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 16, paddingVertical: 5 },
  activeTitle: { color: colors.text, fontWeight: '800', fontSize: 13 },
  activeHint: { color: colors.muted, fontSize: 10, marginTop: 2 },
  error: { color: colors.danger, fontSize: 11, marginTop: 9 },
  saveButton: { backgroundColor: colors.primary, borderRadius: 12, alignItems: 'center', paddingVertical: 13, marginTop: 13 },
  buttonDisabled: { opacity: 0.45 },
  saveText: { color: '#FFFFFF', fontWeight: '900', fontSize: 13 },
  listHeading: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 8, paddingHorizontal: 2 },
  listTitle: { color: colors.text, fontWeight: '900', fontSize: 16 },
  listCount: { color: colors.muted, fontSize: 10, marginTop: 2 },
  refresh: { color: colors.primary, fontWeight: '800', fontSize: 12 },
  itemCard: { backgroundColor: colors.surface, borderRadius: 15, borderWidth: 1, borderColor: colors.border, padding: 14 },
  itemMain: { flex: 1 },
  itemTitleRow: { flexDirection: 'row', alignItems: 'center', gap: 8, flexWrap: 'wrap' },
  itemName: { color: colors.text, fontWeight: '900', fontSize: 14 },
  itemMeta: { color: colors.muted, fontSize: 11, marginTop: 3, textTransform: 'capitalize' },
  status: { backgroundColor: colors.primarySoft, borderRadius: 20, paddingHorizontal: 8, paddingVertical: 3 },
  statusInactive: { backgroundColor: '#FDECEF' },
  statusText: { color: colors.primaryDark, fontWeight: '800', fontSize: 9 },
  statusTextInactive: { color: colors.danger },
  actions: { flexDirection: 'row', gap: 8, marginTop: 12 },
  editButton: { flex: 1, borderRadius: 10, backgroundColor: colors.primarySoft, alignItems: 'center', paddingVertical: 9 },
  editText: { color: colors.primaryDark, fontWeight: '900', fontSize: 11 },
  deleteButton: { flex: 1, borderRadius: 10, backgroundColor: '#FDECEF', alignItems: 'center', paddingVertical: 9 },
  deleteText: { color: colors.danger, fontWeight: '900', fontSize: 11 },
});
