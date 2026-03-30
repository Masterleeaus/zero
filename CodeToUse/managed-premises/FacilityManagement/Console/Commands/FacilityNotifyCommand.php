<?php

namespace Modules\FacilityManagement\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use Modules\FacilityManagement\Entities\{Doc,Inspection};
use Modules\FacilityManagement\Notifications\{DocExpiringNotification,InspectionOverdueNotification};

class FacilityNotifyCommand extends Command
{
    protected $signature = 'facility:notify';
    protected $description = 'Send notifications for expiring docs and overdue inspections';

    public function handle()
    {
        $days = Config::get('facility.notify.doc_expiry_days', 30);
        $hours = Config::get('facility.notify.inspection_overdue_hours', 24);
        $userId = Config::get('facility.notify.notify_user_id');

        $user = $userId ? User::find($userId) : User::first();
        if (!$user) { $this->warn('No user found to notify.'); return self::SUCCESS; }

        // Docs expiring within N days
        $docs = Doc::query()->whereNotNull('expires_at')->whereDate('expires_at','<=', now()->addDays($days))->get();
        foreach ($docs as $doc) {
            $user->notify(new DocExpiringNotification($doc));
        }
        $this->info('Doc expiry notifications: '.count($docs));

        // Inspections overdue
        $ins = Inspection::query()->where('status','scheduled')->whereNotNull('scheduled_at')->where('scheduled_at','<=', now()->subHours($hours))->get();
        foreach ($ins as $i) {
            $user->notify(new InspectionOverdueNotification($i));
        }
        $this->info('Inspection overdue notifications: '.count($ins));

        return self::SUCCESS;
    }
}
