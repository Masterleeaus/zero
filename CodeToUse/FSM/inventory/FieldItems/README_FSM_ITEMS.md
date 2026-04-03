# Items — FSM Enhancements (v1.3)

Adds procurement-ready fields and UI:
- Cost, Markup %, Unit, Default Supplier
- Price preview endpoint
- Supplier dropdown auto-populated via view composer

## Routes
GET /admin/items/preview-price  → JSON { price }

## Blade
@include('items::items.form_pricing_fields')

## Provider
Modules\Items\Providers\ItemsViewComposerServiceProvider (auto-registered in module.json)
