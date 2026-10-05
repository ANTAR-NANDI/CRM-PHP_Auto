import { useState } from 'react';
import { useRouter } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { Pressable, RefreshControl, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { api } from '@/api/client';
import { EmptyState } from '@/components/EmptyState';
import { PageHeader } from '@/components/PageHeader';
import { ScreenLoader } from '@/components/ScreenLoader';
import { colors } from '@/theme/colors';
import { formatTaka } from '@/lib/currency';
import { bangladeshDate } from '@/lib/datetime';
import type { AdminOverview, EmployeeSalesReport } from '@/types/api';

export default function AdminScreen() {
  const router = useRouter();
  const [from, setFrom] = useState(() => `${bangladeshDate().slice(0, 8)}01`);
  const [to, setTo] = useState(() => bangladeshDate());
  const overview = useQuery({ queryKey: ['admin-overview'], queryFn: async () => (await api.get<{ data: AdminOverview }>('/admin/overview')).data.data });
  const employeeSales = useQuery({ queryKey: ['employee-sales', from, to], queryFn: async () => (await api.get<{ data: EmployeeSalesReport }>('/admin/reports/employee-sales', { params: { from, to } })).data.data });
  if (overview.isLoading) return <ScreenLoader label="Loading admin workspace…" />;
  if (overview.isError || !overview.data) return <View style={styles.screen}><PageHeader title="Admin Workspace" subtitle="Reports and pharmacy modules" /><EmptyState title="Could not load admin data" message="Check your connection and try again." icon="cloud-offline-outline" /></View>;
  const { reports, modules } = overview.data;
  return <ScrollView style={styles.screen} contentContainerStyle={styles.content} refreshControl={<RefreshControl refreshing={overview.isRefetching} onRefresh={overview.refetch} colors={[colors.primary]} />}>
    <PageHeader title="Admin Workspace" subtitle="Reports and all pharmacy modules" />
    <Text style={styles.section}>Business reports</Text>
    <View style={styles.grid}>
      <Metric label="Today's sales" value={reports.today_sales} /><Metric label="Monthly sales" value={reports.monthly_sales} /><Metric label="Monthly profit" value={reports.monthly_profit} /><Metric label="Stock value" value={reports.stock_value} />
    </View>
    <Text style={styles.section}>Employee sales report</Text>
    <View style={styles.filters}><TextInput value={from} onChangeText={setFrom} style={styles.input} placeholder="YYYY-MM-DD" /><TextInput value={to} onChangeText={setTo} style={styles.input} placeholder="YYYY-MM-DD" /></View>
    <View style={styles.quickFilters}><Pressable onPress={() => { const today = bangladeshDate(); setFrom(today); setTo(today); }}><Text style={styles.filterText}>Today</Text></Pressable><Pressable onPress={() => { const end = new Date(); const start = new Date(); start.setDate(end.getDate() - 6); setFrom(bangladeshDate(start)); setTo(bangladeshDate(end)); }}><Text style={styles.filterText}>Last 7 days</Text></Pressable></View>
    {employeeSales.isLoading ? <Text style={styles.empty}>Loading employee sales…</Text> : employeeSales.data?.employees.map((item) => <View key={item.employee.id} style={styles.row}><View><Text style={styles.rowTitle}>{item.employee.name}</Text><Text style={styles.meta}>{item.invoice_count} invoices · Paid ৳{formatTaka(item.total_paid)} · Due ৳{formatTaka(item.total_due)}</Text></View><Text style={styles.total}>৳{formatTaka(item.total_sales)}</Text></View>)}
    <Text style={styles.section}>Manage modules</Text>
    <View style={styles.moduleLinks}>{['suppliers', 'brands', 'generic-names', 'customers'].map((module) => <Pressable key={module} style={styles.moduleLink} onPress={() => router.push({ pathname: '/admin/[resource]', params: { resource: module } })}><Text style={styles.moduleLinkText}>{module.replace('-', ' ')}</Text><Text style={styles.moduleArrow}>→</Text></Pressable>)}</View>
    <Text style={styles.section}>Module totals</Text>
    <View style={styles.modules}>{Object.entries(modules).map(([name, count]) => <View key={name} style={styles.module}><Text style={styles.moduleCount}>{count}</Text><Text style={styles.moduleName}>{name}</Text></View>)}</View>
    <Text style={styles.section}>Low-stock medicines ({reports.low_stock_count})</Text>
    {overview.data.low_stock.length ? overview.data.low_stock.map((item) => <View key={item.id} style={styles.row}><Text style={styles.rowTitle}>{item.name}</Text><Text style={styles.warning}>{item.stock} / {item.reorder_level}</Text></View>) : <Text style={styles.empty}>No low-stock medicines.</Text>}
    <Text style={styles.section}>Recent purchases</Text>
    {overview.data.recent_purchases.map((item) => <View key={item.invoice_number} style={styles.row}><View><Text style={styles.rowTitle}>{item.invoice_number}</Text><Text style={styles.meta}>{item.supplier ?? 'Unknown supplier'} · {item.purchased_at}</Text></View><Text style={styles.total}>৳{formatTaka(item.total)}</Text></View>)}
  </ScrollView>;
}
function Metric({ label, value }: { label: string; value: number }) { return <View style={styles.metric}><Text style={styles.metricValue}>৳{formatTaka(value)}</Text><Text style={styles.metricLabel}>{label}</Text></View>; }
const styles = StyleSheet.create({ screen: { flex: 1, backgroundColor: colors.background }, content: { paddingBottom: 26 }, section: { color: colors.text, fontSize: 14, fontWeight: '900', marginHorizontal: 14, marginTop: 18, marginBottom: 9 }, grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 9, paddingHorizontal: 14 }, metric: { width: '48%', backgroundColor: colors.surface, borderColor: colors.border, borderWidth: 1, borderRadius: 15, padding: 13 }, metricValue: { color: colors.primaryDark, fontSize: 18, fontWeight: '900' }, metricLabel: { color: colors.muted, fontSize: 10, marginTop: 4 }, filters: { flexDirection: 'row', gap: 8, paddingHorizontal: 14 }, input: { flex: 1, backgroundColor: colors.surface, borderColor: colors.border, borderWidth: 1, borderRadius: 10, paddingHorizontal: 11, paddingVertical: 9, color: colors.text, fontSize: 12 }, quickFilters: { flexDirection: 'row', gap: 18, paddingHorizontal: 16, paddingTop: 8 }, filterText: { color: colors.primary, fontSize: 11, fontWeight: '800' }, moduleLinks: { gap: 8, paddingHorizontal: 14 }, moduleLink: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: colors.surface, borderColor: colors.border, borderWidth: 1, borderRadius: 13, padding: 14 }, moduleLinkText: { color: colors.text, fontWeight: '800', textTransform: 'capitalize' }, moduleArrow: { color: colors.primary, fontWeight: '900' }, modules: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, paddingHorizontal: 14 }, module: { width: '31%', backgroundColor: colors.primarySoft, borderRadius: 12, padding: 11 }, moduleCount: { color: colors.primaryDark, fontSize: 17, fontWeight: '900' }, moduleName: { color: colors.muted, fontSize: 10, textTransform: 'capitalize', marginTop: 2 }, row: { marginHorizontal: 14, marginBottom: 8, padding: 13, borderRadius: 13, backgroundColor: colors.surface, borderColor: colors.border, borderWidth: 1, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }, rowTitle: { color: colors.text, fontSize: 12, fontWeight: '800' }, meta: { color: colors.muted, fontSize: 10, marginTop: 3 }, total: { color: colors.primaryDark, fontWeight: '900', fontSize: 12 }, warning: { color: colors.warning, fontSize: 12, fontWeight: '900' }, empty: { color: colors.muted, marginHorizontal: 14, fontSize: 12 } });
