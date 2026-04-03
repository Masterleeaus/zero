<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotes', static function (Blueprint $table) {
            if (! Schema::hasColumn('quotes', 'quote_number')) {
                $table->string('quote_number')->nullable()->after('customer_id');
            }
            if (! Schema::hasColumn('quotes', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('issue_date');
            }
            if (! Schema::hasColumn('quotes', 'site_id')) {
                $table->unsignedBigInteger('site_id')->nullable()->after('customer_id')->index();
            }
            if (! Schema::hasColumn('quotes', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('status');
            }
            if (! Schema::hasColumn('quotes', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('quotes', 'checklist_template')) {
                $table->json('checklist_template')->nullable()->after('notes');
            }
        });

        if (Schema::hasColumn('quotes', 'number') && Schema::hasColumn('quotes', 'quote_number')) {
            DB::table('quotes')->whereNull('quote_number')->update(['quote_number' => DB::raw('number')]);
        }
        if (Schema::hasColumn('quotes', 'due_date') && Schema::hasColumn('quotes', 'valid_until')) {
            DB::table('quotes')->whereNull('valid_until')->update(['valid_until' => DB::raw('due_date')]);
        }

        Schema::table('invoices', static function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('customer_id');
            }
            if (! Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('status');
            }
            if (! Schema::hasColumn('invoices', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('invoices', 'balance')) {
                $table->decimal('balance', 12, 2)->default(0)->after('paid_amount');
            }
        });

        if (Schema::hasColumn('invoices', 'number') && Schema::hasColumn('invoices', 'invoice_number')) {
            DB::table('invoices')->whereNull('invoice_number')->update(['invoice_number' => DB::raw('number')]);
        }
    }

    public function down(): void
    {
        Schema::table('quotes', static function (Blueprint $table) {
            foreach (['site_id', 'subtotal', 'tax', 'checklist_template', 'quote_number', 'valid_until'] as $column) {
                if (Schema::hasColumn('quotes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('invoices', static function (Blueprint $table) {
            foreach (['subtotal', 'tax', 'paid_amount', 'balance', 'invoice_number'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
