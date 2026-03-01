<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Fix sub_orders — align to app flow
        DB::statement("
            ALTER TABLE sub_orders
            MODIFY COLUMN status ENUM(
                'placed',
                'confirmed',
                'processing',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ) NOT NULL DEFAULT 'placed'
        ");

        // Fix orders — add missing statuses
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN order_status ENUM(
                'placed',
                'confirmed',
                'processing',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ) NOT NULL DEFAULT 'placed'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE sub_orders
            MODIFY COLUMN status ENUM(
                'placed',
                'accepted',
                'ready',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ) NOT NULL DEFAULT 'placed'
        ");

        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN order_status ENUM(
                'placed',
                'confirmed',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ) NOT NULL DEFAULT 'placed'
        ");
    }
};
