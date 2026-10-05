import { create } from 'zustand';
import { availableUnits, cartLineTotal } from '@/lib/pricing';
import type { Product, SaleUnit } from '@/types/api';

export type CartItem = { product: Product; saleUnit: SaleUnit; quantity: number };

type CartState = {
  items: CartItem[];
  addItem: (product: Product, saleUnit: SaleUnit) => void;
  setQuantity: (productId: number, saleUnit: SaleUnit, quantity: number) => void;
  removeItem: (productId: number, saleUnit: SaleUnit) => void;
  replaceItems: (items: CartItem[]) => void;
  clear: () => void;
};

export const useCartStore = create<CartState>((set) => ({
  items: [],
  addItem: (product, saleUnit) => set((state) => {
    const multiplier = saleUnit === 'strip' ? Math.max(1, product.pieces_per_strip) : 1;
    const usedPieces = state.items
      .filter((item) => item.product.id === product.id)
      .reduce((total, item) => total + item.quantity * (item.saleUnit === 'strip' ? Math.max(1, product.pieces_per_strip) : 1), 0);
    if (usedPieces + multiplier > product.piece_stock) return state;

    const existing = state.items.find((item) => item.product.id === product.id && item.saleUnit === saleUnit);
    if (!existing) return { items: [...state.items, { product, saleUnit, quantity: 1 }] };

    return {
      items: state.items.map((item) => item.product.id === product.id && item.saleUnit === saleUnit
        ? { ...item, product, quantity: item.quantity + 1 }
        : item),
    };
  }),
  setQuantity: (productId, saleUnit, quantity) => set((state) => ({
    items: state.items.map((item) => {
      if (item.product.id !== productId || item.saleUnit !== saleUnit) return item;
      const multiplier = saleUnit === 'strip' ? Math.max(1, item.product.pieces_per_strip) : 1;
      const usedByOtherUnits = state.items
        .filter((other) => !(other.product.id === productId && other.saleUnit === saleUnit) && other.product.id === productId)
        .reduce((total, other) => total + other.quantity * (other.saleUnit === 'strip' ? Math.max(1, item.product.pieces_per_strip) : 1), 0);
      const maximum = Math.floor(Math.max(0, item.product.piece_stock - usedByOtherUnits) / multiplier);
      return { ...item, quantity: Math.max(1, Math.min(quantity, maximum)) };
    }),
  })),
  removeItem: (productId, saleUnit) => set((state) => ({ items: state.items.filter((item) => !(item.product.id === productId && item.saleUnit === saleUnit)) })),
  replaceItems: (items) => set({ items }),
  clear: () => set({ items: [] }),
}));

export function cartSubtotal(items: CartItem[]): number {
  return Math.round(items.reduce((total, item) => total + cartLineTotal(item.product, item.saleUnit, item.quantity), 0));
}
