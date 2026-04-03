# TITAN OMNI — PASS 04: Driver Contracts + Registry + Channel Config

**Labels:** `titan-omni` `pass-04` `drivers` `channels` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Pass 03 complete

---

## Global Instruction

Re-read all prior pass outputs before building. Use `docs/titan-omni/`, `CodeToUse/`, and existing host transport/config code. Do not put business logic inside drivers.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Build the transport abstraction layer — driver contracts, registry, and channel configuration.

---

## Read First — Docs

- `docs/titan-omni/channel-drivers/TITAN_OMNI_CHANNEL_DRIVERS.md`
- `docs/titan-omni/channel-drivers/TITAN_OMNI_DRIVER_REGISTRY.md`
- `docs/titan-omni/configs/TITAN_OMNI_CONFIG_SURFACE.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_PROVIDER_MAP.md`
- `docs/titan-omni/models/TITAN_OMNI_MODEL_IMPLEMENTATION_REPORT.md` (Pass 03 output)

---

## Read First — CodeToUse

- `CodeToUse/comms*` — any webhook/transport/channel adapter code
- `CodeToUse/TitanTalk*` — any call/voice transport code
- `CodeToUse/extension_library*` — any plugin/extension registration patterns
- `CodeToUse/utilities*` — retry/fallback/webhook utilities
- Existing `app/Domains/Entity/Drivers/` in host — understand driver interface pattern already in use

---

## Build

### Driver Interface / Contracts
- `app/Omni/Contracts/ChannelDriverInterface.php`
  - `send(Message $message): DeliveryResult`
  - `receive(array $payload): InboundEvent`
  - `normalise(array $payload): NormalisedPayload`
  - `resolveIdentity(NormalisedPayload $payload): IdentityCandidate`
  - `supportsAttachments(): bool`
  - `supportsVoice(): bool`

### Driver Registry
- `app/Omni/Registry/ChannelDriverRegistry.php`
  - `register(string $channel, string $driverClass): void`
  - `resolve(string $channel): ChannelDriverInterface`
  - `available(): array`
  - `isEnabled(string $channel): bool`

### Channel Drivers (stubs or real where credentials available)
- `app/Omni/Drivers/SmsDriver.php`
- `app/Omni/Drivers/EmailDriver.php`
- `app/Omni/Drivers/WhatsAppDriver.php`
- `app/Omni/Drivers/TelegramDriver.php`
- `app/Omni/Drivers/MessengerDriver.php`
- `app/Omni/Drivers/VoiceDriver.php`
- `app/Omni/Drivers/WebChatDriver.php`

Each driver must implement:
- Inbound normalisation
- Outbound send
- Identity extraction
- Fallback behaviour when credentials absent

### Config
- `config/titan-omni.php`
  - Enabled channels
  - Provider credentials (env-driven)
  - Retry limits
  - Attachment size limits
  - Fallback channel order

### Service Provider
- `app/Providers/TitanOmniServiceProvider.php`
  - Registers driver registry
  - Loads config
  - Binds contracts to implementations
  - Registers routes

---

## Rules

- Drivers are transport-only — no business logic inside them
- Centralise all registration and config in registry + service provider
- Make stubs clean and safe when provider credentials are absent — must not throw on boot
- Every driver must be independently testable in isolation
- Config must be env-driven, not hardcoded

---

## Required Output

- Contracts in `app/Omni/Contracts/`
- Registry in `app/Omni/Registry/`
- Drivers in `app/Omni/Drivers/`
- Config file `config/titan-omni.php`
- Service provider `app/Providers/TitanOmniServiceProvider.php`
- `docs/titan-omni/channel-drivers/TITAN_OMNI_DRIVER_IMPLEMENTATION_REPORT.md`

---

## Pass Delivery Rules

Output must include:
1. What was scanned
2. What donor driver/transport code was reused
3. What host driver patterns were followed
4. What files were created
5. What docs were updated
6. What remains for Pass 05
