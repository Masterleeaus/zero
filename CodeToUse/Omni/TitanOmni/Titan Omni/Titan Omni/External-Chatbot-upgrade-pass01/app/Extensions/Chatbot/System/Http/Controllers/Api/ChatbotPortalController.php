<?php

namespace App\Extensions\Chatbot\System\Http\Controllers\Api;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotCustomer;
use App\Extensions\Chatbot\System\Models\ChatbotPortalFeedback;
use App\Extensions\Chatbot\System\Models\ChatbotPortalNotification;
use App\Extensions\Chatbot\System\Models\ChatbotPortalRecurringService;
use App\Extensions\Chatbot\System\Models\ChatbotPortalSiteProfile;
use App\Helpers\Classes\MarketplaceHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChatbotPortalController extends Controller
{
    public function overview(Chatbot $chatbot, string $sessionId): JsonResponse
    {
        $customer = ChatbotCustomer::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->where('session_id', $sessionId)
            ->first();

        $customerId = $customer?->getKey();

        $siteProfiles = ChatbotPortalSiteProfile::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->when($customerId, fn ($query) => $query->where('chatbot_customer_id', $customerId))
            ->orderBy('site_label')
            ->get([
                'id',
                'site_id',
                'site_label',
                'entry_method',
                'priority_rooms',
                'preferences',
            ]);

        $recurringServices = ChatbotPortalRecurringService::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->when($customerId, fn ($query) => $query->where('chatbot_customer_id', $customerId))
            ->orderBy('next_service_date')
            ->get([
                'id',
                'site_id',
                'service_name',
                'frequency',
                'next_service_date',
                'is_paused',
                'extras',
            ]);

        $notifications = ChatbotPortalNotification::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->when($customerId, fn ($query) => $query->where('chatbot_customer_id', $customerId))
            ->latest()
            ->limit(10)
            ->get([
                'id',
                'type',
                'title',
                'body',
                'action_label',
                'action_url',
                'payload',
                'read_at',
                'sent_at',
                'created_at',
            ]);

        return response()->json([
            'enabled' => (bool) $chatbot->getAttribute('is_customer_portal'),
            'home_title' => $chatbot->getAttribute('portal_home_title') ?: 'Customer portal',
            'primary_cta' => $chatbot->getAttribute('portal_primary_cta') ?: 'Book visit',
            'modules' => $chatbot->getAttribute('portal_modules') ?: [
                'dashboard',
                'bookings',
                'visits',
                'invoices',
                'feedback',
                'help',
            ],
            'quick_actions' => $chatbot->getAttribute('portal_quick_actions') ?: [
                ['key' => 'book_visit', 'label' => 'Book visit'],
                ['key' => 'reschedule', 'label' => 'Reschedule'],
                ['key' => 'pay_invoice', 'label' => 'Pay invoice'],
                ['key' => 'request_reclean', 'label' => 'Request re-clean'],
            ],
            'customer' => [
                'id' => $customer?->getKey(),
                'name' => $customer?->getAttribute('name'),
                'email' => $customer?->getAttribute('email'),
                'phone' => $customer?->getAttribute('phone'),
            ],
            'stats' => $this->stats($customerId),
            'site_profiles' => $siteProfiles,
            'recurring_services' => $recurringServices,
            'notifications' => $notifications,
        ]);
    }

    public function notifications(Chatbot $chatbot, string $sessionId): JsonResponse
    {
        $customer = ChatbotCustomer::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->where('session_id', $sessionId)
            ->first();

        $notifications = ChatbotPortalNotification::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->when($customer?->getKey(), fn ($query, $customerId) => $query->where('chatbot_customer_id', $customerId))
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    protected function stats(?int $customerId): array
    {
        $stats = [
            'open_feedback_items' => 0,
            'unread_notifications' => 0,
            'pending_invoices' => 0,
        ];

        if (! $customerId) {
            return $stats;
        }

        $stats['open_feedback_items'] = ChatbotPortalFeedback::query()
            ->where('chatbot_customer_id', $customerId)
            ->where('status', '!=', 'resolved')
            ->count();

        $stats['unread_notifications'] = ChatbotPortalNotification::query()
            ->where('chatbot_customer_id', $customerId)
            ->whereNull('read_at')
            ->count();

        if (MarketplaceHelper::isRegistered('workcore') && DB::getSchemaBuilder()->hasTable('invoices')) {
            $stats['pending_invoices'] = DB::table('invoices')
                ->where(function ($query) use ($customerId) {
                    $query->where('client_id', $customerId)
                        ->orWhere('user_id', $customerId)
                        ->orWhere('customer_id', $customerId);
                })
                ->whereIn('status', ['unpaid', 'partial'])
                ->count();
        }

        return $stats;
    }
}
