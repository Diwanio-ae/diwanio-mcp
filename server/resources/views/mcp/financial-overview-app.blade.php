<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            :root {
                color-scheme: light dark;
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                --surface: var(--color-background-primary, #ffffff);
                --surface-muted: var(--color-background-secondary, #f5f5f5);
                --text: var(--color-text-primary, #171717);
                --muted: var(--color-text-secondary, #737373);
                --border: var(--color-border-primary, #e5e5e5);
                --positive: #16803c;
                --warning: #a15c00;
                --danger: #b42318;
            }

            * { box-sizing: border-box; }
            body { margin: 0; background: var(--surface); color: var(--text); }
            main { padding: 18px; max-width: 760px; margin: 0 auto; }
            header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
            h1, h2, p { margin: 0; }
            h1 { font-size: 20px; line-height: 1.25; letter-spacing: -0.02em; }
            h2 { font-size: 13px; line-height: 1.35; }
            .subtitle, .timestamp, .empty, .detail { color: var(--muted); font-size: 12px; }
            .subtitle { margin-top: 5px; }
            .timestamp { text-align: end; white-space: nowrap; }
            .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .card { border: 1px solid var(--border); border-radius: 14px; padding: 14px; background: var(--surface); }
            .metric { font-size: 22px; font-weight: 700; margin-top: 7px; }
            .metric-label { color: var(--muted); font-size: 11px; }
            section { margin-top: 14px; }
            .section-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
            .row { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-top: 1px solid var(--border); }
            .row:first-child { border-top: 0; }
            .amount { font-variant-numeric: tabular-nums; font-weight: 650; white-space: nowrap; }
            .status { border-radius: 999px; padding: 3px 8px; font-size: 11px; background: var(--surface-muted); }
            .status.good { color: var(--positive); }
            .status.warning { color: var(--warning); }
            .status.danger { color: var(--danger); }
            .error { color: var(--danger); font-size: 13px; }
            @media (max-width: 480px) {
                main { padding: 14px; }
                header { display: block; }
                .timestamp { text-align: start; margin-top: 8px; }
            }
        </style>
        <script type="module">
            createMcpApp(async (app) => {
                const root = document.getElementById('financial-overview');

                const escapeHtml = (value) => String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                const formatAmount = (minor, currency) => {
                    const numeric = Number(minor ?? 0) / 100;
                    return `${escapeHtml(currency)} ${numeric.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    })}`;
                };

                const readinessStatus = (readiness) => {
                    if (!readiness || typeof readiness !== 'object') return ['—', ''];
                    const blocked = readiness.ready === false || readiness.status === 'blocked';
                    return [blocked ? @json(__('mcp.financial_overview.blocked')) : @json(__('mcp.financial_overview.ready')), blocked ? 'danger' : 'good'];
                };

                const render = (data) => {
                    if (!data || typeof data !== 'object') {
                        root.innerHTML = `<p class="error">${escapeHtml(@json(__('mcp.financial_overview.unavailable')))}</p>`;
                        return;
                    }

                    const cash = Array.isArray(data.cash) ? data.cash : [];
                    const status = readinessStatus(data.readiness);
                    const cashRows = cash.length === 0
                        ? `<p class="empty">${escapeHtml(@json(__('mcp.financial_overview.no_accounts')))}</p>`
                        : cash.map((account) => `<div class="row">
                            <div><strong>${escapeHtml(account.name ?? @json(__('mcp.financial_overview.bank_account')))}</strong><div class="detail">${escapeHtml(account.status ?? '')}</div></div>
                            <span class="amount" dir="ltr">${formatAmount(account.balance_minor, account.currency)}</span>
                        </div>`).join('');

                    root.innerHTML = `<header>
                        <div><h1>${escapeHtml(data.workspace?.name ?? @json(__('mcp.financial_overview.title')))}</h1><p class="subtitle">${escapeHtml(@json(__('mcp.financial_overview.subtitle')))}</p></div>
                        <p class="timestamp">${escapeHtml(data.generated_at ?? '')}</p>
                    </header>
                    <div class="grid">
                        <article class="card"><div class="metric-label">${escapeHtml(@json(__('mcp.financial_overview.open_invoices')))}</div><div class="metric">${escapeHtml(data.invoices?.open_count ?? 0)}</div></article>
                        <article class="card"><div class="metric-label">${escapeHtml(@json(__('mcp.financial_overview.expenses')))}</div><div class="metric">${escapeHtml(data.expenses?.count ?? 0)}</div></article>
                        <article class="card"><div class="metric-label">${escapeHtml(@json(__('mcp.financial_overview.pending_bank')))}</div><div class="metric">${escapeHtml(data.pending_bank_transactions ?? 0)}</div></article>
                        <article class="card"><div class="metric-label">${escapeHtml(@json(__('mcp.financial_overview.period_readiness')))}</div><div class="metric"><span class="status ${status[1]}">${escapeHtml(status[0])}</span></div></article>
                    </div>
                    <section class="card"><div class="section-heading"><h2>${escapeHtml(@json(__('mcp.financial_overview.cash_heading')))}</h2><span class="detail">${escapeHtml(data.period?.status ?? '')}</span></div>${cashRows}</section>`;
                };

                app.onToolResult((event) => render(event?.structuredContent ?? event));
            });
        </script>
    </x-slot:head>

    <main id="financial-overview" aria-live="polite">
        <p class="empty">{{ __('mcp.financial_overview.loading') }}</p>
    </main>
</x-mcp::app>
