import { useCallback } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useFocusEffect } from 'expo-router';
import { FlatList, RefreshControl, StyleSheet, Text, View } from 'react-native';
import { api } from '@/api/client';
import { EmptyState } from '@/components/EmptyState';
import { PageHeader } from '@/components/PageHeader';
import { ScreenLoader } from '@/components/ScreenLoader';
import { colors } from '@/theme/colors';
import { useAuthStore } from '@/store/auth';
import { formatTaka } from '@/lib/currency';
import { formatBangladeshDateTime } from '@/lib/datetime';
import type { SaleSummary, SalesOverview } from '@/types/api';

export default function SalesScreen() {
  const user = useAuthStore((state) => state.user);
  const sales = useQuery({
    queryKey: ['sales-overview'],
    queryFn: async () => (await api.get<SalesOverview>('/sales')).data,
    // A cashier expects a new invoice to be visible whenever this tab opens.
    refetchOnMount: 'always',
  });

  // Tab screens stay mounted in Expo Router. Refetch on every visit so a sale
  // completed from the POS is immediately visible without logging in again.
  useFocusEffect(useCallback(() => {
    void sales.refetch();
  }, [sales.refetch]));

  if (sales.isLoading) return <ScreenLoader label="Loading your sales…" />;

  return (
    <View style={styles.screen}>
      <PageHeader title={['admin', 'manager'].includes(user?.primary_role ?? '') ? 'All Sales' : 'My Sales'} subtitle={['admin', 'manager'].includes(user?.primary_role ?? '') ? 'Salesperson totals and completed invoices' : 'Your completed invoices'} />
      <FlatList
        data={sales.data?.data ?? []}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={sales.isRefetching} onRefresh={sales.refetch} colors={[colors.primary]} tintColor={colors.primary} />}
        ListHeaderComponent={
          <View style={styles.summaryWrap}>
            {['admin', 'manager'].includes(user?.primary_role ?? '') && <Text style={styles.summaryTitle}>Sales by salesperson</Text>}
            {(sales.data?.meta.salespeople ?? []).map((entry) => (
              <View key={entry.employee.id} style={styles.summaryRow}>
                <View><Text style={styles.employee}>{entry.employee.name}</Text><Text style={styles.employeeMeta}>{entry.sales_count} invoices</Text></View>
                <Text style={styles.summaryTotal}>৳{formatTaka(entry.total_sales)}</Text>
              </View>
            ))}
          </View>
        }
        ListEmptyComponent={<EmptyState title="No sales yet" message="Completed POS sales will appear here." icon="receipt-outline" />}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <View style={styles.row}>
              <Text style={styles.invoice}>{item.invoice_number}</Text>
              <Text style={styles.total}>৳{formatTaka(item.total)}</Text>
            </View>
            <View style={styles.row}>
              <Text style={styles.meta}>{item.employee?.name ?? 'Unknown employee'} · {item.items_count} items · {item.customer_type}</Text>
              <Text style={[styles.status, item.due > 0 && styles.due]}> {item.due > 0 ? `Due ৳${formatTaka(item.due)}` : 'Paid'} </Text>
            </View>
            <Text style={styles.date}>{formatBangladeshDateTime(item.sold_at)}</Text>
          </View>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.background },
  list: { padding: 14, gap: 10, flexGrow: 1 },
  summaryWrap: { gap: 8, marginBottom: 8 },
  summaryTitle: { color: colors.text, fontSize: 13, fontWeight: '900', marginBottom: 2 },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', backgroundColor: colors.primarySoft, borderRadius: 12, paddingHorizontal: 13, paddingVertical: 10 },
  employee: { color: colors.text, fontSize: 12, fontWeight: '800' },
  employeeMeta: { color: colors.muted, fontSize: 10, marginTop: 2 },
  summaryTotal: { color: colors.primaryDark, fontSize: 13, fontWeight: '900' },
  card: { backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: 16, padding: 15 },
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: 10 },
  invoice: { flex: 1, color: colors.text, fontSize: 14, fontWeight: '800' },
  total: { color: colors.primaryDark, fontSize: 16, fontWeight: '900' },
  meta: { color: colors.muted, fontSize: 11, marginTop: 10, textTransform: 'capitalize' },
  status: { color: colors.primaryDark, backgroundColor: colors.primarySoft, fontSize: 10, fontWeight: '700', borderRadius: 10, overflow: 'hidden' },
  due: { color: colors.warning, backgroundColor: '#FFF0E5' },
  date: { color: '#9AA3AF', fontSize: 10, marginTop: 8 },
});
