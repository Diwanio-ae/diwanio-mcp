# Diwanio MCP UI integration

This directory contains the server-side MCP App UI source that belongs with the `diwanio-mcp` plugin.

The UI is served by the Laravel MCP server; it is not a static plugin screen. To wire it into a Diwanio Laravel checkout:

1. Copy `Mcp/Resources/FinancialOverviewApp.php` to `app/Mcp/Resources/FinancialOverviewApp.php`.
2. Copy `resources/views/mcp/financial-overview-app.blade.php` to `resources/views/mcp/financial-overview-app.blade.php`.
3. Copy both locale files into `lang/en/mcp.php` and `lang/ar/mcp.php`.
4. Register `FinancialOverviewApp::class` in `DiwanioServer::$resources`.
5. Add `#[RendersApp(resource: FinancialOverviewApp::class)]` to the `GetFinancialOverview` tool.

The existing Diwanio Laravel checkout already performs steps 1–5. The tool's structured result remains authoritative, so clients without MCP App rendering continue to receive the complete financial overview.

The server must still be hosted and connected through the MCP endpoint. This package does not invent an endpoint URL or credentials; configure those for the target environment separately.
