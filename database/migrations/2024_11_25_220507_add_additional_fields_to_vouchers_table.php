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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('series')->nullable()->default(null);
            $table->string('number')->nullable()->default(null);
            $table->string('voucher_type')->nullable()->default(null);
            $table->string('currency')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['serie', 'number', 'voucher_type', 'currency']);
        });
    }
};
