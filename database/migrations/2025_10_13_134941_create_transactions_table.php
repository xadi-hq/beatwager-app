<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();

            // Transaction details
            $table->enum('type', [
                'wager_placed',
                'wager_won',
                'wager_lost',
                'wager_refunded',
                'point_decay',
                'admin_adjustment',
                'initial_balance'
            ]);

            $table->integer('amount'); // Positive for credit, negative for debit
            $table->integer('balance_before');
            $table->integer('balance_after');

            // Related entities
            $table->foreignUuid('wager_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('wager_entry_id')->nullable()->constrained()->cascadeOnDelete();

            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'group_id']);
            $table->index('wager_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
