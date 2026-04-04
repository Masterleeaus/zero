{{--
    Compatibility wrapper: this path exists for internal routing symmetry.
    The canonical portal agreements list is rendered by PortalController::agreements()
    at default.panel.work.portal.agreements — delegate to avoid duplication.
--}}
@include('default.panel.work.portal.agreements')
