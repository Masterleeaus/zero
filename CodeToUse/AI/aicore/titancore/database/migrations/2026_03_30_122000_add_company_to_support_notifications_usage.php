<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Support tickets
        if (Schema::hasTable('user_support')) {
            Schema::table('user_support', static function (Blueprint $table) {
                if (! Schema::hasColumn('user_support', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('user_id')->index();
                }
            });

            if (Schema::hasColumn('users', 'company_id')) {
                DB::table('user_support as s')
                    ->join('users as u', 's.user_id', '=', 'u.id')
                    ->whereNull('s.company_id')
                    ->update(['s.company_id' => DB::raw('u.company_id')]);
            }
        }

        if (Schema::hasTable('user_support_messages')) {
            Schema::table('user_support_messages', static function (Blueprint $table) {
                if (! Schema::hasColumn('user_support_messages', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('user_support_id')->index();
                }
            });

            if (Schema::hasTable('user_support')) {
                DB::table('user_support_messages as m')
                    ->join('user_support as s', 'm.user_support_id', '=', 's.id')
                    ->whereNull('m.company_id')
                    ->update(['m.company_id' => DB::raw('s.company_id')]);
            }
        }

        // Notifications
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', static function (Blueprint $table) {
                if (! Schema::hasColumn('notifications', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('notifiable_id')->index();
                }
            });

            if (Schema::hasColumn('users', 'company_id')) {
                DB::table('notifications as n')
                    ->where('notifiable_type', '=', 'App\\\\Models\\\\User')
                    ->join('users as u', 'n.notifiable_id', '=', 'u.id')
                    ->whereNull('n.company_id')
                    ->update(['n.company_id' => DB::raw('u.company_id')]);
            }
        }

        // Usage (AI metrics)
        if (Schema::hasTable('usage')) {
            Schema::table('usage', static function (Blueprint $table) {
                if (! Schema::hasColumn('usage', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_support')) {
            Schema::table('user_support', static function (Blueprint $table) {
                if (Schema::hasColumn('user_support', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }

        if (Schema::hasTable('user_support_messages')) {
            Schema::table('user_support_messages', static function (Blueprint $table) {
                if (Schema::hasColumn('user_support_messages', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', static function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }

        if (Schema::hasTable('usage')) {
            Schema::table('usage', static function (Blueprint $table) {
                if (Schema::hasColumn('usage', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }
    }
};
