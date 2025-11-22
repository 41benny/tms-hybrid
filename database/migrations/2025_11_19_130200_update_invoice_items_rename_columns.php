<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // Add quantity if not exists (rename from qty later)
            if (!Schema::hasColumn('invoice_items', 'quantity')) {
                $table->decimal('quantity', 10, 2)->default(1)->after('description');
            }

            // Add amount if not exists (rename from subtotal later)
            if (!Schema::hasColumn('invoice_items', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0)->after('unit_price');
            }

            // Add item_type if not exists
            if (!Schema::hasColumn('invoice_items', 'item_type')) {
                $table->enum('item_type', ['shipping', 'detention', 'storage', 'handling', 'other'])->default('shipping')->after('shipment_leg_id');
            }
        });

        // Copy data from qty to quantity if qty exists
        if (Schema::hasColumn('invoice_items', 'qty')) {
            DB::statement('UPDATE invoice_items SET quantity = qty WHERE quantity = 1');
        }

        // Copy data from subtotal to amount if subtotal exists
        if (Schema::hasColumn('invoice_items', 'subtotal')) {
            DB::statement('UPDATE invoice_items SET amount = subtotal WHERE amount = 0');
        }

        // Drop old columns after data copied
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'qty')) {
                $table->dropColumn('qty');
            }
            if (Schema::hasColumn('invoice_items', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // Restore old columns
            if (!Schema::hasColumn('invoice_items', 'qty')) {
                $table->decimal('qty', 10, 2)->default(1)->after('description');
            }
            if (!Schema::hasColumn('invoice_items', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('unit_price');
            }
        });

        // Copy data back
        DB::statement('UPDATE invoice_items SET qty = quantity');
        DB::statement('UPDATE invoice_items SET subtotal = amount');

        // Drop new columns
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('invoice_items', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('invoice_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });
    }
};
