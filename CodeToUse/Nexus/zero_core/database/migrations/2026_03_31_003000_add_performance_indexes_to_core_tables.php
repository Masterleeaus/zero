<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->addIndexIfMissing('service_jobs', ['company_id', 'status'], 'service_jobs_company_id_status_index');
        $this->addIndexIfMissing('service_jobs', ['company_id', 'assigned_user_id'], 'service_jobs_company_id_assigned_user_id_index');
        $this->addIndexIfMissing('service_jobs', ['service_agreement_id'], 'service_jobs_service_agreement_id_index');
        $this->addIndexIfMissing('service_jobs', ['agreement_id'], 'service_jobs_agreement_id_index');
        $this->addIndexIfMissing('service_jobs', ['scheduled_at'], 'service_jobs_scheduled_at_index');

        $this->addIndexIfMissing('quotes', ['company_id', 'status'], 'quotes_company_id_status_index');
        $this->addIndexIfMissing('quotes', ['company_id', 'customer_id'], 'quotes_company_id_customer_id_index');

        $this->addIndexIfMissing('invoices', ['company_id', 'status'], 'invoices_company_id_status_index');
        $this->addIndexIfMissing('invoices', ['company_id', 'due_date'], 'invoices_company_id_due_date_index');
        $this->addIndexIfMissing('invoices', ['quote_id'], 'invoices_quote_id_index');

        $this->addIndexIfMissing('attendances', ['company_id', 'user_id'], 'attendances_company_id_user_id_index');
        $this->addIndexIfMissing('attendances', ['company_id', 'status'], 'attendances_company_id_status_index');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('service_jobs', 'service_jobs_company_id_status_index');
        $this->dropIndexIfExists('service_jobs', 'service_jobs_company_id_assigned_user_id_index');
        $this->dropIndexIfExists('service_jobs', 'service_jobs_service_agreement_id_index');
        $this->dropIndexIfExists('service_jobs', 'service_jobs_agreement_id_index');
        $this->dropIndexIfExists('service_jobs', 'service_jobs_scheduled_at_index');

        $this->dropIndexIfExists('quotes', 'quotes_company_id_status_index');
        $this->dropIndexIfExists('quotes', 'quotes_company_id_customer_id_index');

        $this->dropIndexIfExists('invoices', 'invoices_company_id_status_index');
        $this->dropIndexIfExists('invoices', 'invoices_company_id_due_date_index');
        $this->dropIndexIfExists('invoices', 'invoices_quote_id_index');

        $this->dropIndexIfExists('attendances', 'attendances_company_id_user_id_index');
        $this->dropIndexIfExists('attendances', 'attendances_company_id_status_index');
    }

    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        if (! $this->hasColumns($table, $columns) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $name) {
            $table->index($columns, $name);
        });
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if (! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($name) {
            $table->dropIndex($name);
        });
    }

    private function hasColumns(string $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $schemaManager = $connection->getDoctrineSchemaManager();
        $tablePrefix = $connection->getTablePrefix();
        $indexes = $schemaManager->listTableIndexes($tablePrefix . $table);

        $normalized = array_change_key_case($indexes, CASE_LOWER);

        return array_key_exists(strtolower($indexName), $normalized);
    }
};
