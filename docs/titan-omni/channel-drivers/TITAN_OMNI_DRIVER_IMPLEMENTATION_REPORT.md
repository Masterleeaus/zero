# TITAN OMNI — Pass 04: Driver Implementation Report

## Overview

Pass 04 builds the transport abstraction layer for Titan Omni. It establishes a clean
contract hierarchy, seven concrete channel drivers, a driver registry, extended channel
config, and service-container wiring.

---

## What Was Scanned

| Source | Purpose |
|--------|---------|
| Pass 01 schema migrations (`database/migrations/omni/`) | Confirmed channel types in use |
| Pass 02 models (`app/Models/Omni/`) | Confirmed OmniChannelBridge config patterns |
| Pass 03 services (`app/Services/Omni/`) | Used OmniChannelService as integration anchor reference |
| `app/Contracts/SchedulableEntity.php` | Interface style reference |
| `app/Contracts/TitanIntegration/ZeroSignalBridgeContract.php` | Contract naming pattern |
| `config/titan_omni.php` | Existing voice/whatsapp/twilio/telegram settings (read before editing) |
| `app/Providers/TitanCoreServiceProvider.php` | Singleton registration patterns |
| `tests/Feature/Omni/OmniPass03Test.php` | Test structure and assertion patterns |

---

## Donor Code Reused

No donor code was directly transplanted. All drivers were authored from first principles
using the existing config keys already present in `config/titan_omni.php` (twilio_sid,
vapi_api_key, bland_api_key, telegram_token, meta_app_id, etc.) to ensure zero
credential duplication.

---

## Contracts Created

All contracts live in `app/Contracts/Omni/`.

| Contract | Extends | Methods |
|----------|---------|---------|
| `OmniDriverContract` | — | `getChannelType()`, `isConfigured()`, `ping()` |
| `OutboundDriverContract` | `OmniDriverContract` | `send()`, `sendBatch()` |
| `InboundDriverContract` | `OmniDriverContract` | `verify()`, `normalize()` |
| `DeliveryStatusContract` | `OmniDriverContract` | `parseStatus()` |
| `ProviderAuthContract` | `OmniDriverContract` | `authenticate()`, `refreshCredentials()`, `getCredentials()` |

---

## Abstract Base Driver

`app/Services/Drivers/AbstractOmniDriver.php`

- Implements `OmniDriverContract`
- Config injection via constructor
- `isConfigured()` iterates `requiredConfigKeys()`
- `ping()` delegates to `isConfigured()`
- `log()` emits `Log::info("omni.driver.{channel}.{event}", ...)` structured entries

---

## Drivers Created

All drivers live in `app/Services/Drivers/`.

| Driver | `getChannelType()` | Implements | Required Config Keys |
|--------|--------------------|------------|---------------------|
| `SmsDriver` | `sms` | Outbound, Inbound, DeliveryStatus | `sid`, `token`, `from` |
| `EmailDriver` | `email` | Outbound | `from_address`, `from_name` |
| `WhatsAppMetaDriver` | `whatsapp_meta` | Outbound, Inbound, DeliveryStatus | `app_id`, `app_secret`, `verify_token` |
| `WhatsAppTwilioDriver` | `whatsapp_twilio` | Outbound, Inbound, DeliveryStatus | `sid`, `token`, `from` (extends `SmsDriver`) |
| `TelegramDriver` | `telegram` | Outbound, Inbound | `token` |
| `WebchatDriver` | `webchat` | Outbound, Inbound | _(none — always configured)_ |
| `VoiceDriver` | `voice` | Outbound, Inbound | `provider` + `vapi_api_key` or `bland_api_key` |

### Design Notes

- **No real HTTP calls** in any driver — external dispatch is isolated in a
  `protected dispatchToProvider(array $payload): array` method that can be overridden in tests.
- **WhatsAppTwilioDriver** extends `SmsDriver` and strips the `whatsapp:` number prefix in `normalize()`.
- **WebchatDriver** `isConfigured()` always returns `true`; `send()` returns `status: delivered` immediately.
- **VoiceDriver** `requiredConfigKeys()` is dynamic — it adds `vapi_api_key` or `bland_api_key` depending on `config.provider`.
- **SmsDriver** / **WhatsAppMetaDriver** `verify()` checks structural presence of signature headers; production subclasses should override with full HMAC validation.

---

## Registry Created

`app/Services/Omni/OmniDriverRegistry`

| Method | Returns |
|--------|---------|
| `register(OmniDriverContract)` | void |
| `get(string $channelType)` | `OmniDriverContract` (throws `RuntimeException` if missing) |
| `has(string $channelType)` | bool |
| `all()` | `array<string, OmniDriverContract>` |
| `allOutbound()` | `array<string, OutboundDriverContract>` |
| `allInbound()` | `array<string, InboundDriverContract>` |
| `configured()` | `array<string, OmniDriverContract>` (only `isConfigured() === true`) |

---

## Config Added

Two new top-level keys appended to `config/titan_omni.php`:

- **`channels`** — per-channel `driver`, `enabled` flag, and channel-level settings
- **`drivers`** — credential/config bags passed into each driver constructor

All values are pulled from environment variables. No credentials are hard-coded.

---

## Service Container Registration

`app/Providers/TitanCoreServiceProvider::register()` now includes an
`OmniDriverRegistry` singleton that instantiates and registers all 7 drivers
from `config('titan_omni.drivers.*')`.

---

## Test Coverage

`tests/Feature/Omni/OmniPass04Test.php` — 25 test methods covering:

1. All 5 contract interfaces exist and have correct methods (via Reflection)
2. `AbstractOmniDriver` is abstract and implements `OmniDriverContract`
3. All 7 drivers instantiate
4. Channel type strings match expected values
5. `WebchatDriver` is always configured
6. `SmsDriver` `isConfigured()` responds to config presence
7. Registry: `register`, `has`, `get`, `all`, `allOutbound`, `allInbound`, `configured`
8. `config/titan_omni.php` has `channels` and `drivers` keys with all 7 entries
9. Driver contract implementation matrix (which driver implements which contracts)
10. `send()`/`normalize()`/`parseStatus()` smoke tests (no real provider calls)
11. `OmniDriverRegistry` resolves from the service container with all 7 drivers

---

## What Remains for Pass 05

| Item | Notes |
|------|-------|
| Inbound webhook controllers | Route inbound payloads to the correct driver's `verify()` + `normalize()` |
| Outbound job/queue wiring | Dispatch `send()` via queued jobs with retry logic using `channels.*.retry_attempts` |
| Full HMAC validation | Replace structural-presence checks in `SmsDriver::verify()` and `WhatsAppMetaDriver::verify()` with production-grade HMAC-SHA1/SHA256 |
| `ProviderAuthContract` implementations | Token refresh lifecycle for Meta and VAPI |
| Channel bridge integration | Wire `OmniDriverRegistry::get()` into `OmniChannelService` dispatch path |
| Pass 05 test | Webhook controller routing and queue dispatch validation |
