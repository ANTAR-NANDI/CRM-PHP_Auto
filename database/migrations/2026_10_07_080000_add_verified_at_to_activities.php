<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::table('activities',fn(Blueprint $t)=>$t->timestamp('verified_at')->nullable()->after('keep_todo'));}public function down():void{Schema::table('activities',fn(Blueprint $t)=>$t->dropColumn('verified_at'));}};
