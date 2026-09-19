---
name: diwanio-mcp
description: "Use Diwanio's tenant-scoped MCP finance tools and connected Diwanio workspace tools for financial reviews, draft preparation, and safe operational follow-up."
---

# Diwanio MCP

Use this skill when the user asks to inspect or prepare Diwanio financial data through MCP, including cash, invoices, expenses, bills, bank transactions, VAT/corporate tax, period close, or Diwanio Webmail.

## Operating rules

- Treat the active workspace/team as the tenant boundary. Never combine records across tenants, infer a tenant from a later user preference, or expose raw identifiers or tax data from another workspace.
- Prefer read-only finance tools for analysis. A draft tool may prepare an unposted record, but it must not be described as posted, issued, sent, paid, reconciled, or filed.
- Draft creation requires the exact explicit confirmation token defined by the tool. Do not invent or silently reuse confirmation. After a draft response, report its `record_id`, `trace_id`, approval requirement, and the required Diwanio review step.
- Keep financial amounts in integer minor units when returned by the tool; preserve currency boundaries and never add AED, USD, or other currencies together without an explicit conversion contract.
- For tax or accounting conclusions, distinguish tool-returned facts, calculated summaries, and professional advice. Flag missing workpapers, exceptions, pending bank rows, and closed-period blockers.
- Use bilingual/RTL-aware wording when presenting user-facing results if the user asks for Arabic or the surrounding UI is Arabic.
- Production and external mutations remain authorization-bound. Reading mail is allowed when connected; sending, moving, or other external changes require the user’s clear request and exact target.

## Tool routing

Read [references/tool-catalog.md](references/tool-catalog.md) when choosing a Diwanio tool, checking its input contract, or explaining what a result proves. The catalog is a snapshot of the indexed Laravel implementation and connected app tools; refresh it from the live tool schema/source when available instead of assuming it is current.

For repository discovery or implementation work, use the codebase-memory MCP tools first: `search_graph`, `trace_path`, `get_code_snippet`, `query_graph`, `search_code`, and `get_architecture`. If the project is not indexed, run `index_repository` before discovery.

For a finance review, a useful sequence is: establish tenant/workspace context, call `GetFinancialOverview`, drill into the relevant read-only listing or status tool, and only then offer a draft action if the user explicitly wants one. State the observation timestamp and scope.

When the MCP host supports MCP Apps, `GetFinancialOverview` also returns the `FinancialOverviewApp` UI resource. Treat the widget as a presentation of the tool result, not a second source of truth; the structured result remains authoritative and must remain usable when the host does not render UI. The app uses the shared `ui/*` bridge and host CSS variables so the same resource can run in ChatGPT, Codex, and other MCP Apps-compatible hosts.

The GitHub-ready package keeps the server-side UI source under `server/`. Read `server/INTEGRATION.md` when installing the UI into a Laravel MCP server. Do not claim the plugin serves the UI by itself unless its MCP server connection is also configured and reachable.

For a draft request, validate required fields before calling the draft tool, show the proposed financial effect in the correct currency, obtain explicit confirmation for that exact action, call the appropriate draft tool, and stop at the returned review/approval boundary.
