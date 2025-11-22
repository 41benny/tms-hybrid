<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 18, 2);
            $table->integer('useful_life_months');
            $table->decimal('residual_value', 18, 2)->default(0);
            $table->enum('depreciation_method', ['straight_line'])->default('straight_line');
            $table->unsignedBigInteger('account_asset_id');
            $table->unsignedBigInteger('account_accum_id');
            $table->unsignedBigInteger('account_expense_id');
            $table->enum('status', ['active','disposed'])->default('active');
            $table->timestamps();
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixed_asset_id');
            $table->string('period_ym', 7); // YYYY-MM
            $table->decimal('amount', 18, 2);
            $table->unsignedBigInteger('posted_journal_id')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixed_asset_id');
            $table->date('disposal_date');
            $table->decimal('proceed_amount', 18, 2);
            $table->unsignedBigInteger('gain_loss_account_id');
            $table->unsignedBigInteger('posted_journal_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
    }
};
