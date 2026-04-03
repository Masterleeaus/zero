# zero-main Scan Summary

## Existing backend anchors
### Models
#### Work
- `app/Models/Work/Attendance.php`
- `app/Models/Work/Checklist.php`
- `app/Models/Work/Leave.php`
- `app/Models/Work/LeaveHistory.php`
- `app/Models/Work/LeaveQuota.php`
- `app/Models/Work/ServiceAgreement.php`
- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/Shift.php`
- `app/Models/Work/Site.php`
- `app/Models/Work/Timelog.php`
#### CRM
- `app/Models/Crm/Customer.php`
- `app/Models/Crm/Enquiry.php`
#### Team
- `app/Models/Team/Team.php`
- `app/Models/Team/TeamMember.php`

### Controllers
#### Work
- `app/Http/Controllers/Core/Work/AttendanceController.php`
- `app/Http/Controllers/Core/Work/ChecklistController.php`
- `app/Http/Controllers/Core/Work/LeaveController.php`
- `app/Http/Controllers/Core/Work/ServiceAgreementController.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `app/Http/Controllers/Core/Work/ShiftController.php`
- `app/Http/Controllers/Core/Work/SiteController.php`
- `app/Http/Controllers/Core/Work/TimelogController.php`
#### CRM
- `app/Http/Controllers/Core/Crm/CustomerContactController.php`
- `app/Http/Controllers/Core/Crm/CustomerController.php`
- `app/Http/Controllers/Core/Crm/CustomerDocumentController.php`
- `app/Http/Controllers/Core/Crm/CustomerNoteController.php`
- `app/Http/Controllers/Core/Crm/DealController.php`
- `app/Http/Controllers/Core/Crm/DealNoteController.php`
- `app/Http/Controllers/Core/Crm/EnquiryController.php`
#### Team
- `app/Http/Controllers/Core/Team/CleanerProfileController.php`
- `app/Http/Controllers/Core/Team/WeeklyTimesheetController.php`
- `app/Http/Controllers/Core/Team/ZoneController.php`
#### Money
- `app/Http/Controllers/Core/Money/BankAccountController.php`
- `app/Http/Controllers/Core/Money/CreditNoteController.php`
- `app/Http/Controllers/Core/Money/ExpenseCategoryController.php`
- `app/Http/Controllers/Core/Money/ExpenseController.php`
- `app/Http/Controllers/Core/Money/InvoiceController.php`
- `app/Http/Controllers/Core/Money/PaymentController.php`
- `app/Http/Controllers/Core/Money/QuoteController.php`
- `app/Http/Controllers/Core/Money/QuoteTemplateController.php`
- `app/Http/Controllers/Core/Money/TaxController.php`

### Services
#### Work
- `app/Services/Work/AgreementSchedulerService.php`
#### Money
- `app/Services/Money/QuoteService.php`

### Core route files
- `routes/core/crm.routes.php`
- `routes/core/insights.routes.php`
- `routes/core/money.routes.php`
- `routes/core/signals.routes.php`
- `routes/core/social.routes.php`
- `routes/core/support.routes.php`
- `routes/core/team.routes.php`
- `routes/core/work.routes.php`