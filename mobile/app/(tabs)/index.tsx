import { useEffect, useRef, useState } from 'react';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { StatusBar } from 'expo-status-bar';
import {
  ActivityIndicator, FlatList, Modal, Pressable, RefreshControl, SafeAreaView, ScrollView,
  StyleSheet, Text, TextInput, View,
} from 'react-native';
import { api, apiErrorMessage } from '@/api/client';
import { EmptyState } from '@/components/EmptyState';
import { colors } from '@/theme/colors';
import { cartSubtotal, useCartStore } from '@/store/cart';
import { formatTaka } from '@/lib/currency';
import type { PaginatedResponse, Product, SaleUnit } from '@/types/api';

const alphabet = ['All', ...'ABCDEFGHIJKLMNOPQRSTUVWXYZ'];

function MedicineCard({ product, pieceQuantity, stripQuantity, onAddPiece, onRemovePiece, onAddStrip, onRemoveStrip }: { product: Product; pieceQuantity: number; stripQuantity: number; onAddPiece: () => void; onRemovePiece: () => void; onAddStrip: () => void; onRemoveStrip: () => void }) {
  const hasStripStock = product.sell_by_strip && product.strip_price !== null && product.strip_stock > 0;
  const hasPieceStock = product.sell_by_piece && product.piece_price !== null && product.piece_stock > 0;
  return (
    <View style={styles.card}>
      <View style={styles.productInfo}>
        <View style={styles.medicineIcon}>
          <Text style={styles.medicineInitial}>{product.name.slice(0, 1).toUpperCase()}</Text>
        </View>
        <View style={styles.cardBody}>
          <Text style={styles.productName} numberOfLines={1}>{product.name}</Text>
          <Text style={styles.productDetail} numberOfLines={1}>{product.generic_name ?? 'Generic not set'}{product.brand ? ` · ${product.brand}` : ''}</Text>
          <View style={styles.cardFooter}>
            <View style={[styles.stockPill, product.is_low_stock && styles.lowStockPill]}>
              <Text style={[styles.stockText, product.is_low_stock && styles.lowStockText]}>
                {product.is_low_stock ? 'Low · ' : ''}{product.piece_stock} pcs
              </Text>
            </View>
          </View>
        </View>
      </View>
      <View style={styles.unitButtons}>
        <UnitCounter label="Piece" price={product.piece_price} quantity={pieceQuantity} enabled={hasPieceStock} onAdd={onAddPiece} onRemove={onRemovePiece} />
        <UnitCounter label="Strip" price={product.strip_price} quantity={stripQuantity} enabled={hasStripStock} onAdd={onAddStrip} onRemove={onRemoveStrip} strip />
      </View>
    </View>
  );
}

function UnitCounter({ label, price, quantity, enabled, onAdd, onRemove, strip }: { label: string; price: number | null; quantity: number; enabled: boolean; onAdd: () => void; onRemove: () => void; strip?: boolean }) {
  return <View style={[styles.unitButton, strip && styles.stripButton, !enabled && styles.unitButtonDisabled]}>
    <Pressable style={[styles.cardStepButton, quantity === 0 && styles.cardStepDisabled]} onPress={onRemove} disabled={quantity === 0}><Ionicons name="remove" size={27} color={quantity === 0 ? '#B9C1C8' : colors.text} /></Pressable>
    <View style={styles.unitCenter}><Text style={[styles.unitButtonLabel, !enabled && styles.unitButtonTextDisabled]}>{label}</Text><Text style={[styles.unitButtonPrice, !enabled && styles.unitButtonTextDisabled]}>৳{price === null ? '—' : formatTaka(price)} <Text style={styles.cardStepQuantity}>· {quantity}</Text></Text></View>
    <Pressable style={[styles.cardStepButton, !enabled && styles.cardStepDisabled]} onPress={onAdd} disabled={!enabled}><Ionicons name="add" size={28} color={!enabled ? '#B9C1C8' : colors.primary} /></Pressable>
  </View>;
}

export default function PosScreen() {
  const router = useRouter();
  const cartItems = useCartStore((state) => state.items);
  const addItem = useCartStore((state) => state.addItem);
  const setQuantity = useCartStore((state) => state.setQuantity);
  const removeItem = useCartStore((state) => state.removeItem);
  const [search, setSearch] = useState('');
  const [debouncedSearch, setDebouncedSearch] = useState('');
  const [toast, setToast] = useState<string | null>(null);
  const [letter, setLetter] = useState('');
  const [company, setCompany] = useState('');
  const [companyModal, setCompanyModal] = useState(false);
  const toastTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(search.trim()), 350);
    return () => clearTimeout(timer);
  }, [search]);

  const products = useQuery({
    queryKey: ['products', debouncedSearch, letter, company],
    queryFn: async () => {
      const response = await api.get<PaginatedResponse<Product>>('/catalog/products', {
        params: { search: debouncedSearch || undefined, letter: letter || undefined, company: company || undefined, per_page: 50 },
      });
      return response.data;
    },
  });

  const companies = useQuery({
    queryKey: ['catalog-companies'],
    enabled: companyModal,
    queryFn: async () => (await api.get<{ data: string[] }>('/catalog/companies')).data.data,
  });

  const addDirectly = (product: Product, unit: SaleUnit) => {
    const alreadyAdded = cartItems.some((item) => item.product.id === product.id && item.saleUnit === unit);
    addItem(product, unit);
    setToast(alreadyAdded ? 'Already added — quantity increased' : `${product.name} added to cart`);
    if (toastTimer.current) clearTimeout(toastTimer.current);
    toastTimer.current = setTimeout(() => setToast(null), 2000);
  };

  const removeDirectly = (product: Product, unit: SaleUnit) => {
    const item = cartItems.find((cartItem) => cartItem.product.id === product.id && cartItem.saleUnit === unit);
    if (!item) return;
    if (item.quantity === 1) removeItem(product.id, unit);
    else setQuantity(product.id, unit, item.quantity - 1);
  };

  const quantityInCart = (productId: number, unit: SaleUnit) => cartItems.find((item) => item.product.id === productId && item.saleUnit === unit)?.quantity ?? 0;

  const subtotal = cartSubtotal(cartItems);

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar style="light" />
      <View style={styles.header}>
        <View>
          <Text style={styles.brand}>PharmaPOS</Text>
          <Text style={styles.headerHint}>Select medicine to start a sale</Text>
        </View>
        <Pressable style={styles.cartButton} onPress={() => router.push('/cart')}>
          <Ionicons name="cart-outline" size={23} color={colors.navy} />
          <View><Text style={styles.cartLabel}>Cart ({cartItems.length})</Text><Text style={styles.cartTotal}>৳{formatTaka(subtotal)}</Text></View>
        </Pressable>
      </View>

      <View style={styles.searchSection}>
        <View style={styles.searchBox}>
          <Ionicons name="search-outline" size={21} color={colors.muted} />
          <TextInput
            value={search}
            onChangeText={setSearch}
            placeholder="Search medicine, generic or barcode"
            placeholderTextColor="#909AAA"
            style={styles.searchInput}
            autoCorrect={false}
          />
          {search ? <Pressable onPress={() => setSearch('')} hitSlop={10}><Ionicons name="close-circle" size={19} color="#A3ACB8" /></Pressable> : null}
        </View>
        <Pressable style={styles.scanButton} accessibilityLabel="Scan barcode">
          <Ionicons name="scan-outline" size={25} color={colors.primary} />
        </Pressable>
      </View>

      <View style={styles.filters}>
        <Pressable style={[styles.companyFilter, company && styles.companyFilterActive]} onPress={() => setCompanyModal(true)}><Ionicons name="business-outline" size={16} color={company ? colors.surface : colors.primary} /><Text style={[styles.companyFilterText, company && styles.companyFilterTextActive]} numberOfLines={1}>{company || 'Company'}</Text><Ionicons name="chevron-down" size={14} color={company ? colors.surface : colors.primary} /></Pressable>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.alphabetList}>
          {alphabet.map((value) => <Pressable key={value} style={[styles.letterChip, (value === 'All' ? !letter : letter === value) && styles.letterChipActive]} onPress={() => setLetter(value === 'All' ? '' : value)}><Text style={[styles.letterChipText, (value === 'All' ? !letter : letter === value) && styles.letterChipTextActive]}>{value}</Text></Pressable>)}
        </ScrollView>
      </View>

      <View style={styles.listHeader}>
        <View>
          <Text style={styles.sectionTitle}>{debouncedSearch ? 'Search results' : 'Available medicines'}</Text>
          <Text style={styles.resultCount}>{products.data?.meta.total ?? 0} products ready to sell</Text>
        </View>
        {products.isFetching && !products.isLoading ? <ActivityIndicator color={colors.primary} /> : null}
      </View>

      {products.isLoading ? (
        <View style={styles.center}><ActivityIndicator color={colors.primary} size="large" /><Text style={styles.loadingText}>Loading current stock…</Text></View>
      ) : products.isError ? (
        <View style={styles.center}>
          <EmptyState title="Could not load medicines" message={apiErrorMessage(products.error)} icon="cloud-offline-outline" />
          <Pressable style={styles.retryButton} onPress={() => products.refetch()}><Text style={styles.retryText}>Try again</Text></Pressable>
        </View>
      ) : (
        <FlatList
          data={products.data?.data ?? []}
          keyExtractor={(item) => item.id.toString()}
          renderItem={({ item }) => <MedicineCard product={item} pieceQuantity={quantityInCart(item.id, 'piece')} stripQuantity={quantityInCart(item.id, 'strip')} onAddPiece={() => addDirectly(item, 'piece')} onRemovePiece={() => removeDirectly(item, 'piece')} onAddStrip={() => addDirectly(item, 'strip')} onRemoveStrip={() => removeDirectly(item, 'strip')} />}
          contentContainerStyle={styles.list}
          keyboardShouldPersistTaps="handled"
          refreshControl={<RefreshControl refreshing={products.isRefetching} onRefresh={products.refetch} colors={[colors.primary]} tintColor={colors.primary} />}
          ListEmptyComponent={<EmptyState title="No medicine found" message="Try another name, generic name, brand, or barcode." />}
        />
      )}

      {toast ? <View style={styles.toast}><Ionicons name="checkmark-circle" size={18} color={colors.surface} /><Text style={styles.toastText}>{toast}</Text></View> : null}
      <Modal visible={companyModal} transparent animationType="slide" onRequestClose={() => setCompanyModal(false)}><View style={styles.companyBackdrop}><View style={styles.companyModal}><View style={styles.companyModalHeader}><View><Text style={styles.companyModalTitle}>Filter by company</Text><Text style={styles.companyModalHint}>Only companies with available stock are shown</Text></View><Pressable onPress={() => setCompanyModal(false)}><Ionicons name="close" size={24} color={colors.text} /></Pressable></View><FlatList data={['', ...(companies.data ?? [])]} keyExtractor={(item, index) => item || `all-${index}`} contentContainerStyle={styles.companyList} ListEmptyComponent={companies.isLoading ? <ActivityIndicator color={colors.primary} style={{ marginTop: 30 }} /> : <EmptyState title="No companies found" message="Add purchase stock first." />} renderItem={({ item }) => <Pressable style={[styles.companyRow, company === item && styles.companyRowActive]} onPress={() => { setCompany(item); setCompanyModal(false); }}><Text style={[styles.companyRowText, company === item && styles.companyRowTextActive]}>{item || 'All companies'}</Text>{company === item ? <Ionicons name="checkmark" size={18} color={colors.primary} /> : null}</Pressable>} /></View></View></Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: colors.background },
  header: { minHeight: 86, paddingHorizontal: 18, paddingVertical: 14, backgroundColor: colors.primary, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  brand: { color: colors.surface, fontSize: 24, fontWeight: '800', letterSpacing: -0.6 },
  headerHint: { color: '#D7F3EC', fontSize: 11, marginTop: 2 },
  cartButton: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: colors.surface, borderRadius: 13, paddingHorizontal: 12, paddingVertical: 8, marginTop: 8 },
  cartLabel: { color: colors.muted, fontSize: 10, fontWeight: '600' },
  cartTotal: { color: colors.navy, fontSize: 13, fontWeight: '800', marginTop: -1 },
  searchSection: { flexDirection: 'row', gap: 9, padding: 14, backgroundColor: colors.surface, borderBottomWidth: 1, borderBottomColor: colors.border },
  filters: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingHorizontal: 14, paddingVertical: 9, borderBottomWidth: 1, borderBottomColor: colors.border, backgroundColor: colors.surface }, companyFilter: { maxWidth: 125, height: 35, flexDirection: 'row', alignItems: 'center', gap: 4, borderWidth: 1, borderColor: colors.primary, borderRadius: 10, paddingHorizontal: 8, backgroundColor: colors.primarySoft }, companyFilterActive: { backgroundColor: colors.primary }, companyFilterText: { maxWidth: 74, color: colors.primaryDark, fontSize: 11, fontWeight: '800' }, companyFilterTextActive: { color: colors.surface }, alphabetList: { gap: 6, paddingRight: 10 }, letterChip: { minWidth: 33, height: 35, alignItems: 'center', justifyContent: 'center', borderRadius: 10, borderWidth: 1, borderColor: colors.border, backgroundColor: '#FBFDFC', paddingHorizontal: 8 }, letterChipActive: { borderColor: colors.primary, backgroundColor: colors.primary }, letterChipText: { color: colors.muted, fontSize: 11, fontWeight: '800' }, letterChipTextActive: { color: colors.surface },
  searchBox: { flex: 1, height: 51, borderWidth: 1, borderColor: colors.border, borderRadius: 14, flexDirection: 'row', alignItems: 'center', paddingHorizontal: 13, gap: 9, backgroundColor: '#FCFDFD' },
  searchInput: { flex: 1, height: '100%', color: colors.text, fontSize: 13 },
  scanButton: { width: 51, height: 51, borderWidth: 1, borderColor: colors.border, borderRadius: 14, alignItems: 'center', justifyContent: 'center' },
  listHeader: { paddingHorizontal: 16, paddingTop: 15, paddingBottom: 10, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  sectionTitle: { color: colors.text, fontSize: 17, fontWeight: '800' },
  resultCount: { color: colors.muted, fontSize: 11, marginTop: 2 },
  list: { paddingHorizontal: 14, paddingBottom: 20, gap: 10, flexGrow: 1 },
  card: { minHeight: 148, backgroundColor: colors.surface, borderRadius: 17, borderWidth: 1, borderColor: colors.border, padding: 12 }, productInfo: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  medicineIcon: { width: 66, height: 76, borderRadius: 13, backgroundColor: colors.primarySoft, alignItems: 'center', justifyContent: 'center' },
  medicineInitial: { color: colors.primary, fontSize: 29, fontWeight: '900' },
  cardBody: { flex: 1, alignSelf: 'stretch', justifyContent: 'center' },
  productName: { color: colors.text, fontSize: 15, fontWeight: '800' },
  productDetail: { color: colors.muted, fontSize: 11, marginTop: 4 },
  cardFooter: { flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 10 },
  stockPill: { backgroundColor: colors.primarySoft, borderRadius: 20, paddingHorizontal: 7, paddingVertical: 3 },
  lowStockPill: { backgroundColor: '#FFF0E5' },
  stockText: { color: colors.primaryDark, fontSize: 9, fontWeight: '700' },
  lowStockText: { color: colors.warning },
  unitButtons: { gap: 9, marginTop: 13 }, unitButton: { minHeight: 64, flexDirection: 'row', alignItems: 'center', borderWidth: 1.5, borderColor: colors.primary, borderRadius: 14, backgroundColor: colors.primarySoft, overflow: 'hidden' }, stripButton: { borderColor: '#4F46E5', backgroundColor: '#EEF2FF' }, unitButtonDisabled: { borderColor: '#D6DCE0', backgroundColor: '#F4F6F7' }, unitCenter: { flex: 1, alignItems: 'center' }, unitButtonLabel: { color: colors.primaryDark, fontSize: 14, fontWeight: '900' }, unitButtonPrice: { color: colors.primary, fontSize: 14, fontWeight: '900', marginTop: 3 }, unitButtonTextDisabled: { color: '#B9C1C8' }, cardStepButton: { width: 64, height: 64, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.surface }, cardStepDisabled: { backgroundColor: '#F1F3F4' }, cardStepQuantity: { color: colors.text, fontSize: 14, fontWeight: '900' },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingBottom: 50 },
  loadingText: { color: colors.muted, fontSize: 12, marginTop: 11 },
  retryButton: { marginTop: -40, paddingHorizontal: 20, paddingVertical: 10, borderRadius: 10, backgroundColor: colors.primary },
  retryText: { color: colors.surface, fontWeight: '700' },
  toast: { position: 'absolute', left: 20, right: 20, bottom: 18, minHeight: 50, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, borderRadius: 14, backgroundColor: colors.primaryDark, paddingHorizontal: 16, shadowColor: '#000', shadowOpacity: 0.2, shadowRadius: 10, elevation: 8 }, toastText: { flexShrink: 1, color: colors.surface, fontSize: 12, fontWeight: '800', textAlign: 'center' },
  companyBackdrop: { flex: 1, justifyContent: 'flex-end', backgroundColor: 'rgba(7, 17, 36, 0.5)' }, companyModal: { height: '70%', borderTopLeftRadius: 24, borderTopRightRadius: 24, backgroundColor: colors.surface, paddingTop: 18 }, companyModalHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 18, paddingBottom: 14 }, companyModalTitle: { color: colors.text, fontSize: 19, fontWeight: '900' }, companyModalHint: { color: colors.muted, fontSize: 10, marginTop: 3 }, companyList: { padding: 14, gap: 7, flexGrow: 1 }, companyRow: { minHeight: 49, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', borderWidth: 1, borderColor: colors.border, borderRadius: 12, paddingHorizontal: 13 }, companyRowActive: { borderColor: colors.primary, backgroundColor: colors.primarySoft }, companyRowText: { flex: 1, color: colors.text, fontSize: 13, fontWeight: '700' }, companyRowTextActive: { color: colors.primaryDark, fontWeight: '900' },
});
