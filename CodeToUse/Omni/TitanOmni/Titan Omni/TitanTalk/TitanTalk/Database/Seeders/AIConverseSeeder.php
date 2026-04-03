<?php
namespace Modules\TitanTalk\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\TitanTalk\Models\Intent;
use Modules\TitanTalk\Models\Entity;
use Modules\TitanTalk\Models\TrainingPhrase;

class AIConverseSeeder extends Seeder
{
    public function run()
    {
        $intent = Intent::firstOrCreate(['name'=>'greeting'], ['description'=>'General greetings']);
        $entity = Entity::firstOrCreate(['name'=>'affirmation'], ['values'=>['yes','yep','sure','ok']]);
        TrainingPhrase::firstOrCreate(['intent_id'=>$intent->id,'text'=>'hello']);
        TrainingPhrase::firstOrCreate(['intent_id'=>$intent->id,'text'=>'hi there']);
        TrainingPhrase::firstOrCreate(['intent_id'=>$intent->id,'text'=>'good morning']);
    }
}
