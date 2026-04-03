# TITAN OMNI — PASS 07: Outbound Send + Delivery Tracking

**Labels:** `titan-omni` `pass-07` `outbound` `delivery` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Pass 06 complete

---

## Global Instruction

Re-read all prior pass outputs before building. Every outbound attempt must leave a delivery record. Provider-specific logic stays in drivers only. Separate send logic from UI completely.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Make Omni able to send communications outbound and track delivery state end-to-end.

---

## Read First — Docs

- `docs/titan-omni/channel-drivers/TITAN_OMNI_CHANNEL_DRIVERS.md`
- `docs/titan-omni/services/TITAN_OMNI_SERVICE_BINDINGS.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_ROUTE_MAP.md`
- `docs/titan-omni/configs/TITAN_OMNI_CONFIG_SURFACE.md`
- `docs/titan-omni/channel-drivers/TITAN_OMNI_DRIVER_IMPLEMENTATION_REPORT.md` (Pass 04 output)
- `docs/titan-omni/conversation-graph/TITAN_OMNI_UI_IMPLEMENTATION_REPORT.md` (Pass 06 output)

---

## Read First — CodeToUse

- `CodeToUse/MarketingBot*` — send/retry/status tracking logic
- `CodeToUse/comms*` — outbound channel send patterns
- `CodeToUse/TitanTalk*` — voice outbound / call initiation patterns
- Any donor send/retry/delivery-status code

---

## Build

### Send Message Service
- `app/Omni/Services/Outbound/OutboundSendService.php`
  - `send(Conversation $conversation, array $messageData, User $sender): Message`
  - `sendToContact(Contact $contact, string $channel, array $messageData): Message`
  - `resolvePreferredChannel(Contact $contact): string`
  - `dispatchViaDriver(Message $message): DeliveryResult`
  - `recordDeliveryAttempt(Message $message, DeliveryResult $result): Delivery`

### Retry Pipeline
- `app/Omni/Services/Outbound/DeliveryRetryService.php`
  - `shouldRetry(Delivery $delivery): bool`
  - `retry(Delivery $delivery): DeliveryResult`
  - `markFailed(Delivery $delivery, string $reason): void`
- `app/Jobs/Omni/RetryFailedDelivery.php` — queued retry job

### Delivery State Machine
States: `pending` → `queued` → `sent` → `delivered` → `read` | `failed` | `bounced`
- State transitions persisted to `deliveries` table
- Every state change logged to audit trail

### Provider Status Callbacks (where available)
- `app/Http/Controllers/Omni/Webhooks/DeliveryStatusController.php`
  - `smsStatus(Request $request)` — Twilio/Vonage delivery receipts
  - `emailStatus(Request $request)` — SendGrid/Mailgun bounce/delivery webhooks
  - `whatsappStatus(Request $request)` — Meta delivery callbacks
- Routes in `routes/omni/webhooks.php`

### Attachment Send Support
- `app/Omni/Services/Outbound/AttachmentSendService.php`
  - `prepareAttachment(UploadedFile $file, string $channel): PreparedAttachment`
  - `attachToMessage(Message $message, PreparedAttachment $attachment): Attachment`

### Outbound Controller (thin)
- `app/Http/Controllers/Omni/OutboundController.php`
  - `send(Request $request, Conversation $conversation)` — operator sends from thread view
  - `sendQuick(Request $request, Contact $contact)` — quick message without existing conversation

---

## Rules

- Separate send logic from UI — controller calls service only
- Every outbound attempt must create a `Delivery` record before the driver is called
- Failed states must be fully inspectable — reason, attempt count, timestamps
- Provider-specific logic stays in drivers (Pass 04) — `OutboundSendService` only orchestrates
- Attachment handling must check driver `supportsAttachments()` before attempting
- Retry must respect per-channel limits from `config/titan-omni.php`

---

## Required Output

- Services in `app/Omni/Services/Outbound/`
- Jobs in `app/Jobs/Omni/`
- Controllers in `app/Http/Controllers/Omni/` and `app/Http/Controllers/Omni/Webhooks/`
- Routes in `routes/omni/webhooks.php`
- `docs/titan-omni/channel-drivers/TITAN_OMNI_OUTBOUND_IMPLEMENTATION_REPORT.md`

---

## Pass Delivery Rules

1. What was scanned
2. What donor send/retry code was reused
3. What host code was extended
4. What files were created
5. What docs were updated
6. What remains for Pass 08
