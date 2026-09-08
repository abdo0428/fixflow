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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('service_cost', 12, 2)->default(0)->after('invoice_number');
            $table->decimal('parts_total', 12, 2)->default(0)->after('service_cost');
            $table->decimal('tax_rate', 5, 4)->default(0.1500)->after('parts_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['service_cost', 'parts_total', 'tax_rate']);
        });
    }
};
