<section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h3 class="text-2xl font-extrabold">Gestao de recargas</h3>
            <p class="mt-2 text-sm text-on-surface-variant">Aprove, rejeite e acompanhe as recargas em um unico fluxo.</p>
        </div>
        <span class="text-sm font-bold text-primary"><?= e((string) ($topupPagination['total'] ?? 0)) ?> registros</span>
    </div>

    <form action="/admin/finance" class="mt-6 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-low p-5 xl:grid-cols-[1fr_0.4fr_auto]" method="get">
        <input name="tab" type="hidden" value="topups">
        <input class="rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="topup_q" placeholder="Buscar por @usuario, e-mail, nota ou referencia..." type="search" value="<?= e((string) ($topupFilters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="topup_status">
            <option value="">Todos os status</option>
            <option value="pending" <?= (string) ($topupFilters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendentes</option>
            <option value="approved" <?= (string) ($topupFilters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Aprovadas</option>
            <option value="rejected" <?= (string) ($topupFilters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeitadas</option>
        </select>
        <div class="flex items-center gap-3">
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-white px-6 py-4 text-sm font-bold text-slate-600" href="/admin/finance?tab=topups">Reset</a>
        </div>
    </form>

    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($topupTransactions as $transaction): ?>
            <?php
            $transactionType = (string) ($transaction['type'] ?? 'top_up_pending');
            $transactionStatus = (string) ($transaction['status'] ?? ($transactionType === 'top_up_pending' ? 'pending' : 'approved'));
            $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
            $isPending = $transactionType === 'top_up_pending' && $transactionStatus === 'pending';
            ?>
            <article class="rounded-3xl bg-surface-container-low p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-lg font-bold"><?= e(user_handle($user, 'usuario')) ?></p>
                        <p class="mt-1 truncate text-sm text-on-surface-variant"><?= e((string) ($user['email'] ?? '')) ?></p>
                        <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['provider'] ?? 'syncpay')) ?> • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                    </div>
                    <div class="text-right"><?= luacoin_brl_pair_html((int) ($transaction['amount'] ?? 0), $luacoinPriceBrl, 'inline-flex flex-col items-end gap-1 leading-tight', 'inline-flex items-center gap-2 whitespace-nowrap text-2xl font-extrabold text-primary', 'block text-xs font-bold leading-tight text-slate-500') ?></div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] <?= e($financeStatusClass($transactionStatus)) ?>"><?= e($financeStatusLabel($transactionStatus)) ?></span>
                    <?php if (trim((string) ($transaction['external_reference'] ?? '')) !== ''): ?>
                        <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-slate-500"><?= e((string) ($transaction['external_reference'] ?? '')) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($isPending): ?>
                    <form action="/admin/finance/review-topup" class="mt-5 space-y-4" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="transaction_id" type="hidden" value="<?= e((string) ($transaction['id'] ?? 0)) ?>">
                        <input name="tab" type="hidden" value="topups">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</span>
                            <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                                <option value="approved">Aprovar</option>
                                <option value="rejected">Rejeitar</option>
                            </select>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nota</span>
                            <textarea class="min-h-24 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="admin_note" placeholder="Ex.: comprovante validado manualmente."><?= e((string) ($transaction['admin_note'] ?? '')) ?></textarea>
                        </label>
                        <button class="w-full rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Revisar recarga</button>
                    </form>
                <?php else: ?>
                    <div class="mt-5 rounded-3xl bg-white px-5 py-4 text-sm text-slate-500 shadow-sm">
                        <?= trim((string) ($transaction['admin_note'] ?? '')) !== '' ? e((string) ($transaction['admin_note'] ?? '')) : 'Recarga finalizada. Nenhuma acao pendente.' ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($topupTransactions === []): ?><p class="mt-6 rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma recarga encontrada com esse filtro.</p><?php endif; ?>

    <?php if ((int) ($topupPagination['total_pages'] ?? 1) > 1): ?>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">Mostrando <?= e((string) ($topupPagination['start'] ?? 0)) ?>-<?= e((string) ($topupPagination['end'] ?? 0)) ?> de <?= e((string) ($topupPagination['total'] ?? 0)) ?> recargas</p>
            <div class="flex flex-wrap gap-2">
                <?php for ($page = 1; $page <= (int) ($topupPagination['total_pages'] ?? 1); $page++): ?>
                    <a class="<?= $page === (int) ($topupPagination['page'] ?? 1) ? 'bg-primary text-white' : 'bg-surface-container-low text-slate-600' ?> inline-flex h-11 min-w-[44px] items-center justify-center rounded-full px-4 text-sm font-bold" href="<?= e($buildFinanceUrl(['tab' => 'topups', 'topup_page' => $page])) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
