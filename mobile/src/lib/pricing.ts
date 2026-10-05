import type { Product, SaleUnit } from '@/types/api';

export function availableUnits(product: Product, unit: SaleUnit): number {
  return unit === 'strip' ? product.strip_stock : product.piece_stock;
}

export function cartLineTotal(product: Product, unit: SaleUnit, quantity: number): number {
  const multiplier = unit === 'strip' ? Math.max(1, product.pieces_per_strip) : 1;
  let remaining = quantity;
  let total = 0;

  for (const batch of product.batches) {
    const batchUnits = unit === 'strip' ? Math.floor(batch.piece_stock / multiplier) : batch.piece_stock;
    const price = unit === 'strip' ? batch.strip_price : batch.piece_price;
    if (batchUnits < 1 || price === null) continue;

    const taken = Math.min(remaining, batchUnits);
    total += taken * price;
    remaining -= taken;
    if (remaining === 0) break;
  }

  return Math.round(total);
}
