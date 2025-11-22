<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns if they do not exist
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('due_date');
            }
            if (!Schema::hasColumn('invoices', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('tax_amount');
            }
            if (!Schema::hasColumn('invoices', 'total_amount')) {
                // In older schema total_amount exists; this is a safeguard.
                $table->decimal('total_amount', 15, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('invoices', 'status')) {
                $table->enum('status', ['draft','pending','sent','partial','paid','overdue','cancelled'])->default('draft')->after('paid_amount');
            }
            if (!Schema::hasColumn('invoices', 'payment_terms')) {
                $table->string('payment_terms', 100)->nullable()->after('status');
            }
            if (!Schema::hasColumn('invoices', 'notes')) {
                $table->text('notes')->nullable()->after('payment_terms');
            }
            if (!Schema::hasColumn('invoices', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('invoices', 'sent_at')) {
                $table->dateTime('sent_at')->nullable()->after('internal_notes');
            }
            if (!Schema::hasColumn('invoices', 'paid_at')) {
                $table->dateTime('paid_at')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('invoices', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('paid_at');
            }
            if (!Schema::hasColumn('invoices', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }
        });

        // Adjust ENUM for status if column already exists with different definition (MySQL specific)
        // Wrap in try to avoid failure on unsupported drivers.
        try {
            if (Schema::hasColumn('invoices', 'status')) {
                DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','pending','sent','partial','paid','overdue','cancelled') DEFAULT 'draft'");
            }
        } catch (\Throwable $e) {
            // Optional: log or ignore; keeping silent to not break migration flow.
        }
    }

    public function down(): void
    {
        // We won't drop columns automatically to avoid data loss.
        // If rollback needed, manually adjust migration.
    }
};
