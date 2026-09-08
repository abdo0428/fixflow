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
        Schema::create('service_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->index();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number', 100);
            $table->string('qr_code', 150)->unique();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive', 'under_maintenance', 'retired'])->default('active')->index();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('customer_id');
            $table->index('serial_number');
            $table->unique(['company_id', 'serial_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_assets');
    }
};
