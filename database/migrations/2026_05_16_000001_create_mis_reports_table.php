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
        Schema::create('mis_reports', function (Blueprint $table) {
            $table->id();
            $table->string('branch')->index();
            $table->date('report_date')->index();
            $table->integer('occupancy')->default(0);
            $table->decimal('occupancy_pct', 5, 2)->default(0);
            $table->integer('admission')->default(0);
            $table->integer('discharge')->default(0);
            $table->integer('total_op')->default(0);
            $table->json('report_data')->nullable();
            $table->timestamps();

            $table->unique(['branch', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mis_reports');
    }
};
