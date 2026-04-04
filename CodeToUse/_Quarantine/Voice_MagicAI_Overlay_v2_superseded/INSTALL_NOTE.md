# MagicAI Titan Voice Minimal Overlay

This overlay contains only additive files needed to bring the Titan Voice suite into an existing MagicAI site.

## Included
- app/Extensions/ChatbotVoice
- app/Extensions/ChatbotWhatsapp
- app/Extensions/ElevenlabsVoiceChat
- app/Extensions/ChatbotMessenger
- app/Extensions/ChatbotTelegram
- shared root additions under app/Services, app/Models, config, database/migrations
- TitanDocs/TitanVoice for merge/install reference

## Sources merged
- TitanVoiceSuite_Unified_Merged_From_Largest_Base.zip
- ChatbotMessenger.zip
- ChatbotTelegram.zip

## Notes
- Mac junk files were stripped.
- ChatbotWhatsapp keeps original `Twillio` path and also includes `Twilio` alias copies.
- This ZIP does not include the MagicAI core site files.
- Review route/provider registration before production enablement.
