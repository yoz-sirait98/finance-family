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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'account_id'], 'transactions_user_account');
            $table->index(['user_id', 'type', 'transaction_date'], 'transactions_user_type_date');
            $table->index(['member_id', 'transaction_date'], 'transactions_member_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_user_account');
            $table->dropIndex('transactions_user_type_date');
            $table->dropIndex('transactions_member_date');
        });
    }
};
