import { useEffect, useMemo, useRef, useState } from 'react';
import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import {
  ActivityIndicator, Alert, FlatList, KeyboardAvoidingView, Modal, Platform, Pressable,
  SafeAreaView, ScrollView, StyleSheet, Text, TextInput, View,
} from 'react-native';
import { api, apiErrorMessage } from '@/api/client';
import { EmptyState } from '@/components/EmptyState';
import { availableUnits, cartLineTotal } from '@/lib/pricing';
import { formatTaka, roundTaka } from '@/lib/currency';
import { cartSubtotal, useCartStore, type CartItem } from '@/store/cart';
import { colors } from '@/theme/colors';
import type { Customer, SaleDetail } from '@/types/api';

type CustomerType = 'walking' | 'retail' | 'wholesale';
type PaymentMethod = 'cash' | 'card' | 'mobile_banking';
type HeldOrderDetail = { id: number; customer_type: CustomerType; customer: Customer | null; discount: number; paid: number; payment_method: PaymentMethod; items: Array<{ sale_unit: CartItem['saleUnit']; quantity: number; product: CartItem['product'] }> };

export default function CartScreen() {
  const router = useRouter();
  const { heldOrderId } = useLocalSearchParams<{ heldOrderId?: string }>();
  const queryClient = useQueryClient();
  const items = useCartStore((state) => state.items);
  const setQuantity = useCartStore((state) => state.setQuantity);
  const removeItem = useCartStore((state) => state.removeItem);
  const clear = useCartStore((state) => state.clear);
  const replaceItems = useCartStore((state) => state.replaceItems);
  const [customerType, setCustomerType] = useState<CustomerType>('walking');
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [customerModal, setCustomerModal] = useState(false);
  const [discountInput, setDiscountInput] = useState('0');
  const [paidInput, setPaidInput] = useState('');
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cash');
  const [error, setError] = useState('');
  const [resumedOrderId, setResumedOrderId] = useState<number | null>(null);
  const hydratedHeldOrder = useRef<number | null>(null);
  const skipCustomerReset = useRef(false);

  const subtotal = cartSubtotal(items);
  const discount = Math.min(Math.max(roundTaka(discountInput), 0), subtotal);
  const total = Math.max(0, roundTaka(subtotal - discount));
  const paid = Math.max(roundTaka(paidInput), 0);

  const customers = useQuery({
    queryKey: ['customers', customerType],
    enabled: customerType !== 'walking',
    queryFn: async () => (await api.get<{ data: Customer[] }>('/customers', { params: { type: customerType } })).data.data,
  });

  const heldOrder = useQuery({
    queryKey: ['held-order', heldOrderId],
    enabled: Boolean(heldOrderId),
    queryFn: async () => (await api.get<{ data: HeldOrderDetail }>(`/pos/held-orders/${heldOrderId}`)).data.data,
  });

  useEffect(() => {
    if (skipCustomerReset.current) {
      skipCustomerReset.current = false;
      return;
    }
    setCustomer(null);
    setPaidInput(customerType === 'walking' ? formatTaka(total) : '');
  }, [customerType]);

  useEffect(() => {
    if (!heldOrder.data || hydratedHeldOrder.current === heldOrder.data.id) return;
    hydratedHeldOrder.current = heldOrder.data.id;
    replaceItems(heldOrder.data.items.map((item) => ({ product: item.product, saleUnit: item.sale_unit, quantity: item.quantity })));
    if (heldOrder.data.customer_type !== customerType) {
      skipCustomerReset.current = true;
      setCustomerType(heldOrder.data.customer_type);
    }
    setCustomer(heldOrder.data.customer);
    setDiscountInput(formatTaka(heldOrder.data.discount));
    setPaidInput(formatTaka(heldOrder.data.paid));
    setPaymentMethod(heldOrder.data.payment_method);
    setResumedOrderId(heldOrder.data.id);
  }, [customerType, heldOrder.data, replaceItems]);

  const checkout = useMutation({
    mutationFn: async () => {
      const response = await api.post<{ data: SaleDetail }>('/sales', {
        items: items.map((item) => ({ product_id: item.product.id, sale_unit: item.saleUnit, quantity: item.quantity })),
        customer_type: customerType,
        customer_id: customer?.id ?? null,
        discount,
        paid,
        payment_method: paymentMethod,
        saved_order_id: resumedOrderId,
      });
      return response.data.data;
    },
    onSuccess: async (sale) => {
      clear();
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['products'] }),
        queryClient.invalidateQueries({ queryKey: ['stock'] }),
        queryClient.invalidateQueries({ queryKey: ['sales-overview'] }),
        queryClient.invalidateQueries({ queryKey: ['held-orders'] }),
      ]);
      // The Sales tab remains mounted while checkout/receipt screens are open.
      // Refresh its query in the background so the new invoice is ready as soon
      // as the cashier opens Sales.
      await queryClient.refetchQueries({ queryKey: ['sales-overview'], type: 'all' });
      router.replace({ pathname: '/sale-success', params: { id: sale.id.toString() } });
    },
    onError: (requestError) => setError(apiErrorMessage(requestError)),
  });

  const holdOrder = useMutation({
    mutationFn: async () => {
      const response = await api.post<{ data: { order_number: string } }>('/pos/held-orders', {
        items: items.map((item) => ({ product_id: item.product.id, sale_unit: item.saleUnit, quantity: item.quantity })),
        customer_type: customerType,
        customer_id: customer?.id ?? null,
        discount,
        paid,
        payment_method: paymentMethod,
      });
      return response.data.data;
    },
    onSuccess: (order) => {
      clear();
      queryClient.invalidateQueries({ queryKey: ['held-orders'] });
      Alert.alert('Sale held', `${order.order_number} is saved. Stock has not been deducted.`, [
        { text: 'Start new sale', onPress: () => router.replace('/(tabs)') },
      ]);
    },
    onError: (requestError) => setError(apiErrorMessage(requestError)),
  });

  const completeSale = () => {
    setError('');
    if (!items.length) return setError('Add at least one medicine to the cart.');
    if (customerType !== 'walking' && !customer) return setError(`Select a saved ${customerType} customer.`);
    if ((Number(discountInput) || 0) > subtotal) return setError('Discount cannot be greater than the subtotal.');
    checkout.mutate();
  };

  const holdCurrentSale = () => {
    setError('');
    if (!items.length) return setError('Add at least one medicine to the cart.');
    if (customerType !== 'walking' && !customer) return setError(`Select a saved ${customerType} customer.`);
    if ((Number(discountInput) || 0) > subtotal) return setError('Discount cannot be greater than the subtotal.');
    holdOrder.mutate();
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <Pressable style={styles.headerIcon} onPress={() => router.back()}><Ionicons name="arrow-back" size={23} color={colors.surface} /></Pressable>
        <View style={styles.headerText}><Text style={styles.headerTitle}>Cart & Checkout</Text><Text style={styles.headerSubtitle}>{items.length} medicine{items.length === 1 ? '' : 's'} selected</Text></View>
        <Pressable style={styles.headerIcon} onPress={clear} disabled={!items.length}><Ionicons name="trash-outline" size={21} color={items.length ? colors.surface : '#78B9AA'} /></Pressable>
      </View>

      {!items.length ? (
        <View style={styles.emptyWrap}>
          <EmptyState title="Your cart is empty" message="Return to POS and add a medicine by strip or single piece." icon="cart-outline" />
          <Pressable style={styles.primarySmall} onPress={() => router.replace('/(tabs)')}><Text style={styles.primarySmallText}>Browse medicines</Text></Pressable>
        </View>
      ) : (
        <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
          <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
            <Text style={styles.sectionTitle}>Cart items</Text>
            <View style={styles.items}>{items.map((item) => (
              <CartRow key={`${item.product.id}-${item.saleUnit}`} item={item} onQuantity={setQuantity} onRemove={removeItem} />
            ))}</View>
            <Pressable style={styles.addMoreButton} onPress={() => router.replace('/(tabs)')}>
              <Ionicons name="add-circle-outline" size={20} color={colors.primary} />
              <Text style={styles.addMoreText}>Add more medicines</Text>
            </Pressable>

            <Text style={styles.sectionTitle}>Customer type</Text>
            <View style={styles.segmented}>
              {(['walking', 'retail', 'wholesale'] as CustomerType[]).map((type) => (
                <Pressable key={type} style={[styles.segment, customerType === type && styles.segmentActive]} onPress={() => setCustomerType(type)}>
                  <Text style={[styles.segmentText, customerType === type && styles.segmentTextActive]}>{type === 'walking' ? 'Walk-in' : type}</Text>
                </Pressable>
              ))}
            </View>

            {customerType !== 'walking' ? (
              <Pressable style={styles.customerSelect} onPress={() => setCustomerModal(true)}>
                <View style={styles.customerIcon}><Ionicons name="person-outline" size={21} color={colors.primary} /></View>
                <View style={styles.customerInfo}>
                  <Text style={styles.fieldLabel}>{customer ? 'Selected customer' : `Choose ${customerType} customer`}</Text>
                  <Text style={styles.customerValue}>{customer ? `${customer.name}${customer.phone ? ` · ${customer.phone}` : ''}` : 'Required for credit and customer records'}</Text>
                </View>
                <Ionicons name="chevron-down" size={18} color={colors.muted} />
              </Pressable>
            ) : null}

            <Text style={styles.sectionTitle}>Payment</Text>
            <View style={styles.paymentOptions}>
              {([
                ['cash', 'cash-outline', 'Cash'],
                ['card', 'card-outline', 'Card'],
                ['mobile_banking', 'phone-portrait-outline', 'Mobile'],
              ] as const).map(([method, icon, label]) => (
                <Pressable key={method} style={[styles.payment, paymentMethod === method && styles.paymentActive]} onPress={() => setPaymentMethod(method)}>
                  <Ionicons name={icon} size={20} color={paymentMethod === method ? colors.primary : colors.muted} />
                  <Text style={[styles.paymentText, paymentMethod === method && styles.paymentTextActive]}>{label}</Text>
                </Pressable>
              ))}
            </View>

            <View style={styles.moneyInputs}>
              <View style={styles.moneyField}><Text style={styles.fieldLabel}>Discount (৳)</Text><TextInput value={discountInput} onChangeText={setDiscountInput} keyboardType="number-pad" selectTextOnFocus style={styles.moneyInput} /></View>
              <View style={styles.moneyField}><Text style={styles.fieldLabel}>Paid amount (৳)</Text><TextInput value={paidInput} onChangeText={setPaidInput} keyboardType="number-pad" placeholder="0" selectTextOnFocus style={styles.moneyInput} /></View>
            </View>

            <View style={styles.summary}>
              <SummaryRow label="Subtotal" value={subtotal} />
              <SummaryRow label="Discount" value={-discount} muted />
              <View style={styles.summaryDivider} />
              <SummaryRow label="Total" value={total} total />
              {customerType !== 'walking' ? <SummaryRow label="Due" value={Math.max(0, total - paid)} warning={paid < total} /> : null}
            </View>

            {error ? <View style={styles.error}><Ionicons name="alert-circle-outline" size={19} color={colors.danger} /><Text style={styles.errorText}>{error}</Text></View> : null}
            <Pressable style={({ pressed }) => [styles.holdButton, pressed && styles.pressed]} onPress={holdCurrentSale} disabled={checkout.isPending || holdOrder.isPending}>
              {holdOrder.isPending ? <ActivityIndicator color={colors.warning} /> : <><Ionicons name="pause-circle-outline" size={21} color={colors.warning} /><Text style={styles.holdText}>Hold sale</Text></>}
            </Pressable>
            <Pressable style={({ pressed }) => [styles.completeButton, pressed && styles.pressed]} onPress={completeSale} disabled={checkout.isPending || holdOrder.isPending}>
              {checkout.isPending ? <ActivityIndicator color={colors.surface} /> : <><Ionicons name="checkmark-circle-outline" size={22} color={colors.surface} /><Text style={styles.completeText}>Complete sale · ৳{formatTaka(total)}</Text></>}
            </Pressable>
          </ScrollView>
        </KeyboardAvoidingView>
      )}

      <CustomerModal visible={customerModal} customers={customers.data ?? []} loading={customers.isLoading} onClose={() => setCustomerModal(false)} onSelect={(value) => { setCustomer(value); setCustomerModal(false); }} />
    </SafeAreaView>
  );
}

function CartRow({ item, onQuantity, onRemove }: { item: CartItem; onQuantity: (id: number, unit: CartItem['saleUnit'], quantity: number) => void; onRemove: (id: number, unit: CartItem['saleUnit']) => void }) {
  const lineTotal = cartLineTotal(item.product, item.saleUnit, item.quantity);
  const max = availableUnits(item.product, item.saleUnit);
  return (
    <View style={styles.itemCard}>
      <View style={styles.productIcon}><Text style={styles.productInitial}>{item.product.name[0]}</Text></View>
      <View style={styles.itemBody}>
        <View style={styles.itemTop}><View style={styles.itemNameWrap}><Text style={styles.itemName} numberOfLines={1}>{item.product.name}</Text><Text style={styles.itemMeta}>{item.saleUnit} · max {max}</Text></View><Pressable hitSlop={10} onPress={() => onRemove(item.product.id, item.saleUnit)}><Ionicons name="close-circle" size={21} color="#B7BEC7" /></Pressable></View>
        <View style={styles.itemBottom}>
          <View style={styles.quantity}>
            <Pressable style={styles.quantityButton} onPress={() => item.quantity === 1 ? onRemove(item.product.id, item.saleUnit) : onQuantity(item.product.id, item.saleUnit, item.quantity - 1)}><Ionicons name="remove" size={17} color={colors.text} /></Pressable>
            <Text style={styles.quantityValue}>{item.quantity}</Text>
            <Pressable style={styles.quantityButton} onPress={() => onQuantity(item.product.id, item.saleUnit, item.quantity + 1)} disabled={item.quantity >= max}><Ionicons name="add" size={17} color={item.quantity >= max ? '#C8CDD3' : colors.primary} /></Pressable>
          </View>
          <Text style={styles.lineTotal}>৳{formatTaka(lineTotal)}</Text>
        </View>
      </View>
    </View>
  );
}

function SummaryRow({ label, value, total, muted, warning }: { label: string; value: number; total?: boolean; muted?: boolean; warning?: boolean }) {
  return <View style={styles.summaryRow}><Text style={[styles.summaryLabel, total && styles.summaryTotalLabel]}>{label}</Text><Text style={[styles.summaryValue, total && styles.summaryTotalValue, muted && styles.summaryMuted, warning && styles.summaryWarning]}>{value < 0 ? '-' : ''}৳{formatTaka(Math.abs(value))}</Text></View>;
}

function CustomerModal({ visible, customers, loading, onClose, onSelect }: { visible: boolean; customers: Customer[]; loading: boolean; onClose: () => void; onSelect: (customer: Customer) => void }) {
  const [search, setSearch] = useState('');
  const filtered = useMemo(() => customers.filter((customer) => `${customer.name} ${customer.phone ?? ''}`.toLowerCase().includes(search.toLowerCase())), [customers, search]);
  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
      <View style={styles.modalBackdrop}>
        <View style={styles.customerModal}>
          <View style={styles.modalHeader}><View><Text style={styles.modalTitle}>Select customer</Text><Text style={styles.modalHint}>Only active saved customers are shown</Text></View><Pressable onPress={onClose}><Ionicons name="close" size={24} color={colors.text} /></Pressable></View>
          <View style={styles.search}><Ionicons name="search-outline" size={19} color={colors.muted} /><TextInput value={search} onChangeText={setSearch} placeholder="Search name or phone" style={styles.searchInput} /></View>
          {loading ? <ActivityIndicator style={styles.customerLoader} color={colors.primary} /> : <FlatList data={filtered} keyExtractor={(item) => item.id.toString()} contentContainerStyle={styles.customerList} ListEmptyComponent={<EmptyState title="No customer found" message="Add the customer from the web admin panel first." icon="people-outline" />} renderItem={({ item }) => (
            <Pressable style={styles.customerRow} onPress={() => onSelect(item)}><View style={styles.customerAvatar}><Text style={styles.customerInitial}>{item.name[0]}</Text></View><View style={styles.customerInfo}><Text style={styles.customerName}>{item.name}</Text><Text style={styles.customerPhone}>{item.phone ?? 'No phone'} · Due ৳{formatTaka(item.due_balance)}</Text></View><Ionicons name="chevron-forward" size={18} color={colors.muted} /></Pressable>
          )} />}
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 }, safeArea: { flex: 1, backgroundColor: colors.background },
  header: { minHeight: 82, flexDirection: 'row', alignItems: 'center', backgroundColor: colors.primary, paddingHorizontal: 12, gap: 10 },
  headerIcon: { width: 42, height: 42, alignItems: 'center', justifyContent: 'center' }, headerText: { flex: 1 },
  headerTitle: { color: colors.surface, fontSize: 20, fontWeight: '900' }, headerSubtitle: { color: '#D7F3EC', fontSize: 10, marginTop: 2 },
  content: { padding: 14, paddingBottom: 35 }, sectionTitle: { color: colors.text, fontSize: 15, fontWeight: '900', marginTop: 5, marginBottom: 9 },
  items: { gap: 9, marginBottom: 18 }, itemCard: { flexDirection: 'row', gap: 11, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: 16, padding: 11 },
  addMoreButton: { minHeight: 48, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, borderWidth: 1.5, borderColor: colors.primary, borderRadius: 13, backgroundColor: colors.surface, marginTop: -7, marginBottom: 18 }, addMoreText: { color: colors.primary, fontSize: 13, fontWeight: '900' },
  productIcon: { width: 52, height: 62, borderRadius: 13, backgroundColor: colors.primarySoft, alignItems: 'center', justifyContent: 'center' }, productInitial: { color: colors.primary, fontSize: 22, fontWeight: '900' },
  itemBody: { flex: 1 }, itemTop: { flexDirection: 'row', alignItems: 'flex-start' }, itemNameWrap: { flex: 1 }, itemName: { color: colors.text, fontSize: 14, fontWeight: '800' }, itemMeta: { color: colors.muted, fontSize: 10, marginTop: 3, textTransform: 'capitalize' },
  itemBottom: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 11 }, quantity: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderColor: colors.border, borderRadius: 12, overflow: 'hidden' }, quantityButton: { width: 44, height: 42, alignItems: 'center', justifyContent: 'center', backgroundColor: '#F7FAF9' }, quantityValue: { minWidth: 44, textAlign: 'center', color: colors.text, fontSize: 16, fontWeight: '900' }, lineTotal: { color: colors.text, fontSize: 16, fontWeight: '900' },
  segmented: { flexDirection: 'row', padding: 3, borderRadius: 13, backgroundColor: '#E9EFED', marginBottom: 11 }, segment: { flex: 1, height: 39, borderRadius: 10, alignItems: 'center', justifyContent: 'center' }, segmentActive: { backgroundColor: colors.surface, shadowColor: '#20382F', shadowOpacity: 0.1, shadowRadius: 5, elevation: 2 }, segmentText: { color: colors.muted, fontSize: 11, fontWeight: '700', textTransform: 'capitalize' }, segmentTextActive: { color: colors.primaryDark },
  customerSelect: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: 14, padding: 11, marginBottom: 17 }, customerIcon: { width: 40, height: 40, borderRadius: 11, backgroundColor: colors.primarySoft, alignItems: 'center', justifyContent: 'center' }, customerInfo: { flex: 1 }, fieldLabel: { color: colors.muted, fontSize: 10, fontWeight: '600' }, customerValue: { color: colors.text, fontSize: 12, fontWeight: '700', marginTop: 3 },
  paymentOptions: { flexDirection: 'row', gap: 8, marginBottom: 12 }, payment: { flex: 1, height: 53, alignItems: 'center', justifyContent: 'center', gap: 3, borderWidth: 1, borderColor: colors.border, borderRadius: 12, backgroundColor: colors.surface }, paymentActive: { borderColor: colors.primary, backgroundColor: colors.primarySoft }, paymentText: { color: colors.muted, fontSize: 9, fontWeight: '700' }, paymentTextActive: { color: colors.primaryDark },
  moneyInputs: { flexDirection: 'row', gap: 9 }, moneyField: { flex: 1, borderWidth: 1, borderColor: colors.border, borderRadius: 13, backgroundColor: colors.surface, paddingHorizontal: 12, paddingTop: 9 }, moneyInput: { height: 37, color: colors.text, fontSize: 15, fontWeight: '800', paddingVertical: 0 }, readonly: { color: colors.muted },
  summary: { backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: 16, padding: 15, gap: 9, marginTop: 13 }, summaryRow: { flexDirection: 'row', justifyContent: 'space-between' }, summaryLabel: { color: colors.muted, fontSize: 12 }, summaryValue: { color: colors.text, fontSize: 12, fontWeight: '700' }, summaryDivider: { height: 1, backgroundColor: colors.border }, summaryTotalLabel: { color: colors.text, fontSize: 17, fontWeight: '900' }, summaryTotalValue: { color: colors.primary, fontSize: 20, fontWeight: '900' }, summaryMuted: { color: colors.muted }, summaryWarning: { color: colors.warning },
  error: { flexDirection: 'row', alignItems: 'flex-start', gap: 8, backgroundColor: '#FFF0F2', borderRadius: 11, padding: 11, marginTop: 12 }, errorText: { flex: 1, color: colors.danger, fontSize: 12, lineHeight: 17 },
  holdButton: { height: 51, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, borderWidth: 1.5, borderColor: colors.warning, borderRadius: 15, marginTop: 13, backgroundColor: '#FFF9E8' }, holdText: { color: '#A56A00', fontSize: 14, fontWeight: '900' },
  completeButton: { height: 57, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: colors.primary, borderRadius: 15, marginTop: 13 }, pressed: { opacity: 0.88 }, completeText: { color: colors.surface, fontSize: 15, fontWeight: '900' },
  emptyWrap: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingBottom: 70 }, primarySmall: { backgroundColor: colors.primary, borderRadius: 12, paddingHorizontal: 20, paddingVertical: 11, marginTop: -35 }, primarySmallText: { color: colors.surface, fontWeight: '800' },
  modalBackdrop: { flex: 1, backgroundColor: 'rgba(7, 17, 36, 0.5)', justifyContent: 'flex-end' }, customerModal: { height: '72%', backgroundColor: colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, paddingTop: 18 }, modalHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 18 }, modalTitle: { color: colors.text, fontSize: 19, fontWeight: '900' }, modalHint: { color: colors.muted, fontSize: 10, marginTop: 2 },
  search: { height: 48, flexDirection: 'row', alignItems: 'center', gap: 8, margin: 15, paddingHorizontal: 12, borderWidth: 1, borderColor: colors.border, borderRadius: 13, backgroundColor: '#FBFDFC' }, searchInput: { flex: 1, height: '100%', color: colors.text, fontSize: 13 }, customerLoader: { marginTop: 45 }, customerList: { paddingHorizontal: 15, paddingBottom: 25, gap: 8, flexGrow: 1 }, customerRow: { flexDirection: 'row', alignItems: 'center', gap: 11, borderWidth: 1, borderColor: colors.border, borderRadius: 14, padding: 10 }, customerAvatar: { width: 42, height: 42, borderRadius: 12, backgroundColor: colors.primarySoft, alignItems: 'center', justifyContent: 'center' }, customerInitial: { color: colors.primary, fontSize: 17, fontWeight: '900' }, customerName: { color: colors.text, fontSize: 13, fontWeight: '800' }, customerPhone: { color: colors.muted, fontSize: 10, marginTop: 3 },
});
