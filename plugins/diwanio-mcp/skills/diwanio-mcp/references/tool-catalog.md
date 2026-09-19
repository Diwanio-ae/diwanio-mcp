# Diwanio MCP tool catalog

This catalog describes the finance MCP tools indexed in `diwanio-laravel/app/Mcp/Tools`. Exact live MCP names and schemas take precedence if they differ.

## Tenant-scoped finance tools

| Tool | Mode | Use | Important inputs/outputs |
|---|---|---|---|
| `GetFinancialOverview` | Read-only + optional MCP App UI | One-pass workspace financial health review | Returns open invoices by currency, expenses by currency, cash by account, count of unreviewed bank transactions, active accounting period, and readiness data. No inputs. When supported, renders `ui://resources/financial-overview-app`; structured content remains authoritative. |
| `GetCashPosition` | Read-only | Current cash by bank account | Returns account name/status/currency, opening balance, inflow, outflow, and `balance_minor`. No inputs. |
| `GetTaxStatus` | Read-only | VAT and corporate tax registration/workpaper status | Returns registration flags/TRNs, VAT periods, qualifying-free-zone flag, and latest workpaper per tax type including exceptions. No inputs. |
| `GetPeriodCloseStatus` | Read-only | Active or requested accounting-period close readiness | Optional `period` string, max 20 characters. Returns selected period, status, and readiness blockers. |
| `ListExpenses` | Read-only | Inspect tenant expenses | Supports the shared bounded `limit` and date-range parameters. Returns tenant-owned expense rows with currency and minor-unit amounts. |
| `ListOverdueInvoices` | Read-only | Review receivables needing attention | Supports bounded `limit`; returns overdue invoice details, currency, minor-unit total, and days overdue. |
| `ListPendingTransactions` | Read-only | Review unreconciled bank rows | Supports bounded `limit`; returns bank account, currency, posted date, description/reference, minor-unit amount, type, and match state. |
| `CreateInvoiceDraft` | Draft, confirmation required | Prepare an unposted customer invoice | Requires tenant `contact_id`, issue/due dates, at least one line, and exact confirmation `CREATE_INVOICE_DRAFT`. Never issues or sends the invoice. |
| `CreateBillDraft` | Draft, confirmation required | Prepare an unposted supplier bill | Requires supplier `contact_id`, issue/due dates, currency, `subtotal_minor`, `tax_minor`, `total_minor`, and exact confirmation `CREATE_BILL_DRAFT`. Never posts the bill. |
| `CreateExpenseDraft` | Draft, confirmation required | Prepare an unposted expense | Requires description, spent date, amount, optional currency/tax/evidence fields, and exact confirmation `CREATE_EXPENSE_DRAFT`. Never posts the expense. |

All finance tools resolve the tenant from the authenticated MCP request. Do not pass a guessed `tenant_id` or use a record ID as proof of ownership.

## Draft response contract

The shared draft flow returns a structured result containing:

- `status: draft`
- `record_type` and `record_id`
- `approval_required: true`
- `trace_id`
- a next-step message instructing review in Diwanio before posting or sending

The draft response proves preparation only. A separate authenticated Diwanio workflow is required for review, posting, issuing, sending, payment, reconciliation, or filing.

## Connected Diwanio Webmail tools

When the Diwanio Webmail connector is available, the callable tools are:

- `mcp__codex_apps__diwanio_webmail_list_folders`
- `mcp__codex_apps__diwanio_webmail_mailbox_summary`
- `mcp__codex_apps__diwanio_webmail_list_emails`
- `mcp__codex_apps__diwanio_webmail_read_email`
- `mcp__codex_apps__diwanio_webmail_move_email`
- `mcp__codex_apps__diwanio_webmail_send_email`

List/search/read for evidence and context. Before moving or sending mail, confirm the exact folder/message/recipient and the requested action. Never include secrets, full email bodies, or attachments in a finance summary unless needed.

## MCP App UI contract

The finance overview widget follows the portable MCP Apps pattern: the tool advertises a UI resource through `_meta.ui.resourceUri`, and the host reads the `text/html;profile=mcp-app` resource. The iframe receives the authoritative result through `ui/notifications/tool-result` and may use `tools/call` for future UI-local refresh actions. Do not make UI-only state or client calculations authoritative.

The packaged server-side source is under `server/`; its `INTEGRATION.md` lists the Laravel file mappings and wiring points.

## Repository and implementation discovery

The indexed project is `diwanio-laravel`. Relevant source locations include:

- `app/Mcp/Tools/*.php` for finance tool contracts
- `app/Mcp/Tools/McpTool.php` for tenant, date, limit, and shared validation helpers
- `app/Mcp/Tools/McpDraftTool.php` for confirmation and draft response behavior
- `app/Mcp/Servers/DiwanioServer.php` and `app/Mcp/Server/Methods/CallTool.php` for server dispatch
- `tests/Feature/Mcp/DiwanioMcpAccessTest.php` for access/subscription behavior

Use graph search to verify current names and callers before changing or relying on a contract.
