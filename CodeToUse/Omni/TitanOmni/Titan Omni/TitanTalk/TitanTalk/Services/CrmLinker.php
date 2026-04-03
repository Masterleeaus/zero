<?php
namespace Modules\TitanTalk\Services;

use Modules\TitanTalk\Models\Conversation;
use Illuminate\Support\Facades\DB;

class CrmLinker
{
    /**
     * Very simple auto-link: try to match external_ref to client phone or email.
     * You can expand this to your real Worksuite schema.
     */
    public function linkConversation(Conversation $conversation): void
    {
        $external = $conversation->external_ref;

        if (! $external) {
            return;
        }

        try {
            // Example: assume clients table has phone and email
            if (DB::connection()->getSchemaBuilder()->hasTable('clients')) {
                $client = DB::table('clients')
                    ->where('mobile', $external)
                    ->orWhere('phone', $external)
                    ->orWhere('email', $external)
                    ->select('id')
                    ->first();

                if ($client && ! $conversation->client_id) {
                    $conversation->client_id = $client->id;
                    $conversation->save();
                }
            }
        } catch (\Throwable $e) {
            // Swallow errors – auto-link should never break webhooks
        }
    }
}
