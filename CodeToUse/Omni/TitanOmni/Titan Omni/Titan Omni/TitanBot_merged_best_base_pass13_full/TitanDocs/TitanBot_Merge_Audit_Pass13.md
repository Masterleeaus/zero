# TitanBot Merge Audit - Pass 13

## Base Selection

- Selected base: `TitanBot_overlay_pass11_full.zip` via pass12 merged artifact.

- Reason: newest overlay build, largest functional surface, includes bridge/ingress/overlay/dashboard additions, and is a clean superset of pass6.


## Deep Scan Result

### TitanBot_original

- ZIP: `TitanBot.zip`

- Different files vs current base: **9**

- Missing files vs current base: **0**

- Snapshotted into `Sources/TitanBotMergeSnapshots/` for safe future cherry-picks.

  - `app/Extensions/Chatbot/extension.json`

  - `app/Extensions/Chatbot/System/ChatbotServiceProvider.php`

  - `app/Extensions/Chatbot/System/Models/Chatbot.php`

  - `app/Extensions/Chatbot/System/Models/ChatbotChannel.php`

  - `app/Extensions/Chatbot/System/Models/ChatbotChannelWebhook.php`

  - `app/Extensions/Chatbot/System/Models/ChatbotConversation.php`

  - `app/Extensions/Chatbot/System/Models/ChatbotCustomer.php`

  - `app/Extensions/Chatbot/System/Models/ChatbotHistory.php`

  - `app/Extensions/Chatbot/System/Models/ChatbotPageVisit.php`


### TitanBot_pass3_big

- ZIP: `TitanBot_overlay_pass3_big.zip`

- Different files vs current base: **17**

- Missing files vs current base: **0**

- Snapshotted into `Sources/TitanBotMergeSnapshots/` for safe future cherry-picks.

  - `app/Extensions/Chatbot/resources/views/overlay/command/index.blade.php`

  - `app/Extensions/Chatbot/resources/views/overlay/command/show.blade.php`

  - `app/Extensions/Chatbot/resources/views/overlay/command/analytics.blade.php`

  - `app/Extensions/Chatbot/resources/views/overlay/command/partials/channel-cards.blade.php`

  - `app/Extensions/Chatbot/resources/views/overlay/agent/index.blade.php`

  - `app/Extensions/Chatbot/resources/views/overlay/agent/show.blade.php`

  - `app/Extensions/Chatbot/resources/views/overlay/customer/index.blade.php`

  - `app/Extensions/Chatbot/resources/views/overlay/customer/show.blade.php`

  - `app/Extensions/Chatbot/resources/views/overlay/layout.blade.php`

  - `app/Extensions/Chatbot/System/ChatbotServiceProvider.php`

  - `app/Extensions/Chatbot/System/Http/Controllers/Overlay/ChatbotCommandController.php`

  - `app/Extensions/Chatbot/System/Http/Controllers/Overlay/ChatbotAgentController.php`

  - `app/Extensions/Chatbot/System/Http/Controllers/Overlay/ChatbotCustomerPortalController.php`

  - `app/Extensions/Chatbot/System/Models/Chatbot.php`

  - `app/Extensions/Chatbot/System/Models/ChatbotChannel.php`

  - `app/Extensions/Chatbot/System/Models/ChatbotConversation.php`

  - `app/Extensions/Chatbot/System/Services/Channels/ChannelWebhookService.php`


### TitanBot_pass6_full

- ZIP: `TitanBot_overlay_pass6_full.zip`

- Different files vs current base: **0**

- Missing files vs current base: **0**

- No code differences requiring snapshot export.


### TitanBot_pass11_full

- ZIP: `TitanBot_overlay_pass11_full.zip`

- Different files vs current base: **0**

- Missing files vs current base: **0**

- No code differences requiring snapshot export.


## Notes

- Base PHP files lint clean under `php -l`.

- Pass3 contains older overlay views/controllers; current base versions are richer and were retained.

- Original TitanBot contains older core model/provider variants; current base kept newer tenancy/channel bridge logic.

- This pass preserves all alternative implementations in-source so later agents can reuse, diff, or restore specific behaviors without rescanning the raw ZIPs.
