<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $rootTables = [
            'customers',
            'sites',
            'service_jobs',
            'checklists',
            'quotes',
            'invoices',
            'payments',
            'attendances',
            'shifts',
            'timelogs',
            'leaves',
        ];

        foreach ($rootTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (! Schema::hasColumn($tableName, 'uuid')) {
                        $table->uuid('uuid')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'origin_node_id')) {
                        $table->unsignedBigInteger('origin_node_id')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'created_by_node_id')) {
                        $table->unsignedBigInteger('created_by_node_id')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'updated_by_node_id')) {
                        $table->unsignedBigInteger('updated_by_node_id')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'content_hash')) {
                        $table->string('content_hash')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'schema_version')) {
                        $table->string('schema_version')->nullable();
                    }
                    if (! Schema::hasColumn($tableName, 'sync_status')) {
                        $table->string('sync_status')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'last_local_modified_at')) {
                        $table->timestamp('last_local_modified_at')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'last_synced_at')) {
                        $table->timestamp('last_synced_at')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'tombstoned_at')) {
                        $table->timestamp('tombstoned_at')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'visibility_scope')) {
                        $table->string('visibility_scope')->nullable();
                    }
                    if (! Schema::hasColumn($tableName, 'encryption_scope')) {
                        $table->string('encryption_scope')->nullable();
                    }
                });
            }
        }

        $childTables = [
            'quote_items',
            'invoice_items',
            'user_support',
            'user_support_messages',
        ];

        foreach ($childTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (! Schema::hasColumn($tableName, 'uuid')) {
                        $table->uuid('uuid')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'origin_node_id')) {
                        $table->unsignedBigInteger('origin_node_id')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'updated_by_node_id')) {
                        $table->unsignedBigInteger('updated_by_node_id')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'content_hash')) {
                        $table->string('content_hash')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'sync_status')) {
                        $table->string('sync_status')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'last_synced_at')) {
                        $table->timestamp('last_synced_at')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'tombstoned_at')) {
                        $table->timestamp('tombstoned_at')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'parent_object_uuid')) {
                        $table->uuid('parent_object_uuid')->nullable()->index();
                    }
                });
            }
        }

        Schema::create('tz_nodes', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('node_name')->nullable();
            $table->string('node_type')->nullable();
            $table->string('platform')->nullable();
            $table->string('device_fingerprint')->nullable()->index();
            $table->string('trust_level')->nullable();
            $table->json('capabilities_json')->nullable();
            $table->string('key_fingerprint')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('last_sync_at')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tz_node_keys', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('node_id')->index();
            $table->uuid('key_uuid')->index();
            $table->string('key_type');
            $table->string('algorithm')->nullable();
            $table->string('public_key_ref')->nullable();
            $table->string('fingerprint')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_node_pairings', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('source_node_id')->index();
            $table->unsignedBigInteger('target_node_id')->index();
            $table->string('pairing_status')->nullable()->index();
            $table->string('pairing_token_hash')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_object_registry', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('object_uuid')->index();
            $table->string('object_table')->index();
            $table->unsignedBigInteger('object_id')->nullable()->index();
            $table->string('object_type')->nullable();
            $table->unsignedBigInteger('origin_node_id')->nullable()->index();
            $table->string('latest_hash')->nullable()->index();
            $table->string('latest_version_nonce')->nullable();
            $table->timestamp('latest_seen_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->timestamp('tombstoned_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tz_change_log', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('object_uuid')->index();
            $table->string('object_table')->index();
            $table->unsignedBigInteger('object_id')->nullable()->index();
            $table->uuid('change_uuid')->index();
            $table->uuid('parent_change_uuid')->nullable()->index();
            $table->unsignedBigInteger('node_id')->index();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->string('operation_type')->index();
            $table->string('payload_hash')->nullable()->index();
            $table->json('patch_json')->nullable();
            $table->string('full_payload_ref')->nullable();
            $table->timestamp('happened_at')->nullable()->index();
            $table->timestamp('applied_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tz_sync_outbox', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('node_id')->index();
            $table->uuid('object_uuid')->index();
            $table->string('object_table')->index();
            $table->uuid('change_uuid')->index();
            $table->string('destination_scope')->nullable()->index();
            $table->unsignedBigInteger('destination_node_id')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('available_at')->nullable()->index();
            $table->timestamp('last_attempt_at')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_sync_inbox', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('receiving_node_id')->nullable()->index();
            $table->unsignedBigInteger('source_node_id')->nullable()->index();
            $table->uuid('object_uuid')->index();
            $table->string('object_table')->index();
            $table->uuid('change_uuid')->index();
            $table->string('payload_hash')->nullable()->index();
            $table->json('payload_json')->nullable();
            $table->timestamp('received_at')->nullable()->index();
            $table->string('validation_status')->nullable()->index();
            $table->string('apply_status')->nullable()->index();
            $table->timestamp('applied_at')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_sync_conflicts', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('object_uuid')->index();
            $table->string('object_table')->index();
            $table->uuid('local_change_uuid')->nullable()->index();
            $table->uuid('remote_change_uuid')->nullable()->index();
            $table->string('local_hash')->nullable()->index();
            $table->string('remote_hash')->nullable()->index();
            $table->string('conflict_type')->nullable()->index();
            $table->json('conflict_payload_json')->nullable();
            $table->string('resolution_status')->nullable()->index();
            $table->unsignedBigInteger('resolved_by_user_id')->nullable()->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_tombstones', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('object_uuid')->index();
            $table->string('object_table')->index();
            $table->unsignedBigInteger('object_id')->nullable()->index();
            $table->unsignedBigInteger('node_id')->index();
            $table->timestamp('tombstoned_at')->index();
            $table->timestamp('propagated_at')->nullable()->index();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_sync_sessions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('session_uuid')->index();
            $table->unsignedBigInteger('source_node_id')->nullable()->index();
            $table->unsignedBigInteger('target_node_id')->nullable()->index();
            $table->string('sync_mode')->nullable()->index();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->unsignedInteger('objects_sent_count')->default(0);
            $table->unsignedInteger('objects_received_count')->default(0);
            $table->unsignedInteger('conflicts_count')->default(0);
            $table->text('error_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_signals', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('signal_uuid')->index();
            $table->string('signal_type')->index();
            $table->string('source_table')->nullable()->index();
            $table->uuid('source_object_uuid')->nullable()->index();
            $table->unsignedBigInteger('source_object_id')->nullable()->index();
            $table->unsignedBigInteger('source_node_id')->nullable()->index();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->json('payload_json')->nullable();
            $table->string('priority')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->timestamp('emit_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tz_signal_deliveries', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('signal_id')->index();
            $table->string('target_type')->index();
            $table->string('target_ref')->index();
            $table->string('delivery_status')->nullable()->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('delivered_at')->nullable()->index();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_signal_subscriptions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->string('subscriber_type')->index();
            $table->string('subscriber_ref')->index();
            $table->string('signal_type')->index();
            $table->json('filter_json')->nullable();
            $table->string('status')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tz_rewind_snapshots', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('snapshot_uuid')->index();
            $table->string('snapshot_scope')->index();
            $table->unsignedBigInteger('source_node_id')->nullable()->index();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_rewind_snapshot_items', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('snapshot_id')->index();
            $table->uuid('object_uuid')->index();
            $table->string('object_table')->index();
            $table->unsignedBigInteger('object_id')->nullable()->index();
            $table->string('object_hash')->nullable()->index();
            $table->string('payload_ref')->nullable();
            $table->timestamps();
        });

        Schema::create('tz_rewind_restores', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('snapshot_id')->index();
            $table->uuid('restore_uuid')->index();
            $table->string('target_scope')->index();
            $table->unsignedBigInteger('initiated_by_user_id')->nullable()->index();
            $table->unsignedBigInteger('source_node_id')->nullable()->index();
            $table->string('restore_status')->nullable()->index();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tzTables = [
            'tz_rewind_restores',
            'tz_rewind_snapshot_items',
            'tz_rewind_snapshots',
            'tz_signal_subscriptions',
            'tz_signal_deliveries',
            'tz_signals',
            'tz_sync_sessions',
            'tz_tombstones',
            'tz_sync_conflicts',
            'tz_sync_inbox',
            'tz_sync_outbox',
            'tz_change_log',
            'tz_object_registry',
            'tz_node_pairings',
            'tz_node_keys',
            'tz_nodes',
        ];

        foreach ($tzTables as $table) {
            Schema::dropIfExists($table);
        }

        $rootTables = [
            'customers',
            'sites',
            'service_jobs',
            'checklists',
            'quotes',
            'invoices',
            'payments',
            'attendances',
            'shifts',
            'timelogs',
            'leaves',
        ];

        foreach ($rootTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    foreach ([
                        'uuid',
                        'origin_node_id',
                        'created_by_node_id',
                        'updated_by_node_id',
                        'content_hash',
                        'schema_version',
                        'sync_status',
                        'last_local_modified_at',
                        'last_synced_at',
                        'tombstoned_at',
                        'visibility_scope',
                        'encryption_scope',
                    ] as $column) {
                        if (Schema::hasColumn($tableName, $column)) {
                            $table->dropColumn($column);
                        }
                    }
                });
            }
        }

        $childTables = [
            'quote_items',
            'invoice_items',
            'user_support',
            'user_support_messages',
        ];

        foreach ($childTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    foreach ([
                        'uuid',
                        'origin_node_id',
                        'updated_by_node_id',
                        'content_hash',
                        'sync_status',
                        'last_synced_at',
                        'tombstoned_at',
                        'parent_object_uuid',
                    ] as $column) {
                        if (Schema::hasColumn($tableName, $column)) {
                            $table->dropColumn($column);
                        }
                    }
                });
            }
        }
    }
};
