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
        Schema::table('scheduled_messages', function (Blueprint $table) {
            $table->boolean('is_drop_event')->default(false)->after('is_active');
            $table->integer('drop_amount')->nullable()->after('is_drop_event');

            // Index for querying drop events
            $table->index(['group_id', 'is_drop_event', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_messages', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'is_drop_event', 'scheduled_date']);
            $table->dropColumn(['is_drop_event', 'drop_amount']);
        });
    }
};
