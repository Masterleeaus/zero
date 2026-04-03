# Route slice plan

- source route file: `routes/web.php`
- target host route pattern: `routes/core/*.routes.php` or `routes/extensions/*.routes.php`
- suggested middleware translation: `auth, multi-company-select, email_verified` -> host equivalents

## crm_customers_leads
- controllers: 19
  - `CustomerController`
  - `CustomerCategoryController`
  - `CustomerContactController`
  - `CustomerDocController`
  - `CustomerNoteController`
  - `CustomerSubCategoryController`
  - `DealController`
  - `DealNoteController`
  - `GdprController`
  - `GdprSettingsController`
  - `LeadBoardController`
  - `LeadCategoryController`
  - `LeadContactController`
  - `LeadCustomFormController`
  - `LeadFileController`
  - `LeadNoteController`
  - `LeadReportController`
  - `ProposalController`
  - `ProposalTemplateController`

## sites_service jobs_time
- controllers: 36
  - `DiscussionController`
  - `DiscussionCategoryController`
  - `DiscussionFilesController`
  - `DiscussionReplyController`
  - `GanttLinkController`
  - `SiteController`
  - `SiteCalendarController`
  - `SiteCategoryController`
  - `SiteFileController`
  - `SiteLabelController`
  - `SiteMemberController`
  - `SiteMilestoneController`
  - `SiteNoteController`
  - `SiteRatingController`
  - `SiteSubCategoryController`
  - `SiteTemplateController`
  - `SiteTemplateMemberController`
  - `SiteTemplateMilestoneController`
  - `SiteTemplateSubService JobController`
  - `SiteTemplateService JobController`
  - `SiteTimelogBreakController`
  - `SubService JobController`
  - `SubService JobFileController`
  - `Service JobController`
  - `Service JobBoardController`
  - `Service JobCalendarController`
  - `Service JobCategoryController`
  - `Service JobCommentController`
  - `Service JobFileController`
  - `Service JobLabelController`
  - `Service JobNoteController`
  - `Service JobReportController`
  - `TimelogController`
  - `TimelogCalendarController`
  - `TimelogReportController`
  - `TimelogWeeklyApprovalController`

## finance_sales
- controllers: 23
  - `BankAccountController`
  - `CreditNoteController`
  - `QuoteController`
  - `QuoteRequestController`
  - `QuoteTemplateController`
  - `ExpenseController`
  - `ExpenseCategoryController`
  - `ExpenseReportController`
  - `FinanceReportController`
  - `InvoiceController`
  - `InvoiceFilesController`
  - `InvoicePaymentDetailController`
  - `OrderController`
  - `PaymentController`
  - `Service / ExtraController`
  - `Service / ExtraCategoryController`
  - `Service / ExtraFileController`
  - `Service / ExtraSubCategoryController`
  - `QuickbookController`
  - `RecurringEventController`
  - `RecurringExpenseController`
  - `RecurringInvoiceController`
  - `RecurringService JobController`

## hr_people
- controllers: 18
  - `AppreciationController`
  - `AttendanceController`
  - `AttendanceReportController`
  - `ZoneController`
  - `RoleController`
  - `EmergencyContactController`
  - `CleanerController`
  - `CleanerDocController`
  - `CleanerDocumentExpiryController`
  - `CleanerShiftChangeRequestController`
  - `CleanerShiftScheduleController`
  - `CleanerVisaController`
  - `HolidayController`
  - `LeaveController`
  - `LeaveFileController`
  - `LeaveReportController`
  - `LeavesQuotaController`
  - `PromotionController`

## support_knowledge
- controllers: 12
  - `KnowledgeBaseController`
  - `KnowledgeBaseCategoryController`
  - `KnowledgeBaseFileController`
  - `MessageController`
  - `MessageFileController`
  - `NoticeController`
  - `NoticeFileController`
  - `NotificationController`
  - `Issue / SupportController`
  - `Issue / SupportCustomFormController`
  - `Issue / SupportFileController`
  - `Issue / SupportReplyController`

## calendar_contracts
- controllers: 10
  - `ContractController`
  - `ContractDiscussionController`
  - `ContractFileController`
  - `ContractRenewController`
  - `ContractTemplateController`
  - `ContractTypeController`
  - `EventCalendarController`
  - `EventFileController`
  - `MyCalendarController`
  - `WeeklyTimesheetController`

## system_misc
- controllers: 11
  - `AwardController`
  - `DashboardController`
  - `ImageController`
  - `ImportController`
  - `IncomeVsExpenseReportController`
  - `PassportController`
  - `SalesReportController`
  - `SearchController`
  - `SettingsController`
  - `StickyNoteController`
  - `UserPermissionController`
