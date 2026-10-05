export type Employee = {
  id: number;
  name: string;
  employee_code: string | null;
  email: string;
  designation: string | null;
  roles: string[];
  primary_role?: 'admin' | 'manager' | 'cashier' | 'salesperson' | null;
};

export type Product = {
  id: number;
  name: string;
  barcode: string | null;
  brand: string | null;
  generic_name: string | null;
  pieces_per_strip: number;
  sell_by_piece: boolean;
  sell_by_strip: boolean;
  piece_stock: number;
  strip_stock: number;
  reorder_level: number;
  is_low_stock: boolean;
  piece_price: number | null;
  strip_price: number | null;
  batches: ProductBatch[];
};

export type ProductBatch = {
  id: number;
  piece_stock: number;
  piece_price: number;
  strip_price: number | null;
  expires_on: string | null;
};

export type SaleUnit = 'piece' | 'strip';

export type Customer = {
  id: number;
  name: string;
  customer_type: 'retail' | 'wholesale';
  phone: string | null;
  due_balance: string;
};

export type PaginatedResponse<T> = {
  data: T[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
};

export type SaleSummary = {
  id: number;
  invoice_number: string;
  customer_type: 'walking' | 'retail' | 'wholesale';
  total: number;
  paid: number;
  due: number;
  items_count: number;
  sold_at: string;
  employee: { id: number; name: string; employee_code: string | null } | null;
};

export type SalesOverview = PaginatedResponse<SaleSummary> & {
  meta: PaginatedResponse<SaleSummary>['meta'] & {
    salespeople: Array<{
      employee: { id: number; name: string; employee_code: string | null };
      sales_count: number;
      total_sales: number;
    }>;
  };
};

export type SaleDetail = SaleSummary & {
  subtotal: number;
  discount: number;
  change: number;
  payment_method: 'cash' | 'card' | 'mobile_banking';
  employee: { id: number; name: string; employee_code: string | null };
  customer: { id: number; name: string; type: string; phone: string | null } | null;
  items: Array<{
    id: number;
    product_id: number;
    product_name: string;
    sale_unit: SaleUnit;
    quantity: number;
    units_per_sale_unit: number;
    unit_price: number;
    line_total: number;
  }>;
};

export type AdminOverview = {
  reports: { today_sales: number; monthly_sales: number; monthly_profit: number; monthly_purchases: number; stock_value: number; low_stock_count: number };
  modules: { products: number; suppliers: number; customers: number; purchases: number; employees: number };
  recent_purchases: Array<{ invoice_number: string; supplier: string | null; total: number; purchased_at: string | null }>;
  low_stock: Array<{ id: number; name: string; stock: number; reorder_level: number }>;
};

export type EmployeeSalesReport = { from: string; to: string; employees: Array<{ employee: { id: number; name: string; employee_code: string | null }; invoice_count: number; total_sales: number; total_paid: number; total_due: number }> };
