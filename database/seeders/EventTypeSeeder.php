<?php
namespace Database\Seeders; use App\Models\EventType; use Illuminate\Database\Seeder; class EventTypeSeeder extends Seeder { public function run():void{foreach(['Vehicle Launch','Test Drive','Roadshow','Showroom Event','Digital Campaign'] as $name)EventType::updateOrCreate(['name'=>$name],['is_active'=>true]);} }
