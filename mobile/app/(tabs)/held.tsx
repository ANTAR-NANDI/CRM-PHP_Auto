import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { FlatList, Pressable, RefreshControl, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { api, apiErrorMessage } from '@/api/client';
import { EmptyState } from '@/components/EmptyState';
import { PageHeader } from '@/components/PageHeader';
import { ScreenLoader } from '@/components/ScreenLoader';
import { formatBangladeshDateTime } from '@/lib/datetime';
import { colors } from '@/theme/colors';

type HeldOrder = { id: number; order_number: string; customer_name: string; items_count: number; created_at: string; note: string | null };

export default function HeldOrdersScreen() {
  const router = useRouter();
  const orders = useQuery({
    queryKey: ['held-orders'],
    queryFn: async () => (await api.get<{ data: HeldOrder[] }>('/pos/held-orders')).data.data,
    refetchOnMount: 'always',
  });

  if (orders.isLoading) return <ScreenLoader label="Loading held sales…" />;

  return <View style={styles.screen}>
    <PageHeader title="Held sales" subtitle="Saved carts waiting to be completed" />
    {orders.isError ? <View style={styles.center}><EmptyState title="Could not load held sales" message={apiErrorMessage(orders.error)} icon="cloud-offline-outline" /><Pressable style={styles.retry} onPress={() => orders.refetch()}><Text style={styles.retryText}>Try again</Text></Pressable></View> : <FlatList
      data={orders.data ?? []}
      keyExtractor={(item) => item.id.toString()}
      contentContainerStyle={styles.list}
      refreshControl={<RefreshControl refreshing={orders.isRefetching} onRefresh={orders.refetch} colors={[colors.primary]} tintColor={colors.primary} />}
      ListEmptyComponent={<EmptyState title="No held sales" message="Use Hold sale during checkout to save a cart for later." icon="pause-circle-outline" />}
      renderItem={({ item }) => <View style={styles.card}>
        <View style={styles.row}><View style={styles.orderIcon}><Ionicons name="pause" size={19} color={colors.primary} /></View><View style={styles.info}><Text style={styles.number}>{item.order_number}</Text><Text style={styles.meta}>{item.customer_name} · {item.items_count} medicine{item.items_count === 1 ? '' : 's'}</Text></View></View>
        {item.note ? <Text style={styles.note} numberOfLines={1}>{item.note}</Text> : null}
        <View style={styles.footer}><Text style={styles.date}>{formatBangladeshDateTime(item.created_at)}</Text><Pressable style={styles.resume} onPress={() => router.push({ pathname: '/cart', params: { heldOrderId: item.id.toString() } })}><Text style={styles.resumeText}>Continue sale</Text><Ionicons name="arrow-forward" size={16} color={colors.surface} /></Pressable></View>
      </View>}
    />}
  </View>;
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.background }, list: { padding: 14, gap: 10, flexGrow: 1 }, center: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingBottom: 50 },
  card: { backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: 16, padding: 14 }, row: { flexDirection: 'row', alignItems: 'center', gap: 10 }, orderIcon: { width: 39, height: 39, borderRadius: 12, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.primarySoft }, info: { flex: 1 }, number: { color: colors.text, fontSize: 13, fontWeight: '900' }, meta: { color: colors.muted, fontSize: 11, marginTop: 3 }, note: { color: colors.muted, fontSize: 11, fontStyle: 'italic', marginTop: 11 }, footer: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 13 }, date: { color: '#9AA3AF', fontSize: 10 },
  resume: { minHeight: 36, flexDirection: 'row', alignItems: 'center', gap: 5, borderRadius: 10, backgroundColor: colors.primary, paddingHorizontal: 11 }, resumeText: { color: colors.surface, fontSize: 11, fontWeight: '900' }, retry: { marginTop: -38, backgroundColor: colors.primary, borderRadius: 10, paddingHorizontal: 20, paddingVertical: 10 }, retryText: { color: colors.surface, fontWeight: '800' },
});
