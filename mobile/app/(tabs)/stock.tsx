import { useQuery } from '@tanstack/react-query';
import { FlatList, RefreshControl, StyleSheet, Text, View } from 'react-native';
import { api } from '@/api/client';
import { EmptyState } from '@/components/EmptyState';
import { PageHeader } from '@/components/PageHeader';
import { ScreenLoader } from '@/components/ScreenLoader';
import { colors } from '@/theme/colors';
import type { PaginatedResponse, Product } from '@/types/api';

export default function StockScreen() {
  const stock = useQuery({
    queryKey: ['stock'],
    queryFn: async () => (await api.get<PaginatedResponse<Product>>('/stock', { params: { per_page: 50 } })).data,
  });

  if (stock.isLoading) return <ScreenLoader label="Checking current stock…" />;

  return (
    <View style={styles.screen}>
      <PageHeader title="Stock" subtitle="Live sellable stock, excluding expired batches" />
      <FlatList
        data={stock.data?.data ?? []}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={stock.isRefetching} onRefresh={stock.refetch} colors={[colors.primary]} />}
        ListEmptyComponent={<EmptyState title="No sellable stock" message="Receive a purchase from the web admin panel first." icon="file-tray-outline" />}
        renderItem={({ item }) => (
          <View style={styles.row}>
            <View style={styles.icon}><Text style={styles.initial}>{item.name[0]}</Text></View>
            <View style={styles.info}>
              <Text style={styles.name}>{item.name}</Text>
              <Text style={styles.meta}>{item.strip_stock} strips · {item.piece_stock} pieces</Text>
            </View>
            <Text style={[styles.level, item.is_low_stock && styles.low]}>{item.is_low_stock ? 'LOW' : 'OK'}</Text>
          </View>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.background },
  list: { padding: 14, gap: 9, flexGrow: 1 },
  row: { minHeight: 77, flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: 15, padding: 12 },
  icon: { width: 46, height: 46, borderRadius: 13, backgroundColor: colors.primarySoft, alignItems: 'center', justifyContent: 'center' },
  initial: { color: colors.primary, fontSize: 19, fontWeight: '900' },
  info: { flex: 1 },
  name: { color: colors.text, fontSize: 14, fontWeight: '800' },
  meta: { color: colors.muted, fontSize: 11, marginTop: 4 },
  level: { color: colors.primaryDark, backgroundColor: colors.primarySoft, paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8, fontSize: 9, fontWeight: '900', overflow: 'hidden' },
  low: { color: colors.warning, backgroundColor: '#FFF0E5' },
});
