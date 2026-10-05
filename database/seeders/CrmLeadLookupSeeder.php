<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CrmLeadLookupSeeder extends Seeder {
    public function run(): void {
        foreach (['Corporate','Retail','Government','SME'] as $name) DB::table('crm_segments')->updateOrInsert(['name'=>$name], ['is_active'=>true, 'updated_at'=>now(), 'created_at'=>now()]);
        foreach ([['name'=>'Cold','badge_color'=>'#b89532'],['name'=>'Warm','badge_color'=>'#d97706'],['name'=>'Hot','badge_color'=>'#dc2626'],['name'=>'Won','badge_color'=>'#059669'],['name'=>'Lost','badge_color'=>'#64748b']] as $row) DB::table('crm_lead_statuses')->updateOrInsert(['name'=>$row['name']], $row+['is_active'=>true,'updated_at'=>now(),'created_at'=>now()]);
        foreach ([['name'=>'Lead in','code'=>'C0'],['name'=>'Qualified','code'=>'C1'],['name'=>'Proposal','code'=>'C2'],['name'=>'Negotiation','code'=>'C3'],['name'=>'Closed','code'=>'C4']] as $row) DB::table('crm_pipelines')->updateOrInsert(['name'=>$row['name']], $row+['is_active'=>true,'updated_at'=>now(),'created_at'=>now()]);
        foreach (['Facebook','Website','Referral','Phone call','Walk-in','Campaign'] as $name) DB::table('crm_sources')->updateOrInsert(['name'=>$name], ['is_active'=>true,'updated_at'=>now(),'created_at'=>now()]);
        foreach ([['name'=>'White','hex_code'=>'#ffffff'],['name'=>'Black','hex_code'=>'#111827'],['name'=>'Red','hex_code'=>'#dc2626'],['name'=>'Blue','hex_code'=>'#2563eb'],['name'=>'Silver','hex_code'=>'#94a3b8']] as $row) DB::table('crm_colors')->updateOrInsert(['name'=>$row['name']], $row+['is_active'=>true,'updated_at'=>now(),'created_at'=>now()]);
    }
}
