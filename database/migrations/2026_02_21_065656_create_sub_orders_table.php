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
        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->decimal('subtotal', 10, 2);

            $table->enum('status', [
                'placed',
                'accepted',
                'ready',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ])->default('placed')->index();

            $table->timestamps();
            $table->index('store_id');
            $table->index('delivery_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_orders');
    }
};
