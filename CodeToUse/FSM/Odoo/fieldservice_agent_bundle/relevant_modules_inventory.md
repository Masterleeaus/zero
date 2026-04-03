# Relevant Modules Inventory

This inventory was generated from `field-service-18.0.zip` by scanning for real Odoo addon manifests (`__manifest__.py`).

**Relevant module count:** 30

## Prompt → Module map

- `01_base_territory.md` → `base_territory`
- `02_fieldservice_core.md` → `fieldservice`
- `03_fieldservice_calendar.md` → `fieldservice_calendar`
- `04_fieldservice_activity.md` → `fieldservice_activity`
- `05_fieldservice_kanban_info.md` → `fieldservice_kanban_info`
- `06_fieldservice_crm.md` → `fieldservice_crm`
- `07_fieldservice_project.md` → `fieldservice_project`
- `08_fieldservice_recurring.md` → `fieldservice_recurring`
- `09_fieldservice_route.md` → `fieldservice_route`
- `10_fieldservice_availability.md` → `fieldservice_availability`
- `11_fieldservice_route_availability.md` → `fieldservice_route_availability`
- `12_fieldservice_skill.md` → `fieldservice_skill`
- `13_fieldservice_vehicle.md` → `fieldservice_vehicle`
- `14_fieldservice_timesheet.md` → `fieldservice_timesheet`
- `15_fieldservice_portal.md` → `fieldservice_portal`
- `16_fieldservice_account.md` → `fieldservice_account`
- `17_fieldservice_sale.md` → `fieldservice_sale`
- `18_fieldservice_sale_stock.md` → `fieldservice_sale_stock`
- `19_fieldservice_stock.md` → `fieldservice_stock`
- `20_fieldservice_equipment_stock.md` → `fieldservice_equipment_stock`
- `21_fieldservice_equipment_warranty.md` → `fieldservice_equipment_warranty`
- `22_fieldservice_repair.md` → `fieldservice_repair`
- `23_fieldservice_repair_templates.md` → `fieldservice_repair_order_template`
- `24_fieldservice_agreement.md` → `fieldservice_agreement`
- `25_fieldservice_sale_agreement.md` → `fieldservice_sale_agreement`
- `26_fieldservice_sale_equipment_stock.md` → `fieldservice_sale_agreement_equipment_stock`
- `27_fieldservice_sale_recurring.md` → `fieldservice_sale_recurring`
- `28_fieldservice_sale_recurring_agreement.md` → `fieldservice_sale_recurring_agreement`
- `29_fieldservice_size.md` → `fieldservice_size`
- `30_fieldservice_stage_actions.md` → `fieldservice_stage_server_action`

## Module manifest summary

### base_territory
- **Name:** Base Territory
- **Version:** 18.0.1.0.0
- **Depends:** base
- **Data files:** 7
- **Demo files:** 1
- **Summary:** This module allows you to define territories, branches, districts and regions to be used for Field Service operations or Sales.

### fieldservice
- **Name:** Field Service
- **Version:** 18.0.5.6.0
- **Depends:** base_territory, base_geolocalize, resource, contacts
- **Data files:** 26
- **Demo files:** 4
- **Summary:** Manage Field Service Locations, Workers and Orders

### fieldservice_account
- **Name:** Field Service - Accounting
- **Version:** 18.0.1.1.0
- **Depends:** fieldservice, account
- **Data files:** 4
- **Demo files:** 0
- **Summary:** Track invoices linked to Field Service orders

### fieldservice_activity
- **Name:** Field Service Activity
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice
- **Data files:** 3
- **Demo files:** 0
- **Summary:** Field Service Activities are a set of actions      that need to be performed on a service order

### fieldservice_agreement
- **Name:** Field Service - Agreements
- **Version:** 18.0.2.0.1
- **Depends:** fieldservice, agreement
- **Data files:** 4
- **Demo files:** 0
- **Summary:** Manage Field Service agreements and contracts

### fieldservice_availability
- **Name:** Fieldservice Availability
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice_route
- **Data files:** 5
- **Demo files:** 0
- **Summary:** Provides models for defining blackout days, stress days, and delivery time ranges for FSM availability management.

### fieldservice_calendar
- **Name:** Field Service - Calendar
- **Version:** 18.0.1.0.0
- **Depends:** calendar, fieldservice
- **Data files:** 2
- **Demo files:** 0
- **Summary:** Add calendar to FSM Orders

### fieldservice_crm
- **Name:** Field Service - CRM
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice, crm
- **Data files:** 4
- **Demo files:** 0
- **Summary:** Create Field Service orders from the CRM

### fieldservice_equipment_stock
- **Name:** Field Service - Stock Equipment
- **Version:** 18.0.1.0.1
- **Depends:** fieldservice_stock
- **Data files:** 5
- **Demo files:** 0
- **Summary:** Integrate stock operations with your field service equipments

### fieldservice_equipment_warranty
- **Name:** Field Service Equipment Warranty
- **Version:** 18.0.1.0.0
- **Depends:** product_warranty, fieldservice_equipment_stock
- **Data files:** 1
- **Demo files:** 0
- **Summary:** Field Service equipment warranty

### fieldservice_kanban_info
- **Name:** Field Service - Kanban Info
- **Version:** 18.0.1.0.1
- **Depends:** fieldservice
- **Data files:** 2
- **Demo files:** 0
- **Summary:** Display key service information on Field Service Kanban cards.

### fieldservice_portal
- **Name:** Field Service - Portal
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice, portal
- **Data files:** 5
- **Demo files:** 2
- **Summary:** Bridge module between fieldservice and portal.

### fieldservice_project
- **Name:** Field Service - Project
- **Version:** 18.0.1.0.1
- **Depends:** fieldservice, project
- **Data files:** 6
- **Demo files:** 0
- **Summary:** Create field service orders from a project or project task

### fieldservice_recurring
- **Name:** Field Service Recurring Work Orders
- **Version:** 18.0.1.2.0
- **Depends:** fieldservice
- **Data files:** 11
- **Demo files:** 3
- **Summary:** Manage recurring Field Service orders

### fieldservice_repair
- **Name:** Field Service - Repair
- **Version:** 18.0.3.0.1
- **Depends:** repair, fieldservice_equipment_stock
- **Data files:** 2
- **Demo files:** 0
- **Summary:** Integrate Field Service orders with MRP repair orders

### fieldservice_repair_order_template
- **Name:** Field Service - Repair Order Template
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice_repair, repair_order_template
- **Data files:** 1
- **Demo files:** 0
- **Summary:** Use Repair Order Templates when creating a repair orders

### fieldservice_route
- **Name:** Field Service Route
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice
- **Data files:** 10
- **Demo files:** 0
- **Summary:** Organize the routes of each day.

### fieldservice_route_availability
- **Name:** Field Service Route Availability
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice_availability
- **Data files:** 2
- **Demo files:** 0
- **Summary:** Restricts blackout days for Scheduled Start (ETA) orders with the same date.

### fieldservice_sale
- **Name:** Field Service - Sales
- **Version:** 18.0.1.2.1
- **Depends:** fieldservice, sale_management, fieldservice_account
- **Data files:** 6
- **Demo files:** 0
- **Summary:** Sell field services.

### fieldservice_sale_agreement
- **Name:** Field Service - Sale Agreements
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice_agreement, fieldservice_sale, agreement_sale
- **Data files:** 0
- **Demo files:** 0
- **Summary:** Integrate Field Service with Sale Agreements

### fieldservice_sale_agreement_equipment_stock
- **Name:** Field Service - Sale Agreements and Stock Equipment
- **Version:** 18.0.1.0.0
- **Depends:** agreement_sale, fieldservice_agreement, fieldservice_sale, fieldservice_equipment_stock, sale_stock
- **Data files:** 0
- **Demo files:** 0
- **Summary:** Integrate Field Service with Sale Agreements and Stock Equipment

### fieldservice_sale_recurring
- **Name:** Field Service - Sales - Recurring
- **Version:** 18.0.1.1.0
- **Depends:** fieldservice_recurring, fieldservice_sale, fieldservice_account
- **Data files:** 4
- **Demo files:** 0
- **Summary:** Sell recurring field services.

### fieldservice_sale_recurring_agreement
- **Name:** Field Service Recurring Agreement
- **Version:** 18.0.1.0.0
- **Depends:** agreement_sale, fieldservice_agreement, fieldservice_sale_recurring
- **Data files:** 1
- **Demo files:** 0
- **Summary:** Field Service Recurring Agreement

### fieldservice_sale_stock
- **Name:** Field Service - Sale Stock
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice_sale, fieldservice_stock
- **Data files:** 0
- **Demo files:** 0
- **Summary:** Sell stockable items linked to field service orders.

### fieldservice_size
- **Name:** Field Service Sizes
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice, uom
- **Data files:** 5
- **Demo files:** 0
- **Summary:** Manage Sizes for Field Service Locations and Orders

### fieldservice_skill
- **Name:** Field Service - Skills
- **Version:** 18.0.1.0.0
- **Depends:** hr_skills, fieldservice
- **Data files:** 7
- **Demo files:** 0
- **Summary:** Manage your Field Service workers skills

### fieldservice_stage_server_action
- **Name:** Field Service - Stage Server Action
- **Version:** 18.0.1.1.0
- **Depends:** fieldservice, base_automation
- **Data files:** 4
- **Demo files:** 0
- **Summary:** Execute server actions when reaching a Field Service stage

### fieldservice_stock
- **Name:** Field Service - Stock
- **Version:** 18.0.2.0.0
- **Depends:** fieldservice, stock
- **Data files:** 7
- **Demo files:** 0
- **Summary:** Integrate the logistics operations with Field Service

### fieldservice_timesheet
- **Name:** Field Service - Timesheet
- **Version:** 18.0.1.0.0
- **Depends:** hr_timesheet, fieldservice_project
- **Data files:** 3
- **Demo files:** 0
- **Summary:** Timesheet on Field Service Orders

### fieldservice_vehicle
- **Name:** Field Service Vehicles
- **Version:** 18.0.1.0.0
- **Depends:** fieldservice
- **Data files:** 6
- **Demo files:** 0
- **Summary:** Manage Field Service vehicles and assign drivers
