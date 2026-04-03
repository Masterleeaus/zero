# Namespace rewrite map

Goal: move WorkCore app code under MagicAI/Titan-owned namespaces instead of dropping files into host root blindly.

- `app/Http/Controllers` -> `App\Http\Controllers\WorkCore\<Domain>`
- `app/Models` -> `App\Models\WorkCore\<Domain>`
- `app/Services` -> `App\Services\WorkCore\<Domain>`
- `app/Events` -> `App\Events\WorkCore\<Domain>`
- `app/Listeners` -> `App\Listeners\WorkCore\<Domain>`
- `app/Observers` -> `App\Observers\WorkCore\<Domain>`
- `app/Notifications` -> `App\Notifications\WorkCore\<Domain>`
- `app/Console` -> `App\Console\Commands\WorkCore`
- `app/DataTables` -> `App\DataTables\WorkCore\<Domain>`
- `app/View/Components` -> `App\View\Components\WorkCore\<Domain>`

Suggested domain folders:
- CRM -> `WorkCore/Crm`
- Sites -> `WorkCore/Sites`
- Finance -> `WorkCore/Finance`
- HR -> `WorkCore/Hr`
- Support -> `WorkCore/Support`
- Platform/shared -> `WorkCore/Shared`

Rule: keep host controllers/models untouched; import WorkCore code under isolated namespaces first, then adapt route/model bindings incrementally.