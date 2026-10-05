<?php
namespace Database\Seeders; use Illuminate\Database\Seeder; use Illuminate\Support\Facades\DB;
class ContactLookupSeeder extends Seeder { public function run():void { foreach(['Customer','Lead','Supplier','Partner','Other'] as $name) DB::table('crm_contact_types')->updateOrInsert(['name'=>$name],['is_active'=>true,'updated_at'=>now(),'created_at'=>now()]); foreach(['Dhaka','Chattogram','Khulna','Rajshahi','Sylhet','Barishal','Rangpur','Mymensingh'] as $name) DB::table('crm_locations')->updateOrInsert(['name'=>$name],['is_active'=>true,'updated_at'=>now(),'created_at'=>now()]); } }
