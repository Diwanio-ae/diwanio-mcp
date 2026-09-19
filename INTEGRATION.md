# Diwanio MCP integration

This repository packages the plugin metadata, finance skill, tool catalog, branding, and portable MCP App UI source. It does not replace the separately hosted Diwanio MCP server and does not contain Laravel or Blade runtime code.

## Connect the existing MCP server

Configure the externally hosted Diwanio MCP endpoint for the target Codex, ChatGPT, or other MCP Apps host. Do not add credentials or invent an endpoint URL in this repository.

The server remains responsible for authentication, tenant resolution, billing gates, tool execution, and authoritative `structuredContent` responses.

## Install the FinancialOverviewApp UI in Laravel

The portable UI source is `plugins/diwanio-mcp/ui/financial-overview-app.html`. Copy it to the Laravel server as `resources/mcp/financial-overview-app.html`, then expose it with a Laravel MCP `AppResource`:

```php
public function handle(Request $request): Response
{
    return Response::html('mcp/financial-overview-app.html');
}
```

The server must also:

1. Register the resource in `DiwanioServer::$resources`.
2. Link the resource from the selected tool with `#[RendersApp(resource: FinancialOverviewApp::class)]`.
3. Return complete financial data in `structuredContent`, because the UI is only a presentation layer.
4. Keep the MCP endpoint protected by the server's existing authentication, tenant, and billing middleware.

The UI is framework-neutral: it is a self-contained HTML document with standard JavaScript, no external dependencies, no Blade directives, and no host-specific requirement. It listens for `ui/notifications/tool-result` and uses `ui/initialize`/`tools/call`-compatible JSON-RPC messaging through `window.parent.postMessage`. ChatGPT-only `window.openai` output is feature-detected only as a fallback.

## Endpoint and package boundary

The GitHub package is the portable plugin distribution. The external MCP server is the runtime integration. Publishing this repository does not publish or proxy the server endpoint, and the UI is not a standalone public web page.
