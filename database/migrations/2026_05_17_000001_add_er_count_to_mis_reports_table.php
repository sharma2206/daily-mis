<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mis_reports', function (Blueprint $table) {
            $table->integer('er_count')->default(0)->after('total_op');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mis_reports', function (Blueprint $table) {
            $table->dropColumn('er_count');
        });
    }
};
