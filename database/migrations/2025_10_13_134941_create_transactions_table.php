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
            // Type is validated via App\Enums\TransactionType enum, not database constraint
            $table->string('type', 50);

            $table->integer('amount'); // Positive for credit, negative for debit
            $table->integer('balance_before');
            $table->integer('balance_after');

            // Polymorphic relationship
            $table->string('transactionable_type')->nullable()->after('group_id');
            $table->uuid('transactionable_id')->nullable()->after('transactionable_type');

            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'group_id']);
            $table->index('type');
            $table->index('created_at');
            $table->index(['transactionable_type', 'transactionable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
