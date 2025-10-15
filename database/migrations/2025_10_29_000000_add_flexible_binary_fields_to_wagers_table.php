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
        Schema::table('wagers', function (Blueprint $table) {
            // Flexible binary labels (option_a defaults to "Yes", option_b to "No")
            $table->string('label_option_a')->default('Yes')->after('type');
            $table->string('label_option_b')->default('No')->after('label_option_a');

            // Threshold for over/under auto-settlement
            $table->decimal('threshold_value', 15, 2)->nullable()->after('options');

            // Threshold date for before/after auto-settlement
            $table->date('threshold_date')->nullable()->after('threshold_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wagers', function (Blueprint $table) {
            $table->dropColumn([
                'label_option_a',
                'label_option_b',
                'threshold_value',
                'threshold_date',
            ]);
        });
    }
};
