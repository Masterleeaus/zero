# Current Feature Inventory
**Generated:** 2026-04-04 | **Branch:** canonical (main) | **Status:** Forensic audit

---

## Domain 1: Core SaaS Platform

| Feature | Status | Key Files |
|---------|--------|-----------|
| User management | FULLY MERGED | app/Models/User.php, app/Http/Controllers/Auth/ |
| Plans & subscriptions | FULLY MERGED | app/Models/Plan.php, app/Models/Finance/Subscription.php |
| Payment gateways (10+) | FULLY MERGED | app/Services/PaymentGateways/, app/Services/Payment/ |
| OpenAI content generation | FULLY MERGED | app/Models/OpenAIGenerator.php, app/Services/Ai/ |
| AI chat (OpenAI) | FULLY MERGED | app/Models/UserOpenaiChat.php, app/TitanCore/Chat/ |
| ElevenLabs voice | FULLY MERGED | app/Services/Ai/ElevenLabsService.php, app/Models/Voice/ |
| Chatbot (embedded) | FULLY MERGED | app/Services/Chatbot/, app/Models/Chatbot/, ChatbotServiceProvider |
| Credit system | FULLY MERGED | app/Models/Concerns/User/HasCredit.php, app/Services/Credits/ |
| Affiliate program | FULLY MERGED | app/Models/UserAffiliate.php, migrations |
| Frontend landing page | FULLY MERGED | app/Models/Frontend/, app/Models/Section/ |
| User support tickets | FULLY MERGED | app/Models/UserSupport*, migrations |
| Themes system | FULLY MERGED | app/Services/Theme/, ViewServiceProvider |
| Activity/audit log | FULLY MERGED | app/Models/Activity.php, migrations |
| Settings (Setting + SettingTwo) | FULLY MERGED | app/Models/Setting.php, AppServiceProvider observers |
| AI image generation | FULLY MERGED | app/Services/Ai/Images/, FalAI/Creatify/Topview/Vizard packages |
| Telescope | FULLY MERGED | TelescopeServiceProvider, migrations |
| AWS integration | FULLY MERGED | AwsServiceProvider |
| Multi-currency | FULLY MERGED | app/Models/Currency.php |
| Bedrock AI | FULLY MERGED | app/Services/Bedrock/BedrockRuntimeService.php |
| DeFi market data | FULLY MERGED | app/Services/DeFi/, app/Http/Controllers/DeFi/ |

---

## Domain 2: CRM

| Feature | Status | Key Files |
|---------|--------|-----------|
| Customer management | FULLY MERGED | app/Models/Crm/Customer.php, CustomerContact, CustomerDocument, CustomerNote |
| Deal pipeline | FULLY MERGED | app/Models/Crm/Deal.php, DealNote.php |
| Enquiry management | FULLY MERGED | app/Models/Crm/Enquiry.php |
| CRM-Job bridge events | FULLY MERGED | app/Events/Crm/ (7 events), app/Services/Crm/CrmServiceJobService.php |
| CRM warranty events | FULLY MERGED | app/Events/Crm/CrmWarranty* (4 events) |
| CRM routes | FULLY MERGED | routes/core/crm.routes.php |

---

## Domain 3: Work / Field Service Management

| Feature | Status | Key Files |
|---------|--------|-----------|
| Service jobs | FULLY MERGED | app/Models/Work/ServiceJob.php, WorkflowService, JobStageService |
| Service agreements | FULLY MERGED | app/Models/Work/ServiceAgreement.php |
| Service plans & visits | FULLY MERGED | app/Models/Work/ServicePlan.php, ServicePlanVisit.php |
| Job stages & activity | FULLY MERGED | app/Models/Work/JobStage.php, JobActivity.php, JobActivityService |
| FSM blockers & priority | FULLY MERGED | app/Models/FSM/FsmJobBlocker.php, FsmJobPriorityScore.php, FsmJobStatusMeta.php |
| Job templates | FULLY MERGED | app/Models/Work/JobTemplate.php, JobType.php |
| Timesheets (HRM) | FULLY MERGED | app/Models/Work/TimesheetSubmission.php, HRM/TimesheetService |
| Leave management | FULLY MERGED | app/Models/Work/Leave.php, LeaveHistory.php, LeaveQuota.php |
| Staff profiles | FULLY MERGED | app/Models/Work/StaffProfile.php |
| Attendance | FULLY MERGED | app/Models/Work/Attendance.php |
| Shift management | FULLY MERGED | app/Models/Work/Shift.php |
| Territory/Region/District | FULLY MERGED | app/Models/Work/Territory.php, Region.php, District.php |
| Service area + branches | FULLY MERGED | app/Models/Work/ServiceArea.php, ServiceAreaBranch, ServiceAreaDistrict, ServiceAreaRegion |
| Checklist framework | FULLY MERGED | app/Models/Work/Checklist.php, ChecklistItem, ChecklistRun, ChecklistResponse, ChecklistTemplate |
| FSM Sale pipeline | FULLY MERGED | app/Services/Work/FieldServiceSaleService.php, FieldServiceSaleCreated/Approved/Converted events |
| Sale recurring agreements | FULLY MERGED | app/Services/Work/SaleRecurringAgreementService.php, migration 500410 |
| Equipment coverage service | FULLY MERGED | app/Services/Work/EquipmentCoverageService.php |
| Recurring sale service | FULLY MERGED | app/Services/Work/RecurringSaleService.php |
| Field service project | FULLY MERGED | app/Models/Work/FieldServiceProject.php, FieldServiceProjectService.php |
| Portal booking/feedback | FULLY MERGED | app/Events/Work/Portal* (6 events) |
| Agreement scheduler | FULLY MERGED | app/Services/Work/AgreementSchedulerService.php |
| Job billing | FULLY MERGED | app/Services/Work/JobBillingService.php |
| Work routes | FULLY MERGED | routes/core/work.routes.php |

---

## Domain 4: Dispatch

| Feature | Status | Key Files |
|---------|--------|-----------|
| Dispatch service (core) | FULLY MERGED | app/Services/Work/DispatchService.php |
| Dispatch constraints | FULLY MERGED | app/Services/Work/DispatchConstraintService.php |
| Dispatch readiness | FULLY MERGED | app/Services/Work/DispatchReadinessService.php |
| Vehicle dispatch | FULLY MERGED | app/Services/Work/VehicleDispatchService.php |
| Stock dispatch | FULLY MERGED | app/Services/Work/StockDispatchService.php |
| Agreement dispatch | FULLY MERGED | app/Services/Work/AgreementDispatchService.php |
| Dispatch queue/assignment | FULLY MERGED | app/Models/Work/DispatchQueue.php, DispatchAssignment.php, DispatchConstraint.php |
| Dispatch board adapters | FULLY MERGED | app/Services/Dispatch/DispatchBoardCardDTO.php, DispatchBoardEventAdapter.php |

---

## Domain 5: Route Planning

| Feature | Status | Key Files |
|---------|--------|-----------|
| Dispatch routes | FULLY MERGED | app/Models/Route/DispatchRoute.php, DispatchRouteStop.php, DispatchRouteStopItem.php |
| Technician availability | FULLY MERGED | app/Models/Route/TechnicianAvailability.php, AvailabilityWindow.php |
| Blackout days | FULLY MERGED | app/Models/Route/RouteBlackoutDay.php, RouteBlackoutGroup.php |
| Route service | FULLY MERGED | app/Services/Route/RouteService.php |
| Scheduling surface | FULLY MERGED | app/Services/Scheduling/ (3 services) |
| Route events (11) | FULLY MERGED | app/Events/Route/ |
| Route routes | FULLY MERGED | routes/core/route.routes.php |

---

## Domain 6: Premises & Facilities

| Feature | Status | Key Files |
|---------|--------|-----------|
| Premises model hierarchy | FULLY MERGED | app/Models/Premises/ (Building, Floor, Room, Unit, Occupancy, SiteAccessProfile) |
| Hazard detection | FULLY MERGED | app/Models/Premises/Hazard.php, 2 events + 2 listeners |
| Facility documents | FULLY MERGED | app/Models/Premises/FacilityDocument.php (MODULE 08 extended) |
| Site assets | FULLY MERGED | app/Models/Facility/SiteAsset.php, AssetServiceEvent.php |
| Inspection framework | FULLY MERGED | app/Models/Inspection/ (7 models), 6 events, InspectionSchedule |

---

## Domain 7: Equipment

| Feature | Status | Key Files |
|---------|--------|-----------|
| Equipment tracking | FULLY MERGED | app/Models/Equipment/Equipment.php, InstalledEquipment.php, EquipmentMovement.php |
| Warranty management | FULLY MERGED | app/Models/Equipment/EquipmentWarranty.php, WarrantyClaim.php |
| Equipment events (10) | FULLY MERGED | app/Events/Equipment/ |
| Equipment listeners | FULLY MERGED | app/Listeners/Equipment/ |

---

## Domain 8: Vehicles

| Feature | Status | Key Files |
|---------|--------|-----------|
| Vehicle management | FULLY MERGED | app/Models/Vehicle/ (5 models: Vehicle, VehicleAssignment, VehicleEquipment, VehicleStock, VehicleLocationSnapshot) |
| FSM Vehicle service | FULLY MERGED | app/Services/FSM/VehicleService.php |
| Vehicle events | FULLY MERGED | app/Events/Work/Vehicle* (6 events) |

---

## Domain 9: Meters

| Feature | Status | Key Files |
|---------|--------|-----------|
| Meter readings | FULLY MERGED | app/Models/Meter/Meter.php, MeterReading.php |
| Migration | FULLY MERGED | migration 000700 |

---

## Domain 10: Inventory

| Feature | Status | Key Files |
|---------|--------|-----------|
| Inventory items | FULLY MERGED | app/Models/Inventory/InventoryItem.php, Warehouse.php |
| Stock movements | FULLY MERGED | app/Models/Inventory/StockMovement.php, Stocktake.php, StocktakeLine.php |
| Purchase orders | FULLY MERGED | app/Models/Inventory/PurchaseOrder.php, PurchaseOrderItem.php |
| Supplier management | FULLY MERGED | app/Models/Inventory/Supplier.php |
| Inventory audit | FULLY MERGED | app/Models/Inventory/InventoryAudit.php |
| Inventory services | FULLY MERGED | app/Services/Inventory/ (3 services) |
| Inventory routes | FULLY MERGED | routes/core/inventory.routes.php |

---

## Domain 11: Finance / Money (TitanMoney)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Invoice & payments | FULLY MERGED | app/Models/Money/Invoice.php, Payment.php, InvoiceItem.php |
| Quotes & templates | FULLY MERGED | app/Models/Money/Quote.php, QuoteItem.php, QuoteTemplate.php, QuoteTemplateItem.php |
| Credit notes | FULLY MERGED | app/Models/Money/CreditNote.php, CreditNoteItem.php |
| Expenses | FULLY MERGED | app/Models/Money/Expense.php, ExpenseCategory.php |
| Chart of accounts | FULLY MERGED | app/Models/Money/Account.php, JournalEntry.php, JournalLine.php, LedgerTransaction.php |
| Bank accounts | FULLY MERGED | app/Models/Money/BankAccount.php |
| Tax management | FULLY MERGED | app/Models/Money/Tax.php |
| AP: Supplier bills | FULLY MERGED | app/Models/Money/SupplierBill.php, SupplierBillItem.php, SupplierBillLine.php, SupplierPayment.php |
| Payroll | FULLY MERGED | app/Models/Money/Payroll.php, PayrollLine.php, PayrollService |
| Financial assets | FULLY MERGED | app/Models/Money/FinancialAsset.php |
| Job cost entries | FULLY MERGED | app/Models/Money/JobCostEntry.php |
| Accounting service | FULLY MERGED | app/Services/TitanMoney/AccountingService.php |
| Finance report service | FULLY MERGED | app/Services/TitanMoney/FinanceReportService.php |
| Quote service | FULLY MERGED | app/Services/Money/QuoteService.php |
| Finance routes | FULLY MERGED | routes/core/money.routes.php, routes/core/finance.routes.php |
| Finance observers | FULLY MERGED | app/Observers/Money/ |

---

## Domain 12: Finance Executive Layer (MODULE 09)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Job cost records | FULLY MERGED | app/Models/Finance/JobCostRecord.php |
| Job revenue records | FULLY MERGED | app/Models/Finance/JobRevenueRecord.php |
| Job financial summary | FULLY MERGED | app/Models/Finance/JobFinancialSummary.php |
| Financial rollup | FULLY MERGED | app/Models/Finance/FinancialRollup.php |
| Costing services | FULLY MERGED | app/Services/Finance/ (4 services) |
| Finance events (4) | FULLY MERGED | app/Events/Finance/ |
| Finance listeners | FULLY MERGED | app/Listeners/Finance/ |

---

## Domain 13: Repair

| Feature | Status | Key Files |
|---------|--------|-----------|
| Repair orders | FULLY MERGED | app/Models/Repair/RepairOrder.php |
| Repair templates | FULLY MERGED | app/Models/Repair/RepairTemplate.php + 3 sub-models |
| Repair workflow | FULLY MERGED | app/Models/Repair/RepairDiagnosis.php, RepairTask.php, RepairAction.php |
| Repair parts | FULLY MERGED | app/Models/Repair/RepairPartUsage.php |
| Repair checklists | FULLY MERGED | app/Models/Repair/RepairChecklist.php, RepairResolution.php |
| Repair events (25+) | FULLY MERGED | app/Events/Repair/ |
| Repair template service | FULLY MERGED | app/Services/Repair/RepairTemplateService.php |
| Repair listeners | FULLY MERGED | app/Listeners/Repair/ |
| Repair routes | FULLY MERGED | routes/core/repair.routes.php |

---

## Domain 14: Security

| Feature | Status | Key Files |
|---------|--------|-----------|
| Blacklist (email/IP) | FULLY MERGED | app/Models/Security/ |
| Cyber security config | FULLY MERGED | app/Models/Security/CyberSecurityConfig.php |
| Login expiry | FULLY MERGED | app/Models/Security/LoginExpiry.php |
| Security audit events | FULLY MERGED | app/Models/Security/SecurityAuditEvent.php |
| Security services | FULLY MERGED | app/Services/Security/ (2 services) |
| Security middleware | FULLY MERGED | app/Http/Middleware/Security/ |
| Security routes | FULLY MERGED | routes/core/security.routes.php |

---

## Domain 15: Admin

| Feature | Status | Key Files |
|---------|--------|-----------|
| Admin user management | FULLY MERGED | app/Services/Admin/AdminUserService.php |
| Admin role management | FULLY MERGED | app/Services/Admin/AdminRoleService.php |
| Admin settings | FULLY MERGED | app/Services/Admin/AdminSettingsService.php |
| Admin audit log | FULLY MERGED | app/Models/Admin/AdminAuditLog.php, AdminAuditService |
| Admin provider | FULLY MERGED | app/Providers/AdminServiceProvider.php |
| Admin routes | FULLY MERGED | routes/core/admin.routes.php, routes/core/titan_admin.routes.php |

---

## Domain 16: Team / Capabilities (MODULE 02)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Team & members | FULLY MERGED | app/Models/Team/Team.php, TeamMember.php |
| Skill definitions | FULLY MERGED | app/Models/Team/SkillDefinition.php, TechnicianSkill.php |
| Certifications | FULLY MERGED | app/Models/Team/Certification.php |
| Skill requirements | FULLY MERGED | app/Models/Team/SkillRequirement.php |
| Availability windows | FULLY MERGED | app/Models/Team/AvailabilityWindow.php, AvailabilityOverride.php |
| Capability services | FULLY MERGED | app/Services/Team/CapabilityRegistryService.php, SkillComplianceService.php |
| Capability events (4) | FULLY MERGED | app/Events/Team/ |
| Team routes | FULLY MERGED | routes/core/team.routes.php |

---

## Domain 17: Trust Ledger (MODULE 03)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Trust ledger entries | FULLY MERGED | app/Models/Trust/TrustLedgerEntry.php (immutable) |
| Trust chain seals | FULLY MERGED | app/Models/Trust/TrustChainSeal.php |
| Trust evidence | FULLY MERGED | app/Models/Trust/TrustEvidenceAttachment.php |
| Trust services | FULLY MERGED | app/Services/Trust/ (2 services) |
| Trust events (3) | FULLY MERGED | app/Events/Trust/ |
| Trust listeners | FULLY MERGED | app/Listeners/Trust/ |
| Trust routes | FULLY MERGED | routes/core/trust.routes.php |

---

## Domain 18: Contracts (MODULE 04)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Contract entitlements | FULLY MERGED | app/Models/Work/ContractEntitlement.php |
| Contract SLA breaches | FULLY MERGED | app/Models/Work/ContractSLABreach.php |
| Contract renewals | FULLY MERGED | app/Models/Work/ContractRenewal.php |
| Contract services (4) | FULLY MERGED | app/Services/Work/Contract*Service.php |
| Contract events (5) | FULLY MERGED | app/Events/Work/Contract* |
| Contract routes | FULLY MERGED | dashboard.work.contracts.* in work.routes.php |

---

## Domain 19: Edge Sync (MODULE 05)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Edge device sessions | FULLY MERGED | app/Models/Sync/EdgeDeviceSession.php |
| Edge sync queue | FULLY MERGED | app/Models/Sync/EdgeSyncQueue.php |
| Edge sync log | FULLY MERGED | app/Models/Sync/EdgeSyncLog.php |
| Edge sync conflicts | FULLY MERGED | app/Models/Sync/EdgeSyncConflict.php |
| Edge sync services (3) | FULLY MERGED | app/Services/Sync/ |
| Edge sync events (4) | FULLY MERGED | app/Events/Sync/ |
| API sync routes | FULLY MERGED | titan_core.routes.php (api.v1.sync.*) |

---

## Domain 20: Execution Time Graph (MODULE 06)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Execution graphs | FULLY MERGED | app/Models/TimeGraph/ExecutionGraph.php |
| Execution events | FULLY MERGED | app/Models/TimeGraph/ExecutionEvent.php |
| Graph checkpoints | FULLY MERGED | app/Models/TimeGraph/ExecutionGraphCheckpoint.php |
| TimeGraph services (2) | FULLY MERGED | app/Services/TimeGraph/ |
| TimeGraph events (4) | FULLY MERGED | app/Events/TimeGraph/ |
| TimeGraph job | FULLY MERGED | app/Jobs/TimeGraph/RecordSignalToTimeGraph.php |
| TimeGraph routes | FULLY MERGED | routes/core/timegraph.routes.php |

---

## Domain 21: TitanPredict (MODULE 07)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Predictions | FULLY MERGED | app/Models/Predict/Prediction.php |
| Prediction signals | FULLY MERGED | app/Models/Predict/PredictionSignal.php |
| Prediction outcomes | FULLY MERGED | app/Models/Predict/PredictionOutcome.php |
| Prediction schedules | FULLY MERGED | app/Models/Predict/PredictionSchedule.php |
| Predict services (3) | FULLY MERGED | app/Services/Predict/ |
| Predict events (4) | FULLY MERGED | app/Events/Predict/ |
| Predict listeners | FULLY MERGED | app/Listeners/Predict/ |
| Predict routes | FULLY MERGED | routes/core/predict.routes.php |

---

## Domain 22: DocsExecution Bridge (MODULE 08)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Facility documents | FULLY MERGED | app/Models/Premises/FacilityDocument.php |
| Job injected docs | FULLY MERGED | app/Models/Premises/JobInjectedDocument.php |
| Inspection injected docs | FULLY MERGED | app/Models/Premises/InspectionInjectedDocument.php |
| Document injection rules | FULLY MERGED | app/Models/Premises/DocumentInjectionRule.php |
| Docs services (3) | FULLY MERGED | app/Services/Docs/ |
| Docs events (4) | FULLY MERGED | app/Events/Docs/ |
| Docs listeners | FULLY MERGED | app/Listeners/Docs/ |
| Docs routes | FULLY MERGED | routes/core/docs.routes.php |

---

## Domain 23: TitanPWA

| Feature | Status | Key Files |
|---------|--------|-----------|
| PWA devices | FULLY MERGED | app/Models/TzPwaDevice.php |
| PWA signal ingress | FULLY MERGED | app/Models/TzPwaSignalIngress.php |
| PWA staged artifacts | FULLY MERGED | app/Models/TzPwaStagedArtifact.php |
| PWA services (10) | FULLY MERGED | app/Services/TitanZeroPwaSystem/ |
| PWA provider | FULLY MERGED | app/Providers/TitanPwaServiceProvider.php |
| PWA routes | FULLY MERGED | routes/core/pwa.routes.php |
| PWA migrations (7) | FULLY MERGED | migrations 000100-000700 |

---

## Domain 24: TitanCore AI Layer

| Feature | Status | Key Files |
|---------|--------|-----------|
| TitanCore provider | FULLY MERGED | app/Providers/TitanCoreServiceProvider.php |
| AI memory tables | FULLY MERGED | migrations 200001-200004 (tz_ai_memories, embeddings, snapshots, handoffs) |
| Core AI structure | MERGED BUT PARTIAL | app/TitanCore/ (structural skeleton exists, full wiring unclear) |
| Titan/Core duplication | DUPLICATED | app/Titan/Core/ vs app/TitanCore/ — parallel structures |
| TitanCoreConsensus | FULLY MERGED | app/Services/TitanCoreConsensus/ (2 services) |
| TitanChat bridge | FULLY MERGED | app/Services/TitanChat/TitanChatBridge.php, routes/core/chat.routes.php |
| Zylos agent layer | PARTIALLY PRESENT | app/TitanCore/Zylos/ exists, unclear wiring |
| MCP handlers | PARTIALLY PRESENT | app/TitanCore/MCP/Handlers/, routes/core/mcp.routes.php |
| TitanSignals | FULLY MERGED | app/Providers/TitanSignalsServiceProvider.php, routes/core/signals.routes.php |

---

## Domain 25: Support & Knowledge Base

| Feature | Status | Key Files |
|---------|--------|-----------|
| Knowledge base | FULLY MERGED | app/Models/Support/KnowledgeBase* (3 models) |
| Notices | FULLY MERGED | app/Models/Support/Notice.php, NoticeView.php |
| Service issues | FULLY MERGED | app/Models/Support/ServiceIssue.php, ServiceIssueMessage.php |
| Support lifecycle | FULLY MERGED | app/Services/Support/SupportLifecycleService.php |
| Support routes | FULLY MERGED | routes/core/support.routes.php |

---

## Domain 26: Portal (FSM Module 21)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Portal routes | FULLY MERGED | routes/core/portal.routes.php |
| Portal events (6) | FULLY MERGED | app/Events/Work/Portal* |
| Portal migrations | FULLY MERGED | migration 500200 |

---

## Domain 27: Project Management (FSM Module 22)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Field service project | FULLY MERGED | app/Models/Work/FieldServiceProject.php |
| Project service | FULLY MERGED | app/Services/Work/FieldServiceProjectService.php |
| Project events (5) | FULLY MERGED | app/Events/Work/FieldServiceProject* |
| Project routes | FULLY MERGED | routes/core/project.routes.php |

---

## Domain 28: TitanRewind Extension

| Feature | Status | Key Files |
|---------|--------|-----------|
| Rewind extension | MERGED BUT PARTIAL | app/Extensions/TitanRewind/ |
| Rewind routes | FULLY MERGED | routes/core/rewind.routes.php |

---

## Domain 29: Nexus / Multi-Mode Architecture

| Feature | Status | Key Files |
|---------|--------|-----------|
| 5-mode architecture design | DOCS ONLY | docs/nexuscore/ (145+ docs) |
| Mode classification | DOCS ONLY | docs/nexuscore/DOC01-DOC04 |
| Route/controller realignment | DOCS ONLY | docs/nexuscore/DOC31-DOC55 |
| Social mode | DOCS ONLY | docs/nexuscore/DOC84 |
| No code implementation | NOT FOUND | — |

---

## Domain 30: Omni / Comms

| Feature | Status | Key Files |
|---------|--------|-----------|
| Omni blueprint | DOCS ONLY | docs/omni/Titan_Omni_Master_Docs_Pass05_Wiring_Blueprint.zip |
| Comms triage plan | DOCS ONLY | docs/COMMS_TRIAGE_PLAN.md |
| TitanChat canvas | PARTIALLY PRESENT | docs/TITAN_CHATBOT_AICHATPRO_CANVAS_INTEGRATION.md |
| Social routes | FULLY MERGED | routes/core/social.routes.php |

---

## Domain 31: TitanMesh (MODULE 10)

| Feature | Status | Key Files |
|---------|--------|-----------|
| Mesh routes | NOT FOUND | No routes/core/mesh.routes.php |
| Mesh models | NOT FOUND | No app/Models/Mesh/ directory |
| Mesh migrations | NOT FOUND | No mesh migration files |
| Mesh services | NOT FOUND | No app/Services/Mesh/ |
| FSM status entry | EXISTS | fsm_module_status.json entry marked installed |

> **CRITICAL GAP:** TitanMesh is marked as installed in fsm_module_status.json and referenced in agent memory, but NO code artifacts exist in the current canonical branch. This is either a false positive in the status file or work that exists only in another branch.
