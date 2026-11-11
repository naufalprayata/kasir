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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_id')->nullable()->unique()->after('id');
            $table->string('customer_name')->nullable()->after('order_id');
            $table->integer('gross_amount')->nullable()->after('customer_name');
            $table->string('snap_token')->nullable()->after('gross_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'customer_name', 'gross_amount', 'snap_token']);
        });
    }
};
