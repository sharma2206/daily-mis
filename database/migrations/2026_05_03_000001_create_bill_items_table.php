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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->string('branch')->index();
            $table->date('bill_date')->index();
            $table->string('patient_id');
            $table->string('patient_type')->nullable();
            $table->string('service_type');
            $table->string('sub_department')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->integer('quantity')->default(1);
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
