# Modular Route Loader Template

Create:

routes/titan/core.routes.php
routes/titan/mcp.routes.php
routes/titan/agents.routes.php

Register in RouteServiceProvider:

Route::middleware('web')
    ->group(base_path('routes/titan/core.routes.php'));
