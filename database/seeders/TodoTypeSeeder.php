<?php
namespace Database\Seeders;
use App\Models\TodoType;
use Illuminate\Database\Seeder;
class TodoTypeSeeder extends Seeder { public function run(): void { foreach (['Follow-up Call','Meeting','Email','Visit','Quotation','Documentation'] as $name) TodoType::updateOrCreate(['name'=>$name],['is_active'=>true]); } }
