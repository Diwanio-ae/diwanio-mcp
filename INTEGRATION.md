# Diwanio MCP integration

This repository packages the plugin metadata, finance skill, tool catalog, branding, portable MCP App UI source, and the connection definition for the separately hosted Diwanio MCP server. It does not contain Laravel or Blade runtime code.

## Connect the existing MCP server

The portable `mcp.json` configures the external Streamable HTTP endpoint:

```text
https://diwanio.ae/mcp/diwanio
```

The package does not contain credentials. Authentication and tenant access remain enforced by the Diwanio server and the host's MCP connection flow.

The server remains responsible for authentication, tenant resolution, billing gates, tool execution, and authoritative `structuredContent` responses.

## Add the authentication phase

The server must complete OAuth before exposing customer-specific finance data:

1. Return 401 Unauthorized from the MCP transport with a WWW-Authenticate
   challenge pointing to the endpoint-specific protected resource metadata.
2. Publish protected-resource metadata and authorization-server metadata under
   the nested .well-known URLs for /mcp/diwanio.
3. Advertise OAuth 2.1 authorization-code + PKCE (S256) and the mcp:use scope.
4. Add securitySchemes with the oauth2 type and mcp:use scope to every tool,
   and enforce that scope at the transport boundary.
5. Expose an authenticated profile tool with _meta["openai/profile"] = true
   when the host supports account linking.

The Laravel implementation uses Passport as the OAuth resource server and
keeps Passport keys in the deployment secret store. Do not add credentials to
mcp.json; the package only declares the remote URL. See
[AUTHENTICATION.md](AUTHENTICATION.md) for the complete contract and production
verification checklist.

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
