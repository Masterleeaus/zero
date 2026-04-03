<?php
namespace Modules\TitanTalk\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\TitanTalk\Models\Channel;

class AIConverseChannelsSeeder extends Seeder
{
    public function run()
    {
        Channel::firstOrCreate(['name'=>'Web'], ['driver'=>'web','enabled'=>true,'config'=>[]]);
        Channel::firstOrCreate(['name'=>'WhatsApp'], ['driver'=>'whatsapp','enabled'=>false,'config'=>['provider'=>'twilio']]);
        Channel::firstOrCreate(['name'=>'Telegram'], ['driver'=>'telegram','enabled'=>false,'config'=>['bot_token'=>'']]);
    }
}
