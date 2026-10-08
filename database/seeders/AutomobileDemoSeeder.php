<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * A safe, repeatable demo dataset for the automobile CRM.
 *
 * It uses update-or-create style operations, so running it does not clear
 * records the user already entered in the application.
 */
class AutomobileDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([PermissionSeeder::class, DemoEmployeeSeeder::class]);

        $now = now();
        $adminId = User::where('email', 'admin@w3crm.com')->value('id');
        $salesId = User::where('email', 'tanvir@example.test')->value('id') ?? $adminId;
        $marketingId = User::where('email', 'rupa@example.test')->value('id') ?? $adminId;

        // CRM, activity and task setup.
        $this->namedRows('crm_sources', ['Facebook', 'Website', 'Referral', 'Phone Call', 'Walk-in', 'Campaign']);
        $this->namedRows('crm_segments', ['Retail', 'Corporate', 'Government', 'SME']);
        $this->namedRows('crm_locations', ['Dhaka', 'Chattogram', 'Khulna', 'Rajshahi', 'Sylhet']);
        $this->namedRows('crm_contact_types', ['Customer', 'Lead', 'Supplier', 'Partner', 'Other']);
        $this->namedRows('todo_types', ['Follow-up Call', 'Meeting', 'Email', 'Visit', 'Quotation', 'Documentation']);
        $this->namedRows('event_types', ['Vehicle Launch', 'Test Drive', 'Roadshow', 'Showroom Event', 'Digital Campaign']);
        $this->namedRows('activity_subject_types', [
            ['name' => 'Customer', 'key' => 'customer'], ['name' => 'Lead', 'key' => 'lead'],
            ['name' => 'Contact', 'key' => 'contact'], ['name' => 'Organization', 'key' => 'organization'],
        ]);
        $this->namedRows('crm_lead_statuses', [
            ['name' => 'Cold', 'badge_color' => '#6b7280'], ['name' => 'Warm', 'badge_color' => '#f59e0b'],
            ['name' => 'Hot', 'badge_color' => '#ef4444'], ['name' => 'Won', 'badge_color' => '#10b981'],
            ['name' => 'Lost', 'badge_color' => '#64748b'],
        ]);
        $this->namedRows('crm_pipelines', [
            ['name' => 'Lead In', 'code' => 'C0'], ['name' => 'Qualified', 'code' => 'C1'],
            ['name' => 'Proposal', 'code' => 'C2'], ['name' => 'Negotiation', 'code' => 'C3'],
            ['name' => 'Closed', 'code' => 'C4'],
        ]);
        $this->namedRows('crm_colors', [
            ['name' => 'White', 'hex_code' => '#ffffff'], ['name' => 'Black', 'hex_code' => '#111827'],
            ['name' => 'Silver', 'hex_code' => '#9ca3af'], ['name' => 'Red', 'hex_code' => '#dc2626'],
            ['name' => 'Blue', 'hex_code' => '#2563eb'],
        ]);

        foreach ([
            'Phone' => ['Incoming', 'Outgoing', 'Follow-up'],
            'Meeting' => ['In person', 'Online', 'Test drive'],
            'Email' => ['Sent', 'Received', 'Quotation email'],
            'WhatsApp' => ['New inquiry', 'Follow-up', 'Document share'],
            'Facebook' => ['Page message', 'Comment', 'Lead form'],
            'Desk work' => ['Quotation', 'Documentation', 'Research'],
        ] as $type => $subtypes) {
            DB::table('activity_types')->updateOrInsert(['name' => $type], ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
            $typeId = DB::table('activity_types')->where('name', $type)->value('id');
            foreach ($subtypes as $name) {
                DB::table('activity_sub_types')->updateOrInsert(
                    ['activity_type_id' => $typeId, 'name' => $name],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                );
            }
        }

        // Inventory masters: these are what make Store Position appear in Goods Receive.
        foreach ([
            ['name' => 'Sales', 'code' => 'SALES'], ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Service', 'code' => 'SERVICE'], ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Operations', 'code' => 'OPS'],
        ] as $row) {
            // Name is the primary business key here: a user may already have
            // created the department with a different (or blank) code.
            DB::table('departments')->updateOrInsert(['name' => $row['name']], $row + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ([
            ['name' => 'Main Showroom', 'code' => 'DHK-01', 'phone' => '02-55000001', 'address' => 'Tejgaon, Dhaka'],
            ['name' => 'Chattogram Showroom', 'code' => 'CTG-01', 'phone' => '031-5500001', 'address' => 'Agrabad, Chattogram'],
            ['name' => 'Central Warehouse', 'code' => 'WH-01', 'phone' => '02-55000002', 'address' => 'Tongi, Gazipur'],
        ] as $row) {
            DB::table('stores')->updateOrInsert(['code' => $row['code']], $row + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ([
            ['store' => 'DHK-01', 'name' => 'Showroom Display', 'code' => 'DHK-DISPLAY'],
            ['store' => 'DHK-01', 'name' => 'Delivery Bay', 'code' => 'DHK-DELIVERY'],
            ['store' => 'CTG-01', 'name' => 'Showroom Display', 'code' => 'CTG-DISPLAY'],
            ['store' => 'WH-01', 'name' => 'Vehicle Yard', 'code' => 'WH-YARD'],
            ['store' => 'WH-01', 'name' => 'Parts Rack A', 'code' => 'WH-RACK-A'],
        ] as $row) {
            $storeId = DB::table('stores')->where('code', $row['store'])->value('id');
            DB::table('store_positions')->updateOrInsert(['store_id' => $storeId, 'code' => $row['code']], ['name' => $row['name'], 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        $this->namedRows('item_categories', ['SUV', 'Sedan', 'Pickup', 'Motorcycle', 'Spare Parts']);
        foreach ([
            ['name' => 'Auto Imports Bangladesh Ltd', 'phone' => '01711001001', 'email' => 'sales@autoimports.test', 'address' => 'Tejgaon, Dhaka'],
            ['name' => 'Prime Motors Trading', 'phone' => '01711001002', 'email' => 'info@primemotors.test', 'address' => 'Uttara, Dhaka'],
            ['name' => 'Eastern Vehicle Solutions', 'phone' => '01711001003', 'email' => 'sales@easternvehicle.test', 'address' => 'Agrabad, Chattogram'],
        ] as $row) {
            Supplier::updateOrCreate(['name' => $row['name']], $row + ['is_active' => true]);
        }

        $retailId = DB::table('crm_segments')->where('name', 'Retail')->value('id');
        $corporateId = DB::table('crm_segments')->where('name', 'Corporate')->value('id');
        $products = [
            ['part_no' => 'TCC-HV-2026', 'name' => 'Toyota Corolla Cross Hybrid 2026', 'category' => 'SUV', 'segment_id' => $retailId, 'unit_price' => 4350000, 'reorder_level' => 1, 'description' => 'Hybrid SUV for family and executive buyers.'],
            ['part_no' => 'HVZ-EHV-2026', 'name' => 'Honda Vezel e:HEV 2026', 'category' => 'SUV', 'segment_id' => $retailId, 'unit_price' => 4050000, 'reorder_level' => 1, 'description' => 'Fuel-efficient hybrid compact SUV.'],
            ['part_no' => 'TAX-HY-2025', 'name' => 'Toyota Axio Hybrid 2025', 'category' => 'Sedan', 'segment_id' => $retailId, 'unit_price' => 2900000, 'reorder_level' => 1, 'description' => 'Reliable hybrid sedan.'],
            ['part_no' => 'ML200-DC-2026', 'name' => 'Mitsubishi L200 Double Cab', 'category' => 'Pickup', 'segment_id' => $corporateId, 'unit_price' => 4750000, 'reorder_level' => 1, 'description' => 'Double-cab pickup for fleet use.'],
            ['part_no' => 'YFZS-V3-2026', 'name' => 'Yamaha FZ-S V3', 'category' => 'Motorcycle', 'segment_id' => $retailId, 'unit_price' => 270000, 'reorder_level' => 2, 'description' => 'Street motorcycle.'],
            ['part_no' => 'EO-5W30-4L', 'name' => 'Engine Oil 5W-30 (4L)', 'category' => 'Spare Parts', 'segment_id' => null, 'unit_price' => 5200, 'reorder_level' => 10, 'description' => 'Fully synthetic engine oil.'],
        ];
        foreach ($products as $row) {
            $categoryId = DB::table('item_categories')->where('name', $row['category'])->value('id');
            Product::updateOrCreate(['part_no' => $row['part_no']], [
                'name' => $row['name'], 'item_category_id' => $categoryId, 'segment_id' => $row['segment_id'], 'category' => $row['category'],
                'description' => $row['description'], 'unit' => 'piece', 'unit_price' => $row['unit_price'], 'reorder_level' => $row['reorder_level'],
                'commission_type' => 'percent', 'unit_commission' => 0, 'is_active' => true,
            ]);
        }

        foreach ([
            ['name' => 'Rahim Ahmed', 'customer_type' => 'retail', 'phone' => '01711010001', 'email' => 'rahim@example.test', 'address' => 'Dhanmondi, Dhaka'],
            ['name' => 'Nusrat Jahan', 'customer_type' => 'retail', 'phone' => '01711010002', 'email' => 'nusrat@example.test', 'address' => 'Banani, Dhaka'],
            ['name' => 'Greenfield Logistics Ltd', 'customer_type' => 'wholesale', 'phone' => '01711010003', 'email' => 'accounts@greenfield.test', 'address' => 'Gulshan, Dhaka'],
            ['name' => 'Delta Transport', 'customer_type' => 'wholesale', 'phone' => '01711010004', 'email' => 'accounts@delta.test', 'address' => 'Mirpur, Dhaka'],
        ] as $row) {
            Customer::updateOrCreate(['phone' => $row['phone']], $row + ['due_balance' => 0, 'is_active' => true]);
        }

        $dhakaId = DB::table('crm_locations')->where('name', 'Dhaka')->value('id');
        $customerContactType = DB::table('crm_contact_types')->where('name', 'Customer')->value('id');
        foreach ([
            ['name' => 'Greenfield Logistics', 'phone' => '01711010003', 'email' => 'accounts@greenfield.test', 'contact_person' => 'Mahmud Hasan'],
            ['name' => 'Delta Transport', 'phone' => '01711010004', 'email' => 'accounts@delta.test', 'contact_person' => 'Shafiq Islam'],
        ] as $row) {
            DB::table('crm_organizations')->updateOrInsert(['name' => $row['name']], $row + ['location_id' => $dhakaId, 'created_by' => $adminId, 'store_id' => DB::table('stores')->where('code', 'DHK-01')->value('id'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        $greenfieldId = DB::table('crm_organizations')->where('name', 'Greenfield Logistics')->value('id');
        DB::table('contacts')->updateOrInsert(['contact_code' => 'CNT-DEMO-001'], ['name' => 'Mahmud Hasan', 'mobile_1' => '01711010003', 'email' => 'mahmud@greenfield.test', 'organization_id' => $greenfieldId, 'job_title' => 'Fleet Manager', 'contact_type_id' => $customerContactType, 'present_location_id' => $dhakaId, 'created_by' => $adminId, 'store_id' => DB::table('stores')->where('code', 'DHK-01')->value('id'), 'created_at' => $now, 'updated_at' => $now]);

        $this->seedLeads($salesId, $marketingId, $adminId, $now);
        $this->seedOperations($salesId, $marketingId, $now);
    }

    private function seedLeads(int $salesId, int $marketingId, int $adminId, $now): void
    {
        $id = fn (string $table, string $name) => DB::table($table)->where('name', $name)->value('id');
        $storeId = DB::table('stores')->where('code', 'DHK-01')->value('id');
        foreach ([
            ['name' => 'Arif Hossain', 'phone' => '01711020001', 'email' => 'arif@example.test', 'segment' => 'Retail', 'product' => 'TCC-HV-2026', 'color' => 'White', 'status' => 'Hot', 'pipeline' => 'Qualified', 'owner' => $salesId, 'activity' => 'Phone', 'source' => 'Facebook', 'remarks' => 'Requested a Corolla Cross test drive.'],
            ['name' => 'Sabila Noor', 'phone' => '01711020002', 'email' => 'sabila@example.test', 'segment' => 'Retail', 'product' => 'HVZ-EHV-2026', 'color' => 'Black', 'status' => 'Warm', 'pipeline' => 'Lead In', 'owner' => $salesId, 'activity' => 'WhatsApp', 'source' => 'Website', 'remarks' => 'Comparing Vezel finance options.'],
            ['name' => 'Mahmud Hasan', 'phone' => '01711020003', 'email' => 'mahmud@greenfield.test', 'segment' => 'Corporate', 'product' => 'ML200-DC-2026', 'color' => 'Silver', 'status' => 'Hot', 'pipeline' => 'Proposal', 'owner' => $marketingId, 'activity' => 'Meeting', 'source' => 'Referral', 'remarks' => 'Needs fleet quotation for three pickups.'],
        ] as $row) {
            DB::table('leads')->updateOrInsert(['phone' => $row['phone']], [
                'name' => $row['name'], 'email' => $row['email'], 'segment_id' => $id('crm_segments', $row['segment']),
                'product_id' => Product::where('part_no', $row['product'])->value('id'), 'color_id' => $id('crm_colors', $row['color']),
                'lead_status_id' => $id('crm_lead_statuses', $row['status']), 'pipeline_id' => $id('crm_pipelines', $row['pipeline']),
                'owner_id' => $row['owner'], 'activity_type_id' => $id('activity_types', $row['activity']), 'source_id' => $id('crm_sources', $row['source']),
                'lead_date' => today(), 'remarks' => $row['remarks'], 'created_by' => $adminId, 'store_id' => $storeId, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function seedOperations(int $salesId, int $marketingId, $now): void
    {
        $storeId = DB::table('stores')->where('code', 'WH-01')->value('id');
        $yardId = DB::table('store_positions')->where('code', 'WH-YARD')->value('id');
        $supplierId = Supplier::where('name', 'Auto Imports Bangladesh Ltd')->value('id');
        $receiptId = DB::table('goods_receipts')->where('mrr_no', 'MRR-DEMO-001')->value('id');
        if (! $receiptId) {
            $receiptId = DB::table('goods_receipts')->insertGetId(['mrr_no' => 'MRR-DEMO-001', 'mrr_date' => today(), 'payment_mode' => 'Bank', 'purchase_type' => 'Local', 'currency' => 'BDT', 'exchange_rate' => 1, 'store_id' => $storeId, 'supplier_id' => $supplierId, 'po_no' => 'PO-DEMO-001', 'remarks' => 'Demo vehicle receiving record.', 'total_amount' => 8400000, 'user_id' => $marketingId, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ([['part' => 'TCC-HV-2026', 'qty' => 1, 'rate' => 4350000], ['part' => 'HVZ-EHV-2026', 'qty' => 1, 'rate' => 4050000]] as $row) {
            $productId = Product::where('part_no', $row['part'])->value('id');
            DB::table('goods_receipt_items')->updateOrInsert(['goods_receipt_id' => $receiptId, 'product_id' => $productId], ['store_position_id' => $yardId, 'quantity' => $row['qty'], 'rate' => $row['rate'], 'amount' => $row['qty'] * $row['rate'], 'landed_cost' => $row['rate'], 'created_at' => $now, 'updated_at' => $now]);
        }

        $leadId = DB::table('leads')->where('phone', '01711020001')->value('id');
        $phoneType = DB::table('activity_types')->where('name', 'Phone')->value('id');
        $followUpType = DB::table('activity_sub_types')->where('activity_type_id', $phoneType)->where('name', 'Follow-up')->value('id');
        DB::table('activities')->updateOrInsert(['remarks' => 'Demo follow-up: Arif confirmed the Friday test drive.'], ['activity_type_id' => $phoneType, 'activity_sub_type_id' => $followUpType, 'user_id' => $salesId, 'subject_type' => 'lead', 'subject_id' => $leadId, 'activity_with' => 'Arif Hossain', 'from_at' => now()->subDay(), 'to_at' => now()->subDay()->addMinutes(12), 'keep_todo' => true, 'created_at' => $now, 'updated_at' => $now]);
        $todoType = DB::table('todo_types')->where('name', 'Test Drive')->value('id') ?? DB::table('todo_types')->where('name', 'Meeting')->value('id');
        DB::table('todos')->updateOrInsert(['note' => 'Call Arif to reconfirm the Corolla Cross test drive.'], ['todo_type_id' => $todoType, 'assigned_to' => $salesId, 'subject_type' => 'lead', 'subject_id' => $leadId, 'task_with' => 'Arif Hossain', 'due_at' => now()->addDay(), 'priority' => 'high', 'remind_before_minutes' => 60, 'status' => 'pending', 'created_at' => $now, 'updated_at' => $now]);

        $retailId = DB::table('crm_segments')->where('name', 'Retail')->value('id');
        $eventTypeId = DB::table('event_types')->where('name', 'Test Drive')->value('id') ?? DB::table('event_types')->where('name', 'Showroom Event')->value('id');
        DB::table('promotion_events')->updateOrInsert(['title' => 'Corolla Cross Weekend Test Drive'], ['segment_id' => $retailId, 'event_type_id' => $eventTypeId, 'supervisor_id' => $marketingId, 'attendee_ids' => json_encode([$salesId, $marketingId]), 'start_date' => today()->addDays(3), 'end_date' => today()->addDays(4), 'status' => 'upcoming', 'remarks' => 'Demo campaign event.', 'created_at' => $now, 'updated_at' => $now]);

        $rahimId = Customer::where('phone', '01711010001')->value('id');
        DB::table('customer_vehicle_sales')->updateOrInsert(['sale_no' => 'CVS-DEMO-001'], ['sale_date' => today()->subDays(2), 'customer_id' => $rahimId, 'mode_of_sale' => 'cash', 'delivery_point' => 'Main Showroom', 'tentative_delivery_date' => today()->addDays(2), 'sales_person_id' => $salesId, 'segment_id' => $retailId, 'product_id' => Product::where('part_no', 'TCC-HV-2026')->value('id'), 'color' => 'White', 'quantity' => 1, 'body_type' => 'SUV', 'seat_capacity' => 5, 'registration_type' => 'individual', 'registration_place' => 'Dhaka', 'vehicle_tracker' => true, 'offered_price' => 4400000, 'unit_price' => 4350000, 'final_price' => 4300000, 'paid_amount' => 500000, 'payment_date' => today()->subDays(2), 'payment_mode' => 'bank', 'remarks' => 'Demo confirmed retail vehicle sale.', 'created_at' => $now, 'updated_at' => $now]);
    }

    /** @param array<int, string|array<string, mixed>> $rows */
    private function namedRows(string $table, array $rows): void
    {
        $now = now();
        foreach ($rows as $row) {
            $row = is_string($row) ? ['name' => $row] : $row;
            $defaults = ['created_at' => $now, 'updated_at' => $now];
            if (DB::getSchemaBuilder()->hasColumn($table, 'is_active')) {
                $defaults['is_active'] = true;
            }
            DB::table($table)->updateOrInsert(['name' => $row['name']], $row + $defaults);
        }
    }
}
