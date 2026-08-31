<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->index(['branch', 'bill_date'], 'bill_items_branch_date_idx');
            $table->index(['branch', 'bill_date', 'service_type'], 'bill_items_branch_date_service_idx');
        });

        Schema::table('cashier_collections', function (Blueprint $table) {
            $table->index(['branch', 'collection_date'], 'cashier_branch_date_idx');
        });

        Schema::table('package_consumptions', function (Blueprint $table) {
            $table->index(['branch', 'consumption_date'], 'package_branch_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->dropIndex('bill_items_branch_date_idx');
            $table->dropIndex('bill_items_branch_date_service_idx');
        });
        Schema::table('cashier_collections', function (Blueprint $table) {
            $table->dropIndex('cashier_branch_date_idx');
        });
        Schema::table('package_consumptions', function (Blueprint $table) {
            $table->dropIndex('package_branch_date_idx');
        });
    }
};
