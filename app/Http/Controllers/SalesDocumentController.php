<?php

namespace App\Http\Controllers;

use App\Models\CustomerVehicleSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SalesDocumentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:255']])['search'] ?? null;

        // Challans created before the sale relation was added commonly used a title
        // such as "DC-CVS-261011-0001". Attach those legacy records automatically.
        DB::table('sales_documents')->whereNull('customer_vehicle_sale_id')->get(['id', 'title'])->each(function ($document) {
            if (! preg_match('/(CVS-\d{6}-\d{4})/i', $document->title, $matches)) {
                return;
            }

            $saleId = CustomerVehicleSale::where('sale_no', strtoupper($matches[1]))->value('id');
            if ($saleId) {
                DB::table('sales_documents')->where('id', $document->id)->update([
                    'customer_vehicle_sale_id' => $saleId,
                    'updated_at' => now(),
                ]);
            }
        });

        $documents = DB::table('sales_documents')
            ->leftJoin('customer_vehicle_sales as sale', 'sales_documents.customer_vehicle_sale_id', '=', 'sale.id')
            ->leftJoin('customers as customer', 'sale.customer_id', '=', 'customer.id')
            ->select('sales_documents.*', 'sale.sale_no', 'customer.name as customer_name')
            ->when($search, fn ($query, $value) => $query->where(fn ($match) => $match->where('sales_documents.title', 'like', "%{$value}%")->orWhere('sale.sale_no', 'like', "%{$value}%")))
            ->latest('sales_documents.created_at')
            ->get();
        return view('sales-documents.index', compact('documents', 'search'));
    }

    public function create(Request $request)
    {
        $selectedSaleId = $request->validate(['customer_vehicle_sale_id' => ['nullable', 'integer', 'exists:customer_vehicle_sales,id']])['customer_vehicle_sale_id'] ?? null;
        $sales = CustomerVehicleSale::query()
            ->leftJoin('customers', 'customer_vehicle_sales.customer_id', '=', 'customers.id')
            ->where('customer_vehicle_sales.account_clearance_status', 'cleared')
            ->select('customer_vehicle_sales.id', 'customer_vehicle_sales.sale_no', 'customer_vehicle_sales.sale_date', 'customers.name as customer_name')
            ->latest('customer_vehicle_sales.sale_date')
            ->get();

        return view('sales-documents.create', compact('sales', 'selectedSaleId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['customer_vehicle_sale_id' => ['required', 'exists:customer_vehicle_sales,id'], 'title' => ['required', 'string', 'max:255'], 'file' => ['required', 'file', 'max:10240'], 'remarks' => ['nullable', 'string', 'max:5000']]);
        $saleIsCleared = CustomerVehicleSale::query()->whereKey($data['customer_vehicle_sale_id'])->where('account_clearance_status', 'cleared')->exists();
        if (! $saleIsCleared) {
            return back()->withInput()->withErrors(['customer_vehicle_sale_id' => 'Accounts clearance is required before adding a delivery challan.']);
        }
        $file = $request->file('file');
        DB::table('sales_documents')->insert(['customer_vehicle_sale_id' => $data['customer_vehicle_sale_id'], 'title' => $data['title'], 'path' => $file->store('sales-documents', 'public'), 'original_name' => $file->getClientOriginalName(), 'remarks' => $data['remarks'] ?? null, 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('sales-documents.index')->with('success', 'Document uploaded successfully.');
    }

    public function export(int $id)
    {
        $document = DB::table('sales_documents')
            ->join('customer_vehicle_sales as sale', 'sales_documents.customer_vehicle_sale_id', '=', 'sale.id')
            ->leftJoin('customers as customer', 'sale.customer_id', '=', 'customer.id')
            ->leftJoin('products as product', 'sale.product_id', '=', 'product.id')
            ->where('sales_documents.id', $id)
            ->select('sales_documents.*', 'sale.sale_no', 'sale.sale_date', 'sale.delivery_point', 'sale.tentative_delivery_date', 'sale.quantity', 'sale.color', 'customer.name as customer_name', 'customer.phone as customer_phone', 'customer.address as customer_address', 'product.name as product_name')
            ->first();
        abort_unless($document, 404);

        return view('sales-documents.export', compact('document'));
    }

    public function download(int $id)
    {
        $document = DB::table('sales_documents')->find($id);
        abort_unless($document, 404);
        return Storage::disk('public')->download($document->path, $document->original_name);
    }

    public function destroy(int $id)
    {
        $document = DB::table('sales_documents')->find($id);
        abort_unless($document, 404);
        Storage::disk('public')->delete($document->path);
        DB::table('sales_documents')->where('id', $id)->delete();
        return redirect()->route('sales-documents.index')->with('success', 'Document deleted successfully.');
    }
}
