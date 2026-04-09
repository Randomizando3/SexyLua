<?php

declare(strict_types=1);

$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$allTransactions = $data['transactions'] ?? [];
$users = $data['users'] ?? [];
$luacoinPriceBrl = (float) ($data['luacoin_price_brl'] ?? 0.07);
$admin = $app->auth->user() ?? [];
$defaultWalletUser = $users[0] ?? null;
$walletPickerUsers = array_map(static function (array $user): array {
    return [
        'id' => (int) ($user['id'] ?? 0),
        'name' => (string) ($user['name'] ?? 'Usuario'),
        'username' => (string) ($user['username'] ?? ''),
        'email' => (string) ($user['email'] ?? ''),
        'role' => (string) ($user['role'] ?? 'subscriber'),
        'wallet_balance' => (int) ($user['wallet_balance'] ?? 0),
        'handle' => user_handle($user, 'usuario'),
    ];
}, $users);

$currentTab = in_array((string) ($filters['tab'] ?? 'payouts'), ['topups', 'payouts', 'transactions', 'adjustments'], true)
    ? (string) ($filters['tab'] ?? 'payouts')
    : 'payouts';
$topupFilters = array_merge(['q' => '', 'status' => '', 'page' => 1], is_array($filters['topups'] ?? null) ? $filters['topups'] : []);
$payoutFilters = array_merge(['q' => '', 'status' => '', 'page' => 1], is_array($filters['payouts'] ?? null) ? $filters['payouts'] : []);
$transactionFilters = array_merge(['q' => '', 'type' => '', 'status' => '', 'page' => 1], is_array($filters['transactions'] ?? null) ? $filters['transactions'] : []);
$adjustmentFilters = array_merge(['q' => '', 'page' => 1], is_array($filters['adjustments'] ?? null) ? $filters['adjustments'] : []);

$matchesFinanceQuery = static function (array $transaction, string $query): bool {
    $query = mb_strtolower(trim($query));
    if ($query === '') {
        return true;
    }

    $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
    $creator = is_array($transaction['creator'] ?? null) ? $transaction['creator'] : [];
    $haystack = mb_strtolower(implode(' ', array_filter([
        (string) ($transaction['note'] ?? ''),
        (string) ($transaction['admin_note'] ?? ''),
        (string) ($transaction['type'] ?? ''),
        (string) ($transaction['status'] ?? ''),
        (string) ($transaction['provider'] ?? ''),
        (string) ($transaction['external_reference'] ?? ''),
        (string) ($transaction['payout_key'] ?? ''),
        (string) ($user['name'] ?? ''),
        (string) ($user['username'] ?? ''),
        (string) ($user['email'] ?? ''),
        (string) ($creator['name'] ?? ''),
        (string) ($creator['username'] ?? ''),
        (string) ($creator['email'] ?? ''),
    ])));

    return str_contains($haystack, $query);
};

$paginateFinanceItems = static function (array $items, int $page, int $perPage): array {
    $total = count($items);
    $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    return [
        'items' => array_slice($items, $offset, $perPage),
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'start' => $total === 0 ? 0 : $offset + 1,
        'end' => $total === 0 ? 0 : min($offset + $perPage, $total),
    ];
};

$financeStatusLabel = static function (string $status): string {
    return match ($status) {
        'completed', 'approved' => 'Concluido',
        'paid' => 'Pago',
        'pending' => 'Pendente',
        'processing' => 'Em processamento',
        'rejected' => 'Rejeitado',
        default => ucfirst($status),
    };
};

$financeStatusClass = static function (string $status): string {
    return match ($status) {
        'completed', 'approved', 'paid' => 'bg-emerald-100 text-emerald-700',
        'processing', 'pending' => 'bg-amber-100 text-amber-700',
        'rejected' => 'bg-rose-100 text-rose-700',
        default => 'bg-slate-200 text-slate-600',
    };
};

$buildFinanceUrl = static function (array $overrides = []) use ($currentTab, $topupFilters, $payoutFilters, $transactionFilters, $adjustmentFilters): string {
    $params = array_merge([
        'tab' => $currentTab,
        'topup_q' => (string) ($topupFilters['q'] ?? ''),
        'topup_status' => (string) ($topupFilters['status'] ?? ''),
        'topup_page' => (int) ($topupFilters['page'] ?? 1),
        'payout_q' => (string) ($payoutFilters['q'] ?? ''),
        'payout_status' => (string) ($payoutFilters['status'] ?? ''),
        'payout_page' => (int) ($payoutFilters['page'] ?? 1),
        'transaction_q' => (string) ($transactionFilters['q'] ?? ''),
        'transaction_type' => (string) ($transactionFilters['type'] ?? ''),
        'transaction_status' => (string) ($transactionFilters['status'] ?? ''),
        'transaction_page' => (int) ($transactionFilters['page'] ?? 1),
        'adjustment_q' => (string) ($adjustmentFilters['q'] ?? ''),
        'adjustment_page' => (int) ($adjustmentFilters['page'] ?? 1),
    ], $overrides);

    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
            continue;
        }

        if (str_ends_with((string) $key, '_page') && (int) $value <= 1) {
            unset($params[$key]);
        }
    }

    return path_with_query('/admin/finance', $params);
};

$topupTransactionsAll = array_values(array_filter($allTransactions, static function (array $transaction) use ($topupFilters, $matchesFinanceQuery): bool {
    $type = (string) ($transaction['type'] ?? '');
    if (! in_array($type, ['top_up_pending', 'top_up'], true)) {
        return false;
    }
    $transactionStatus = (string) ($transaction['status'] ?? ($type === 'top_up_pending' ? 'pending' : 'approved'));
    if ((string) ($topupFilters['status'] ?? '') !== '' && $transactionStatus !== (string) ($topupFilters['status'] ?? '')) {
        return false;
    }
    return $matchesFinanceQuery($transaction, (string) ($topupFilters['q'] ?? ''));
}));
$topupPagination = $paginateFinanceItems($topupTransactionsAll, (int) ($topupFilters['page'] ?? 1), 15);
$topupTransactions = $topupPagination['items'];

$payoutTransactionsAll = array_values(array_filter($allTransactions, static function (array $transaction) use ($payoutFilters, $matchesFinanceQuery): bool {
    if ((string) ($transaction['type'] ?? '') !== 'payout_request') {
        return false;
    }
    $transactionStatus = (string) ($transaction['status'] ?? 'pending');
    if ((string) ($payoutFilters['status'] ?? '') !== '' && $transactionStatus !== (string) ($payoutFilters['status'] ?? '')) {
        return false;
    }
    return $matchesFinanceQuery($transaction, (string) ($payoutFilters['q'] ?? ''));
}));
$payoutPagination = $paginateFinanceItems($payoutTransactionsAll, (int) ($payoutFilters['page'] ?? 1), 15);
$payoutTransactions = $payoutPagination['items'];

$adjustmentTransactionsAll = array_values(array_filter($allTransactions, static function (array $transaction) use ($adjustmentFilters, $matchesFinanceQuery): bool {
    if (! in_array((string) ($transaction['type'] ?? ''), ['admin_credit', 'admin_debit'], true)) {
        return false;
    }
    return $matchesFinanceQuery($transaction, (string) ($adjustmentFilters['q'] ?? ''));
}));
$adjustmentPagination = $paginateFinanceItems($adjustmentTransactionsAll, (int) ($adjustmentFilters['page'] ?? 1), 15);
$adjustmentTransactions = $adjustmentPagination['items'];

$transactionRowsAll = array_values(array_filter($allTransactions, static function (array $transaction) use ($transactionFilters, $matchesFinanceQuery): bool {
    if ((string) ($transactionFilters['type'] ?? '') !== '' && ! str_contains((string) ($transaction['type'] ?? ''), (string) ($transactionFilters['type'] ?? ''))) {
        return false;
    }
    if ((string) ($transactionFilters['status'] ?? '') !== '' && (string) ($transaction['status'] ?? 'completed') !== (string) ($transactionFilters['status'] ?? '')) {
        return false;
    }
    return $matchesFinanceQuery($transaction, (string) ($transactionFilters['q'] ?? ''));
}));
$transactionPagination = $paginateFinanceItems($transactionRowsAll, (int) ($transactionFilters['page'] ?? 1), 20);
$transactionRows = $transactionPagination['items'];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Financeiro</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { primary: "#ab1155", background: "#fbf9fb", "surface-container-lowest": "#ffffff", "surface-container-low": "#f5f3f5", "surface-container-high": "#e9e7e9", "on-surface": "#1b1c1d", "on-surface-variant": "#5a4044" },
                    fontFamily: { headline: ["Plus Jakarta Sans"], body: ["Manrope"] },
                    borderRadius: { DEFAULT: "1rem", lg: "2rem", xl: "3rem", full: "9999px" },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        body { background: #fbf9fb; color: #1b1c1d; font-family: "Manrope", sans-serif; }
        h1, h2, h3, h4 { font-family: "Plus Jakarta Sans", sans-serif; }
        [data-wallet-user-modal][hidden] { display: none !important; }
    </style>
</head>
<body class="min-h-screen">
<?php
$adminTopbarUser = $admin;
require BASE_PATH . '/templates/partials/admin_topbar.php';
$adminSidebarCurrent = 'finance';
$adminSidebarMetricTitle = 'Resultado da plataforma';
$adminSidebarMetricValue = token_amount((int) ($summary['platform_result'] ?? 0));
$adminSidebarMetricDescription = 'Margem liquida aproximada entre consumo e repasse.';
require BASE_PATH . '/templates/partials/admin_sidebar.php';
?>
<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Fluxo de caixa</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Visao <span class="italic text-primary">Financeira</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Acompanhe volume bruto, repasses, recargas, saques e ajustes de carteira com foco operacional.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Volume</p><div class="mt-2"><?= luacoin_brl_pair_html((int) ($summary['gross_volume'] ?? 0), $luacoinPriceBrl, 'inline-flex max-w-full flex-col items-center gap-1 leading-tight', 'inline-flex max-w-full items-center justify-center gap-1 whitespace-nowrap text-[1.55rem] font-extrabold leading-none tracking-tight text-primary md:text-[1.9rem]', 'block text-[11px] font-bold leading-tight text-slate-500') ?></div></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Repasse</p><div class="mt-2"><?= luacoin_brl_pair_html((int) ($summary['creator_income'] ?? 0), $luacoinPriceBrl, 'inline-flex max-w-full flex-col items-center gap-1 leading-tight', 'inline-flex max-w-full items-center justify-center gap-1 whitespace-nowrap text-[1.55rem] font-extrabold leading-none tracking-tight text-emerald-600 md:text-[1.9rem]', 'block text-[11px] font-bold leading-tight text-slate-500') ?></div></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Resultado</p><div class="mt-2"><?= luacoin_brl_pair_html((int) ($summary['platform_result'] ?? 0), $luacoinPriceBrl, 'inline-flex max-w-full flex-col items-center gap-1 leading-tight', 'inline-flex max-w-full items-center justify-center gap-1 whitespace-nowrap text-[1.55rem] font-extrabold leading-none tracking-tight text-primary md:text-[1.9rem]', 'block text-[11px] font-bold leading-tight text-slate-500') ?></div></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Recargas</p><p class="mt-2 text-[1.8rem] font-extrabold leading-tight tracking-tight text-primary md:text-[2.1rem]"><?= e((string) ($summary['top_ups'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Saques pendentes</p><p class="mt-2 text-[1.8rem] font-extrabold leading-tight tracking-tight text-amber-600 md:text-[2.1rem]"><?= e((string) ($summary['pending_payout_count'] ?? 0)) ?></p></article>
        </div>
    </section>

    <nav class="mb-8 flex flex-wrap gap-3">
        <?php foreach ([
            'topups' => ['label' => 'Recargas', 'icon' => 'account_balance_wallet'],
            'payouts' => ['label' => 'Saques', 'icon' => 'payments'],
            'adjustments' => ['label' => 'Ajuste de carteira', 'icon' => 'tune'],
            'transactions' => ['label' => 'Transacoes', 'icon' => 'receipt_long'],
        ] as $tabKey => $tabMeta): ?>
            <a class="<?= $currentTab === $tabKey ? 'bg-primary text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]' : 'bg-surface-container-lowest text-slate-700 shadow-sm' ?> inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-bold" href="<?= e($buildFinanceUrl(['tab' => $tabKey])) ?>">
                <span class="material-symbols-outlined text-[18px]"><?= e((string) $tabMeta['icon']) ?></span>
                <?= e((string) $tabMeta['label']) ?>
            </a>
        <?php endforeach; ?>
        <button class="inline-flex items-center gap-2 rounded-full bg-surface-container-lowest px-5 py-3 text-sm font-bold text-slate-700 shadow-sm" data-prototype-skip="1" data-report-open type="button">
            <span class="material-symbols-outlined text-[18px]">analytics</span>
            Relatorios
        </button>
    </nav>

    <?php if ($currentTab === 'topups'): ?>
        <?php include base_path('templates/partials/admin_finance_topups.php'); ?>
    <?php elseif ($currentTab === 'payouts'): ?>
        <?php include base_path('templates/partials/admin_finance_payouts.php'); ?>
    <?php elseif ($currentTab === 'transactions'): ?>
        <?php include base_path('templates/partials/admin_finance_transactions.php'); ?>
    <?php else: ?>
        <?php include base_path('templates/partials/admin_finance_adjustments.php'); ?>
    <?php endif; ?>
</main>
<div class="fixed inset-0 z-[95] hidden items-end justify-center bg-slate-900/45 p-3 sm:items-center sm:p-6" data-report-modal hidden>
    <div class="flex w-full max-w-xl flex-col overflow-hidden rounded-[2rem] bg-surface-container-lowest shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Relatorios</p>
                <h3 class="mt-2 text-2xl font-extrabold">Exportar planilha financeira</h3>
                <p class="mt-2 text-sm text-on-surface-variant">Escolha o periodo e o conjunto de dados. Se selecionar <strong>Todos</strong>, a planilha sai com abas separadas.</p>
            </div>
            <button class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-surface-container-low text-slate-500" data-prototype-skip="1" data-report-close type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/admin/finance/export" class="space-y-6 px-5 py-5 sm:px-6" method="get">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Periodo</p>
                <div class="mt-3 grid grid-cols-3 gap-3">
                    <?php foreach ([1 => '1 dia', 7 => '7 dias', 30 => '30 dias'] as $daysValue => $daysLabel): ?>
                        <label class="cursor-pointer">
                            <input <?= $daysValue === 7 ? 'checked' : '' ?> class="peer sr-only" name="days" type="radio" value="<?= e((string) $daysValue) ?>">
                            <span class="flex items-center justify-center rounded-2xl bg-surface-container-low px-4 py-4 text-sm font-bold text-slate-600 transition peer-checked:bg-primary peer-checked:text-white"><?= e($daysLabel) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <label class="block space-y-2">
                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Conteudo da planilha</span>
                <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="item">
                    <option value="all">Todos (abas separadas)</option>
                    <option value="topups">Recargas</option>
                    <option value="payouts">Saques</option>
                    <option value="transactions">Transacoes</option>
                    <option value="adjustments">Ajustes de carteira</option>
                </select>
            </label>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-slate-600" data-prototype-skip="1" data-report-close type="button">Cancelar</button>
                <button class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Baixar XLSX</button>
            </div>
        </form>
    </div>
</div>
<script>
    (() => {
        const walletUsers = <?= json_encode($walletPickerUsers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const reportModal = document.querySelector('[data-report-modal]');
        const reportOpen = document.querySelector('[data-report-open]');
        const reportCloseButtons = document.querySelectorAll('[data-report-close]');

        if (reportModal && reportOpen) {
            const openReportModal = () => {
                reportModal.hidden = false;
                reportModal.classList.remove('hidden');
                reportModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };
            const closeReportModal = () => {
                reportModal.hidden = true;
                reportModal.classList.add('hidden');
                reportModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };
            reportOpen.addEventListener('click', openReportModal);
            reportCloseButtons.forEach((button) => button.addEventListener('click', closeReportModal));
            reportModal.addEventListener('click', (event) => {
                if (event.target === reportModal) {
                    closeReportModal();
                }
            });
        }

        const modal = document.querySelector('[data-wallet-user-modal]');
        const openButton = document.querySelector('[data-wallet-user-open]');
        const closeButton = document.querySelector('[data-wallet-user-close]');
        const searchInput = document.querySelector('[data-wallet-user-search]');
        const list = document.querySelector('[data-wallet-user-list]');
        const emptyState = document.querySelector('[data-wallet-user-empty]');
        const prevButton = document.querySelector('[data-wallet-user-prev]');
        const nextButton = document.querySelector('[data-wallet-user-next]');
        const pageLabel = document.querySelector('[data-wallet-user-page]');
        const hiddenField = document.querySelector('[data-wallet-user-id]');
        const summary = document.querySelector('[data-wallet-user-summary]');

        if (modal && openButton && closeButton && list && hiddenField && summary) {
            let query = '';
            let page = 1;
            const perPage = 15;
            const esc = (value) => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            const filteredUsers = () => {
                const term = query.trim().toLowerCase();
                if (!term) return walletUsers;
                return walletUsers.filter((user) => `${user.handle} ${user.name} ${user.email} ${user.role}`.toLowerCase().includes(term));
            };
            const selectedUser = () => walletUsers.find((user) => String(user.id) === String(hiddenField.value || '')) || walletUsers[0] || null;
            const updateSummary = () => {
                const user = selectedUser();
                if (!user) {
                    summary.innerHTML = '<strong class="block truncate text-sm text-on-surface">Selecione um usuario</strong><span class="mt-1 block truncate text-xs text-slate-500">Abra a lista para buscar e escolher.</span>';
                    return;
                }
                summary.innerHTML = `<strong class="block truncate text-sm text-on-surface">${esc(user.handle || '@usuario')}</strong><span class="mt-1 block truncate text-xs text-slate-500">${esc(user.email || '')} • ${esc(String(user.wallet_balance || 0))} LuaCoins</span>`;
            };
            const render = () => {
                const items = filteredUsers();
                const totalPages = Math.max(1, Math.ceil(items.length / perPage));
                page = Math.min(page, totalPages);
                const start = (page - 1) * perPage;
                const pageItems = items.slice(start, start + perPage);
                list.innerHTML = pageItems.map((user) => {
                    const active = String(user.id) === String(hiddenField.value || '');
                    return `<button class="rounded-3xl border px-4 py-4 text-left transition ${active ? 'border-primary bg-primary/5' : 'border-transparent bg-surface-container-low hover:border-primary/30'}" data-wallet-user-select="${esc(String(user.id))}" type="button"><span class="block text-sm font-bold text-on-surface">${esc(user.handle || '@usuario')}</span><span class="mt-1 block text-xs text-slate-500">${esc(user.email || '')}</span><span class="mt-3 inline-flex rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-slate-500">${esc(user.role || 'subscriber')} • ${esc(String(user.wallet_balance || 0))} LuaCoins</span></button>`;
                }).join('');
                emptyState.classList.toggle('hidden', pageItems.length > 0);
                pageLabel.textContent = `Pagina ${page} de ${totalPages}`;
                prevButton.disabled = page <= 1;
                nextButton.disabled = page >= totalPages;
            };
            const openModal = () => {
                modal.hidden = false;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
                render();
                if (searchInput) searchInput.focus();
            };
            const closeModal = () => {
                modal.hidden = true;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };
            openButton.addEventListener('click', openModal);
            closeButton.addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
            if (searchInput) {
                searchInput.addEventListener('input', () => { query = searchInput.value || ''; page = 1; render(); });
            }
            prevButton.addEventListener('click', () => { page = Math.max(1, page - 1); render(); });
            nextButton.addEventListener('click', () => { page += 1; render(); });
            list.addEventListener('click', (event) => {
                const button = event.target.closest('[data-wallet-user-select]');
                if (!button) return;
                hiddenField.value = button.getAttribute('data-wallet-user-select') || '';
                updateSummary();
                closeModal();
            });
            updateSummary();
        }

        document.querySelectorAll('[data-copy-text]').forEach((button) => {
            button.addEventListener('click', async () => {
                const text = button.dataset.copyText || '';
                if (!text) return;
                try {
                    await navigator.clipboard.writeText(text);
                } catch {
                    const helper = document.createElement('textarea');
                    helper.value = text;
                    helper.setAttribute('readonly', 'readonly');
                    helper.style.position = 'absolute';
                    helper.style.left = '-9999px';
                    document.body.appendChild(helper);
                    helper.select();
                    document.execCommand('copy');
                    document.body.removeChild(helper);
                }
                const badge = button.querySelector('[data-copy-status]');
                if (!badge) return;
                badge.textContent = button.dataset.copyFeedback || 'Copiado';
                badge.classList.remove('hidden');
                window.clearTimeout(button.__copyTimer);
                button.__copyTimer = window.setTimeout(() => badge.classList.add('hidden'), 1800);
            });
        });
    })();
</script>
</body>
</html>
