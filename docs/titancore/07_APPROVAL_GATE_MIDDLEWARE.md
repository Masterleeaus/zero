# Approval Gate Middleware

Ensure actions pass approval chain:

if (!Approval::allowed($action)) {
    abort(403);
}
