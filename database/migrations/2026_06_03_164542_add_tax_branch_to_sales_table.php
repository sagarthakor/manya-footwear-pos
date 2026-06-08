<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('loyalty_points')->default(0)->after('visit_count');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->integer('loyalty_points_earned')->default(0)->after('change_amount');
            $table->integer('loyalty_points_redeemed')->default(0)->after('loyalty_points_earned');
        });
    }
    public function down(): void {
        Schema::table('customers', function (Blueprint $table) { $table->dropColumn('loyalty_points'); });
        Schema::table('sales', function (Blueprint $table) { $table->dropColumn(['loyalty_points_earned','loyalty_points_redeemed']); });
    }
};
