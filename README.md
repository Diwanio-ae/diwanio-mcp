<p align="center">
  <img src="plugins/diwanio-mcp/assets/logo.png" alt="Diwanio logo" width="128">
</p>

<h1 align="center">Diwanio MCP</h1>

<p align="center">
  Tenant-safe finance tools and a portable MCP Apps financial overview UI for Diwanio workspaces.
</p>

<p align="center">
  <a href="https://diwanio.ae/mcp/diwanio">MCP endpoint</a>
  ·
  <a href="INTEGRATION.md">Integration guide</a>
  ·
  <a href="https://github.com/Diwanio-ae/diwanio-mcp">Repository</a>
</p>

## Overview

`diwanio-mcp` is the portable plugin package for Diwanio's finance MCP integration. It contains the plugin metadata, connection definition, finance skill, tool contract catalog, branding, and framework-neutral MCP App UI source.

The production MCP server remains separately hosted at [`https://diwanio.ae/mcp/diwanio`](https://diwanio.ae/mcp/diwanio). Authentication, tenant resolution, subscription gates, tool execution, and authoritative financial responses remain owned by that server.

## What it provides

- Tenant-scoped financial reviews for cash, invoices, expenses, taxes, period close, and pending bank transactions.
- Guarded invoice, bill, and expense draft preparation with explicit confirmation tokens.
- Accounting-aware handling of currencies, minor-unit amounts, trace IDs, and approval boundaries.
- Connected Diwanio Webmail tools for evidence gathering and explicitly requested mail actions.
- A self-contained financial overview UI using standard HTML, CSS, and JavaScript.
- Portable package structure suitable for Codex and ChatGPT, with a framework-neutral UI that can be adapted to other MCP Apps hosts.

## Connect the plugin

The package connection is declared in [`plugins/diwanio-mcp/mcp.json`](plugins/diwanio-mcp/mcp.json):

```json
{
  "mcpServers": {
    "diwanio": {
      "type": "streamable-http",
      "url": "https://diwanio.ae/mcp/diwanio"
    }
  }
}
```

No credentials are stored in this repository. The host and the Diwanio server handle authentication and access to the connected workspace.

## Finance tool surface

### Read-only tools

| Tool | Purpose |
| --- | --- |
| `GetFinancialOverview` | Review open invoices, expenses, cash, unreviewed bank transactions, the active accounting period, and readiness data. |
| `GetCashPosition` | Inspect current cash by bank account and currency. |
| `GetTaxStatus` | Review VAT and corporate tax registration and workpaper status. |
| `GetPeriodCloseStatus` | Check accounting-period readiness and blockers. |
| `ListExpenses` | Inspect tenant-owned expenses within bounded filters. |
| `ListOverdueInvoices` | Review overdue receivables. |
| `ListPendingTransactions` | Review unreconciled bank transactions. |

### Draft tools

| Tool | Purpose | Boundary |
| --- | --- | --- |
| `CreateInvoiceDraft` | Prepare an unposted customer invoice. | Never issues or sends the invoice. |
| `CreateBillDraft` | Prepare an unposted supplier bill. | Never posts the bill. |
| `CreateExpenseDraft` | Prepare an unposted expense. | Never posts the expense. |

Draft tools require their exact confirmation token and return a structured draft result with an approval requirement and trace ID. Preparing a draft does not prove that a record was posted, issued, sent, paid, reconciled, or filed.

## Financial safety boundaries

- The authenticated MCP request is the tenant boundary. Never guess or inject a `tenant_id`.
- Record IDs are not proof of ownership; the server must resolve and authorize every record.
- Preserve currency boundaries and minor-unit amounts. Do not add or compare monetary values across currencies without an explicit conversion contract.
- Treat `structuredContent` as authoritative. The UI is only a presentation layer.
- Review is required before posting, issuing, sending, payment, reconciliation, or filing.
- Mail can be read for evidence when the connector is available; moving or sending mail requires the exact requested target and action.

## Included package

| Path | Contents |
| --- | --- |
| [`plugins/diwanio-mcp/plugin.json`](plugins/diwanio-mcp/plugin.json) | Portable plugin metadata, finance category, branding, and host-facing description. |
| [`plugins/diwanio-mcp/mcp.json`](plugins/diwanio-mcp/mcp.json) | Streamable HTTP connection to the separately hosted Diwanio MCP server. |
| [`plugins/diwanio-mcp/skills/diwanio-mcp/SKILL.md`](plugins/diwanio-mcp/skills/diwanio-mcp/SKILL.md) | Safe operating guidance for finance and connected workspace workflows. |
| [`plugins/diwanio-mcp/skills/diwanio-mcp/references/tool-catalog.md`](plugins/diwanio-mcp/skills/diwanio-mcp/references/tool-catalog.md) | Finance tool contracts, draft behavior, and MCP App UI notes. |
| [`plugins/diwanio-mcp/ui/financial-overview-app.html`](plugins/diwanio-mcp/ui/financial-overview-app.html) | Framework-neutral MCP Apps UI source. |
| [`plugins/diwanio-mcp/assets/icon.png`](plugins/diwanio-mcp/assets/icon.png) | Compact plugin icon. |
| [`plugins/diwanio-mcp/assets/logo.png`](plugins/diwanio-mcp/assets/logo.png) | Plugin logo used by host surfaces and this README. |
| [`INTEGRATION.md`](INTEGRATION.md) | Server-side wiring instructions and repository/runtime boundaries. |

## MCP App UI

The financial overview UI is a portable, self-contained HTML document. It uses the shared MCP Apps bridge over `window.parent.postMessage`, listens for `ui/notifications/tool-result`, and keeps server-provided structured data authoritative.

There is no Laravel, Blade, React, or build-tool dependency in the packaged UI. The external Laravel MCP server can copy the file into its resource directory and expose it through its MCP resource registration. See [`INTEGRATION.md`](INTEGRATION.md) for the server-side wiring points.

This separation keeps the package reusable across compatible MCP hosts while leaving authentication, tenant isolation, and financial operations on the Diwanio server.

## Development and validation

From the repository root:

```bash
python3 /Users/steevenlacerna/.codex/skills/.system/plugin-creator/scripts/validate_plugin.py plugins/diwanio-mcp
python3 /Users/steevenlacerna/.codex/skills/.system/skill-creator/scripts/quick_validate.py plugins/diwanio-mcp/skills/diwanio-mcp
```

The UI is intentionally dependency-free, so no JavaScript build step is required for the packaged source.

## Repository boundary

This repository distributes the plugin package and UI source. It does not proxy, deploy, or replace the separately hosted MCP server, and it contains no server credentials or Laravel runtime implementation.

