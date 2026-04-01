# MCP Tool Skeleton

Namespace:

App\Titan\Core\Mcp\Tools

Example:

class CreateWorkOrderTool extends TitanMcpTool
{
    public function handle($payload)
    {
        return Signal::dispatch('work.create', $payload);
    }
}
