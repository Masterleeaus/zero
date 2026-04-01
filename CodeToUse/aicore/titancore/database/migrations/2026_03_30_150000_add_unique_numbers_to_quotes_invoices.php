<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotes', static function (Blueprint $table) {
            $table->unique(['company_id', 'quote_number'], 'quotes_company_quote_number_unique');
        });

        Schema::table('invoices', static function (Blueprint $table) {
            $table->unique(['company_id', 'invoice_number'], 'invoices_company_invoice_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', static function (Blueprint $table) {
            $table->dropUnique('quotes_company_quote_number_unique');
        });

        Schema::table('invoices', static function (Blueprint $table) {
            $table->dropUnique('invoices_company_invoice_number_unique');
        });
    }
};
