import { Ionicons } from '@expo/vector-icons';
import { useQuery } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Pressable, SafeAreaView, ScrollView, StyleSheet, Text, View } from 'react-native';
import { api } from '@/api/client';
import { ScreenLoader } from '@/components/ScreenLoader';
import { colors } from '@/theme/colors';
import { formatTaka } from '@/lib/currency';
import { formatBangladeshDateTime } from '@/lib/datetime';
import type { SaleDetail } from '@/types/api';

export default function SaleSuccessScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();
  const sale = useQuery({
    queryKey: ['sale', id],
    queryFn: async () => (await api.get<{ data: SaleDetail }>(`/sales/${id}`)).data.data,
    enabled: Boolean(id),
  });

  if (sale.isLoading) return <ScreenLoader label="Preparing your receipt…" />;

  if (!sale.data) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <View style={styles.failed}><Ionicons name="alert-circle-outline" size={48} color={colors.danger} /><Text style={styles.failedTitle}>Receipt unavailable</Text><Pressable style={styles.newSale} onPress={() => router.replace('/(tabs)')}><Text style={styles.newSaleText}>Return to POS</Text></Pressable></View>
      </SafeAreaView>
    );
  }

  const receipt = sale.data;
  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.successIcon}><Ionicons name="checkmark" size={48} color={colors.surface} /></View>
        <Text style={styles.title}>Sale completed</Text>
        <Text style={styles.subtitle}>Stock and employee sales have been updated successfully.</Text>

        <View style={styles.receipt}>
          <View style={styles.receiptHeader}><Text style={styles.shopName}>PharmaPOS</Text><Text style={styles.invoice}>{receipt.invoice_number}</Text></View>
          <ReceiptInfo label="Date" value={formatBangladeshDateTime(receipt.sold_at)} />
          <ReceiptInfo label="Employee" value={receipt.employee.name} />
          <ReceiptInfo label="Customer" value={receipt.customer?.name ?? 'Walking customer'} />
          <View style={styles.divider} />
          {receipt.items.map((item) => (
            <View key={item.id} style={styles.item}>
              <View style={styles.itemInfo}><Text style={styles.itemName}>{item.product_name}</Text><Text style={styles.itemMeta}>{item.quantity} {item.sale_unit}{item.quantity > 1 ? 's' : ''} × ৳{formatTaka(item.unit_price)}</Text></View>
              <Text style={styles.itemTotal}>৳{formatTaka(item.line_total)}</Text>
            </View>
          ))}
          <View style={styles.dashedDivider} />
          <ReceiptInfo label="Subtotal" value={`৳${formatTaka(receipt.subtotal)}`} />
          <ReceiptInfo label="Discount" value={`-৳${formatTaka(receipt.discount)}`} />
          <View style={styles.totalRow}><Text style={styles.totalLabel}>Total</Text><Text style={styles.total}>৳{formatTaka(receipt.total)}</Text></View>
          <View style={styles.paymentBox}>
            <ReceiptInfo label="Payment method" value={receipt.payment_method.replace('_', ' ')} />
            <ReceiptInfo label="Paid" value={`৳${formatTaka(receipt.paid)}`} />
            {receipt.due > 0 ? <ReceiptInfo label="Due" value={`৳${formatTaka(receipt.due)}`} /> : <ReceiptInfo label="Status" value="Paid" />}
            {receipt.change > 0 ? <ReceiptInfo label="Change" value={`৳${formatTaka(receipt.change)}`} /> : null}
          </View>
        </View>

        <View style={styles.actions}>
          <Pressable style={styles.newSale} onPress={() => router.replace('/(tabs)')}><Ionicons name="cart-outline" size={21} color={colors.surface} /><Text style={styles.newSaleText}>New sale</Text></Pressable>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

function ReceiptInfo({ label, value }: { label: string; value: string }) {
  return <View style={styles.infoRow}><Text style={styles.infoLabel}>{label}</Text><Text style={styles.infoValue}>{value}</Text></View>;
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: '#EFF9F6' }, content: { alignItems: 'center', padding: 18, paddingBottom: 35 },
  successIcon: { width: 82, height: 82, borderRadius: 41, backgroundColor: colors.primary, alignItems: 'center', justifyContent: 'center', marginTop: 16, shadowColor: colors.primaryDark, shadowOpacity: 0.25, shadowRadius: 15, elevation: 6 },
  title: { color: colors.primaryDark, fontSize: 25, fontWeight: '900', marginTop: 14 }, subtitle: { color: colors.muted, fontSize: 12, lineHeight: 18, textAlign: 'center', marginTop: 4, marginBottom: 19, maxWidth: 300 },
  receipt: { alignSelf: 'stretch', backgroundColor: colors.surface, borderRadius: 20, padding: 17, borderWidth: 1, borderColor: colors.border, shadowColor: '#15372F', shadowOpacity: 0.08, shadowRadius: 14, elevation: 3 },
  receiptHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }, shopName: { color: colors.primary, fontSize: 19, fontWeight: '900' }, invoice: { color: colors.text, fontSize: 10, fontWeight: '800' },
  infoRow: { flexDirection: 'row', justifyContent: 'space-between', gap: 15, marginBottom: 7 }, infoLabel: { color: colors.muted, fontSize: 11 }, infoValue: { flexShrink: 1, color: colors.text, fontSize: 11, fontWeight: '700', textAlign: 'right', textTransform: 'capitalize' },
  divider: { height: 1, backgroundColor: colors.border, marginVertical: 10 }, dashedDivider: { height: 1, borderTopWidth: 1, borderStyle: 'dashed', borderColor: '#C8D1CE', marginVertical: 11 },
  item: { flexDirection: 'row', justifyContent: 'space-between', gap: 12, marginBottom: 11 }, itemInfo: { flex: 1 }, itemName: { color: colors.text, fontSize: 12, fontWeight: '800' }, itemMeta: { color: colors.muted, fontSize: 10, marginTop: 3, textTransform: 'capitalize' }, itemTotal: { color: colors.text, fontSize: 12, fontWeight: '800' },
  totalRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 4 }, totalLabel: { color: colors.text, fontSize: 17, fontWeight: '900' }, total: { color: colors.primary, fontSize: 22, fontWeight: '900' },
  paymentBox: { backgroundColor: colors.primarySoft, borderRadius: 13, padding: 11, paddingBottom: 4, marginTop: 14 },
  actions: { alignSelf: 'stretch', flexDirection: 'row', marginTop: 16 },
  newSale: { flex: 1, minHeight: 57, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 7, backgroundColor: colors.primary, borderRadius: 14, paddingHorizontal: 15 }, newSaleText: { color: colors.surface, fontSize: 13, fontWeight: '900' },
  failed: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 25 }, failedTitle: { color: colors.text, fontSize: 20, fontWeight: '900', marginVertical: 15 },
});
