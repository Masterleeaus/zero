# Titan Nexus Spark Pack — Deep Scan Summary

Scanned source archives in `/mnt/data`:
- TitanZero_Docs.zip
- dashbiardvthemes.zip
- analyst-magicai-master.zip
- NexusSuite Full Suite v1.0.zip
- MarketingBot.zip
- MarketingBot_Integrated_v4_CommsUpgrades.zip
- socialmedia.zip
- TitanHelloBase.zip
- TitanTalk.zip
- Menu.zip
- MegaMenu.zip
- comms.zip
- extension_library.zip
- nexus-field-ops-main.zip
- utilities.zip
- customextensions .zip

## Relevant findings

### Latest docs pack
`TitanZero_Docs.zip` contains `TitanZero_Docs_Cleaned_v10/` and an embedded Nexus docs bundle:
- `07_INTEGRATION_MIGRATION_AND_CONSOLIDATION/Nexus_Engine_Docs_Pass1_2_3_4_5_6_7_8_9_10_11_12_13_14_15_16.zip`

### Important doc paths confirmed
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/MOBILE_STACK_ALIGNMENT.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md`
- `04_FINANCE_AND_MONEY/DOC82_Finance_Mode_Blueprint.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md`
- `05_DATA_SCHEMA_AND_TABLES/48_LIFECYCLE_STATE_TABLE_SCHEMA.md`
- nested Nexus bundle docs such as `DOC12_Scout_Layer.md`, `DOC18_View_Surface_Map.md`, `DOC22_Lifecycle_State_Machine.md`, `DOC23_Mode_Lifecycle_Overlays.md`, `DOC80_Jobs_Mode_Blueprint.md`, `DOC83_Admin_Mode_Blueprint.md`, `DOC145_Final_Prebuild_Checklist.md`

### Theme / UI sources confirmed
`dashbiardvthemes.zip` contains:
- `default/`
- `classic/`
- `bolt/`
- `marketing-bot-dashboard/`
- `social-media-dashboard/`
- `sleek/`

### Comms and dashboard sources confirmed
- `MarketingBot.zip` contains inbox, contacts, settings, telegram campaign, marketing conversation tables and views
- `socialmedia.zip` contains AiSocialMedia extension and related views/migrations
- `Menu.zip` and `MegaMenu.zip` contain menu-related sources

## Drift still present in docs
Even after canonical naming cleanup, the docs set still reflects older assumptions in places. The GitHub agent must reconcile:
- old five-mode Nexus language
- old Comms-in-Nexus language
- old Social Mode language

Canonical current model:
- Omni owns comms
- Nexus owns operational surfaces
- Nexus hubs = Work, Money, Office, Grow
- Nexus Home = chat-first launcher with 5 cards: Work, Money, Office, Grow, Omni
- Spark output = modular mini React apps that remain PWA-friendly and composable into Nexus
