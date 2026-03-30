<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotes', static function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'number')) {
                $table->renameColumn('number', 'quote_number');
            }
            if (Schema::hasColumn('quotes', 'due_date')) {
                $table->renameColumn('due_date', 'valid_until');
            }
            $table->unsignedBigInteger('site_id')->nullable()->after('customer_id')->index();
            $table->decimal('subtotal', 12, 2)->default(0)->after('status');
            $table->decimal('tax', 12, 2)->default(0)->after('subtotal');
            $table->decimal('total', 12, 2)->default(0)->change();
            $table->json('checklist_template')->nullable()->after('notes');
        });

        Schema::table('invoices', static function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'number')) {
                $table->renameColumn('number', 'invoice_number');
            }
            $table->decimal('subtotal', 12, 2)->default(0)->after('status');
            $table->decimal('tax', 12, 2)->default(0)->after('subtotal');
            $table->decimal('total', 12, 2)->default(0)->change();
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total');
            $table->decimal('balance', 12, 2)->default(0)->after('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', static function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'quote_number')) {
                $table->renameColumn('quote_number', 'number');
            }
            if (Schema::hasColumn('quotes', 'valid_until')) {
                $table->renameColumn('valid_until', 'due_date');
            }
            $table->dropColumn(['site_id', 'subtotal', 'tax', 'checklist_template']);
            $table->decimal('total', 12, 2)->default(0)->change();
        });

        Schema::table('invoices', static function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'invoice_number')) {
                $table->renameColumn('invoice_number', 'number');
            }
            $table->dropColumn(['subtotal', 'tax', 'paid_amount', 'balance']);
            $table->decimal('total', 12, 2)->default(0)->change();
        });
    }
};
