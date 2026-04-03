<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'user_openai',
            'user_openai_chat',
            'user_openai_chat_messages',
            'chatbot',
            'chatbot_data',
            'chatbot_data_vectors',
            'chatbot_history',
        ];

        foreach ($tables as $table) {
            Schema::table($table, static function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'company_id')) {
                    $blueprint->unsignedBigInteger('company_id')->nullable()->index()->after('id');
                }
            });
        }

        // Backfill from users where possible
        if (Schema::hasTable('users')) {
            DB::table('user_openai as u')
                ->join('users as usr', 'u.user_id', '=', 'usr.id')
                ->whereNull('u.company_id')
                ->update(['u.company_id' => DB::raw('usr.company_id')]);

            DB::table('user_openai_chat as c')
                ->join('users as usr', 'c.user_id', '=', 'usr.id')
                ->whereNull('c.company_id')
                ->update(['c.company_id' => DB::raw('usr.company_id')]);

            DB::table('user_openai_chat_messages as m')
                ->join('users as usr', 'm.user_id', '=', 'usr.id')
                ->whereNull('m.company_id')
                ->update(['m.company_id' => DB::raw('usr.company_id')]);

            DB::table('chatbot as cb')
                ->join('users as usr', 'cb.user_id', '=', 'usr.id')
                ->whereNull('cb.company_id')
                ->update(['cb.company_id' => DB::raw('usr.company_id')]);
        }

        // Backfill dependent tables from parents
        if (Schema::hasTable('chatbot') && Schema::hasTable('chatbot_data')) {
            DB::table('chatbot_data as d')
                ->join('chatbot as cb', 'd.chatbot_id', '=', 'cb.id')
                ->whereNull('d.company_id')
                ->update(['d.company_id' => DB::raw('cb.company_id')]);
        }

        if (Schema::hasTable('chatbot_data') && Schema::hasTable('chatbot_data_vectors')) {
            DB::table('chatbot_data_vectors as v')
                ->join('chatbot_data as d', 'v.chatbot_data_id', '=', 'd.id')
                ->whereNull('v.company_id')
                ->update(['v.company_id' => DB::raw('d.company_id')]);
        }

        if (Schema::hasTable('user_openai_chat') && Schema::hasTable('chatbot_history')) {
            DB::table('chatbot_history as h')
                ->join('user_openai_chat as c', 'h.user_openai_chat_id', '=', 'c.id')
                ->whereNull('h.company_id')
                ->update(['h.company_id' => DB::raw('c.company_id')]);
        }
    }

    public function down(): void
    {
        $tables = [
            'user_openai',
            'user_openai_chat',
            'user_openai_chat_messages',
            'chatbot',
            'chatbot_data',
            'chatbot_data_vectors',
            'chatbot_history',
        ];

        foreach ($tables as $table) {
            Schema::table($table, static function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'company_id')) {
                    $blueprint->dropColumn('company_id');
                }
            });
        }
    }
};
