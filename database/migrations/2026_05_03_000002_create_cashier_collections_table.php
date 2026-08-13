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
        Schema::create('cashier_collections', function (Blueprint $table) {
            $table->id();
            $table->string('branch');
            $table->date('collection_date');
            $table->string('patient_type')->nullable();
            $table->string('user_department')->nullable();
            $table->decimal('paid_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_collections');
    }
};
