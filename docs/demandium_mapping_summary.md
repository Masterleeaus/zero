# Demandium bundle mapping (CodeToUse/demandium.zip)

Bundle location: `CodeToUse/demandium.zip` → extract to `/tmp/demandium` for inspection.

## Mobile apps (Flutter + GetX)
- **TitanGo** (mobile_apps/TitanGo) – worker/serviceman app. Keep login, assigned jobs, job detail, maps/navigation, chat, notifications, profile/settings, status updates. Extend for checklists, site/access notes, before/after photos, issue reporting, consumables, quick status actions, and offline queue for status/checklist/proof.
- **TitanCommand** (mobile_apps/TitanCommand) – provider/operator app. Keep dashboard, bookings, team/staff, reviews/reports, business settings, chat, notifications. Add dispatch board, assign/reassign worker, service-area filtering, proof review, issue queue, blocked/missed job handling, customer communication shortcuts. Reduce ads/promotions/subscription sales/marketplace semantics.
- **TitanNexus** (mobile_apps/TitanNexus) – customer app. Keep auth, booking flow/history, chat, notifications, addresses/maps, profile/settings, reviews. Add rebook flow, live job status timeline, property/site notes, service preferences, issue reporting, invoice/payment visibility. De-emphasize marketplace browsing, cart-heavy checkout, referrals/loyalty.

## Marketplace baggage to strip or down-scope
- Customer app: cart/checkout, coupons, loyalty points, offers/promotions, refer & earn, custom posts, favorites/wishlist, provider discovery prominence.
- Operator app: advertisements/promotions, custom post management, subscription sales emphasis.
- Backend module targets for removal or stubbing: `PromotionManagement` (ads/coupons/campaigns/banners), bonus/loyalty surfaces in `PaymentModule`, marketplace discovery bits in `ServiceManagement` (favorites), custom post endpoints in `CustomerModule`.

## Backend modules to keep/adapt for MagicAI
- Core flows: `BookingModule`, `ChattingModule`, `ReviewModule`, `ServicemanModule`, `ProviderManagement`, `CustomerModule` (profiles/addresses), `ZoneManagement`, `CategoryManagement`, `BusinessSettingsModule` (config/bootstrap, AI settings), notification token/update logic, worker assignment and booking status transitions.
- Optional/payments: keep gateway hooks only as needed for booking invoices; defer wallet/bonus/loyalty.
- API direction: prefer `/api/titan/{customer|operator|worker}/*` shims mapped over existing controllers to reduce breakage during transition.

## AI/MagicAI touchpoints
- AI settings already present in `backend_modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/ai-settings.blade.php` and module config; OpenAI SDK included in core composer.json. Preserve and re-use for MagicAI bootstrap payloads.

## Notes
- The bundle already uses Titan-aligned app names; keep architecture, navigation shells, and state management intact while pruning marketplace-specific UI and wiring.
- Start changes inside the mobile apps first (UI/domain terminology) before renaming or removing backend internals; add compatibility shims where needed.
