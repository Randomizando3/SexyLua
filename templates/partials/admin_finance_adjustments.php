<section class="space-y-8">
    <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-2xl font-extrabold">Ajuste manual de carteira</h3>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary/10"><?= luacoin_icon('h-5 w-5') ?></span>
        </div>
        <form action="/admin/finance/adjust-wallet" class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_0.35fr_0.35fr]" method="post">
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <input name="tab" type="hidden" value="adjustments">
            <input data-wallet-user-id name="user_id" type="hidden" value="<?= e((string) ((int) ($defaultWalletUser['id'] ?? 0))) ?>">

            <label class="block space-y-2 xl:col-span-3">
                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Usuario</span>
                <button class="flex w-full items-center justify-between gap-4 rounded-2xl border-none bg-surface-container-low px-5 py-4 text-left shadow-sm focus:ring-2 focus:ring-primary/20" data-prototype-skip="1" data-wallet-user-open type="button">
                    <span class="min-w-0" data-wallet-user-summary>
                        <?php if ($defaultWalletUser): ?>
                            <strong class="block truncate text-sm text-on-surface"><?= e(user_handle($defaultWalletUser, 'usuario')) ?></strong>
                            <span class="mt-1 block truncate text-xs text-slate-500"><?= e((string) ($defaultWalletUser['email'] ?? '')) ?> • <?= e(token_amount((int) ($defaultWalletUser['wallet_balance'] ?? 0))) ?></span>
                        <?php else: ?>
                            <strong class="block truncate text-sm text-on-surface">Selecione um usuario</strong>
                            <span class="mt-1 block truncate text-xs text-slate-500">Abra a lista para buscar e escolher.</span>
                        <?php endif; ?>
                    </span>
                    <span class="material-symbols-outlined text-slate-500">expand_more</span>
                </button>
            </label>
            <label class="block space-y-2">
                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Direcao</span>
                <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="direction">
                    <option value="credit">Creditar</option>
                    <option value="debit">Debitar</option>
                </select>
            </label>
            <label class="block space-y-2">
                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">LuaCoins</span>
                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="luacoins" step="1" type="number" value="50">
            </label>
            <label class="block space-y-2">
                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nota</span>
                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="note" type="text" value="Ajuste manual do admin">
            </label>
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white xl:col-span-3" data-prototype-skip="1" type="submit">Aplicar ajuste</button>
        </form>
    </section>

    <div class="fixed inset-0 z-[90] hidden items-end justify-center bg-slate-900/45 p-3 sm:items-center sm:p-6" data-wallet-user-modal hidden>
        <div class="flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-[2rem] bg-surface-container-lowest shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Selecionar usuario</p>
                    <h3 class="mt-2 text-2xl font-extrabold">Carteira para ajuste manual</h3>
                    <p class="mt-2 text-sm text-on-surface-variant">Busque por @usuario, nome ou e-mail e escolha o perfil certo para creditar ou debitar LuaCoins.</p>
                </div>
                <button class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-surface-container-low text-slate-500" data-prototype-skip="1" data-wallet-user-close type="button">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" data-wallet-user-search placeholder="Buscar por @usuario, nome ou e-mail..." type="search">
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6">
                <div class="grid gap-3" data-wallet-user-list></div>
                <p class="hidden rounded-3xl bg-surface-container-low p-5 text-sm text-on-surface-variant" data-wallet-user-empty>Nenhum usuario encontrado com esse filtro.</p>
            </div>
            <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-5 py-4 sm:px-6">
                <button class="rounded-full bg-surface-container-low px-5 py-3 text-sm font-bold text-on-surface-variant" data-prototype-skip="1" data-wallet-user-prev type="button">Anterior</button>
                <span class="text-sm font-bold text-slate-500" data-wallet-user-page>Pagina 1</span>
                <button class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" data-prototype-skip="1" data-wallet-user-next type="button">Proxima</button>
            </div>
        </div>
    </div>

    <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-2xl font-extrabold">Ajustes recentes</h3>
                <p class="mt-2 text-sm text-on-surface-variant">Historico das alteracoes manuais feitas pelo admin na carteira.</p>
            </div>
            <span class="text-sm font-bold text-primary"><?= e((string) ($adjustmentPagination['total'] ?? 0)) ?> registros</span>
        </div>

        <form action="/admin/finance" class="mt-6 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-low p-5 xl:grid-cols-[1fr_auto]" method="get">
            <input name="tab" type="hidden" value="adjustments">
            <input class="rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="adjustment_q" placeholder="Buscar por @usuario, nota ou e-mail..." type="search" value="<?= e((string) ($adjustmentFilters['q'] ?? '')) ?>">
            <div class="flex items-center gap-3">
                <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                <a class="rounded-full bg-white px-6 py-4 text-sm font-bold text-slate-600" href="/admin/finance?tab=adjustments">Reset</a>
            </div>
        </form>

        <div class="mt-6 space-y-4">
            <?php foreach ($adjustmentTransactions as $transaction): ?>
                <?php
                $isCredit = (string) ($transaction['type'] ?? '') === 'admin_credit';
                $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
                ?>
                <article class="rounded-3xl bg-surface-container-low p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold"><?= e($isCredit ? 'Credito manual' : 'Debito manual') ?></p>
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] <?= $isCredit ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>"><?= e($isCredit ? 'Credito' : 'Debito') ?></span>
                            </div>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= e(user_handle($user, 'usuario')) ?> • <?= e((string) ($user['email'] ?? '')) ?></p>
                            <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                            <?php if (trim((string) ($transaction['note'] ?? '')) !== ''): ?>
                                <p class="mt-3 text-sm text-slate-500"><?= e((string) ($transaction['note'] ?? '')) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="text-right">
                            <div class="<?= $isCredit ? 'text-emerald-600' : 'text-rose-700' ?> text-xl font-extrabold"><?= $isCredit ? '+' : '-' ?><?= luacoin_amount_html((int) ($transaction['amount'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.85em] w-[0.85em] shrink-0') ?></div>
                            <p class="mt-1 text-xs font-bold text-slate-500"><?= e(brl_amount(luacoin_to_brl((int) ($transaction['amount'] ?? 0), $luacoinPriceBrl))) ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($adjustmentTransactions === []): ?><p class="mt-6 rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum ajuste manual encontrado com esse filtro.</p><?php endif; ?>

        <?php if ((int) ($adjustmentPagination['total_pages'] ?? 1) > 1): ?>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">Mostrando <?= e((string) ($adjustmentPagination['start'] ?? 0)) ?>-<?= e((string) ($adjustmentPagination['end'] ?? 0)) ?> de <?= e((string) ($adjustmentPagination['total'] ?? 0)) ?> ajustes</p>
                <div class="flex flex-wrap gap-2">
                    <?php for ($page = 1; $page <= (int) ($adjustmentPagination['total_pages'] ?? 1); $page++): ?>
                        <a class="<?= $page === (int) ($adjustmentPagination['page'] ?? 1) ? 'bg-primary text-white' : 'bg-surface-container-low text-slate-600' ?> inline-flex h-11 min-w-[44px] items-center justify-center rounded-full px-4 text-sm font-bold" href="<?= e($buildFinanceUrl(['tab' => 'adjustments', 'adjustment_page' => $page])) ?>"><?= e((string) $page) ?></a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</section>
