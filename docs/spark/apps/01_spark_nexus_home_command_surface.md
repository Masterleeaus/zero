# [spark] Nexus Home Command Surface

## 1. Purpose
Build the primary Nexus entry surface: a mobile-first, chat-first command hub with 5 zoom cards for Work, Money, Office, Grow, and Omni. This is the front door of the product and the routing layer for every later mini-app.

## 2. Why this app is priority
It is the front door of the product and the routing layer for every later mini-app. Without this surface, the other nine Spark mini-apps have no unified launch point.

## 3. Mobile-first UX requirements
- Single-column layout by default
- Sticky compact signal strip
- Primary chat box above all cards
- 5 large thumb-friendly cards (Work, Money, Office, Grow, Omni)
- Suggested actions list between chat and cards

## 4. Chat-launch / wizard-launch behavior
- User intent in chat must route into Work, Money, Office, Grow, or Omni
- Suggested actions should deep-link to the target wizard or hub panel
- Chat should be able to launch the other 9 Spark mini-apps when their intent matches

## 5. Data boundaries and persistence rules
- Do not store operational truth in the UI runtime
- Use this surface as router and launcher only
- Persist suggested action state, lifecycle references, and routing metadata via existing core/lifecycle structures only

## 6. Docs to read first
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/MOBILE_STACK_ALIGNMENT.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md`
- Nexus bundle: `DOC18_View_Surface_Map.md`
- Nexus bundle: `README.md`

## 7. Drift reconciliation note
Older docs may refer to mode switching or Comms within Nexus. Treat Omni as the comms destination. Treat Nexus Home as a chat-first launcher with four operational hubs (Work / Money / Office / Grow) plus Omni. Do not reproduce any five-mode language that includes Comms as a Nexus mode.

## 8. Scope included
- Chat surface
- Signal strip
- Suggested actions rail
- 5 hub cards
- Empty-state and loading states
- Launch contracts for other mini-apps

## 9. Scope excluded
- Full Omni inbox implementation
- Full Work/Money/Office/Grow dashboards
- Backend intent-classifier implementation beyond launch contract stubs

## 10. Acceptance criteria
- [ ] Mobile-first React mini-app runs standalone
- [ ] Has 5 launch cards: Work, Money, Office, Grow, Omni
- [ ] Chat can launch at least stub actions for each target mini-app
- [ ] Suggested actions are componentized and data-driven
- [ ] Surface is embeddable inside future Nexus shell

## 11. Suggested component breakdown
- `SignalStrip`
- `CommandChatBox`
- `SuggestedActionList`
- `HubCardGrid`
- `HubCard`
- `LaunchResolver`

## 12. Suggested API/data dependencies
- suggestion feed endpoint
- signal summary endpoint
- hub launch map
- chat intent routing endpoint or mock contract

## 13. Output expectation for Spark
Produce a standalone React mini-app with mobile-first layout, PWA-friendly assumptions, composable cards, and mocked or contract-based launch wiring.

---
**Labels:** `spark`, `nexus`, `mobile`, `PWA`
