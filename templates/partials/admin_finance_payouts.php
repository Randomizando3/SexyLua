<section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h3 class="text-2xl font-extrabold">Gestao de saques</h3>
            <p class="mt-2 text-sm text-on-surface-variant">Acompanhe status, copie a chave PIX e finalize os pagamentos por aqui.</p>
        </div>
        <span class="text-sm font-bold text-primary"><?= luacoin_brl_pair_html((int) ($summary['pending_payout_tokens'] ?? 0), $luacoinPriceBrl, 'inline-flex flex-col items-end gap-0.5 leading-tight', 'inline-flex items-center gap-1.5 whitespace-nowrap text-primary', 'block text-[11px] font-bold leading-tight text-slate-500') ?></span>
    </div>

    <form action="/admin/finance" class="mt-6 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-low p-5 xl:grid-cols-[1fr_0.4fr_auto]" method="get">
        <input name="tab" type="hidden" value="payouts">
        <input class="rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_q" placeholder="Buscar por @usuario, e-mail, nota ou chave PIX..." type="search" value="<?= e((string) ($payoutFilters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_status">
            <option value="">Todos os status</option>
            <option value="pending" <?= (string) ($payoutFilters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendentes</option>
            <option value="processing" <?= (string) ($payoutFilters['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Em processamento</option>
            <option value="paid" <?= (string) ($payoutFilters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Pagos</option>
            <option value="rejected" <?= (string) ($payoutFilters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeitados</option>
        </select>
        <div class="flex items-center gap-3">
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-white px-6 py-4 text-sm font-bold text-slate-600" href="/admin/finance?tab=payouts">Reset</a>
        </div>
    </form>

    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($payoutTransactions as $transaction): ?>
            <?php
            $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
            $transactionStatus = (string) ($transaction['status'] ?? 'pending');
            $payoutKey = trim((string) ($transaction['payout_key'] ?? ''));
            ?>
            <form action="/admin/finance/review-payout" class="rounded-3xl bg-surface-container-low p-5" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <input name="transaction_id" type="hidden" value="<?= e((string) ($transaction['id'] ?? 0)) ?>">
                <input name="tab" type="hidden" value="payouts">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-lg font-bold"><?= e(user_handle($user, 'usuario')) ?></p>
                        <p class="mt-1 truncate text-sm text-on-surface-variant"><?= e((string) ($user['email'] ?? '')) ?></p>
                        <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">PIX • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                    </div>
                    <div class="text-right"><?= luacoin_brl_pair_html((int) ($transaction['amount'] ?? 0), $luacoinPriceBrl, 'inline-flex flex-col items-end gap-1 leading-tight', 'inline-flex items-center gap-2 whitespace-nowrap text-2xl font-extrabold text-primary', 'block text-xs font-bold leading-tight text-slate-500') ?></div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] <?= e($financeStatusClass($transactionStatus)) ?>"><?= e($financeStatusLabel($transactionStatus)) ?></span>
                </div>

                <?php if ($payoutKey !== ''): ?>
                    <button class="relative mt-4 flex w-full items-center justify-between gap-4 rounded-2xl bg-white px-4 py-4 text-left shadow-sm transition hover:bg-[#fdfbfc]" data-copy-feedback="Chave PIX copiada" data-copy-text="<?= e($payoutKey) ?>" type="button">
                        <span class="absolute right-4 top-3 hidden rounded-full bg-slate-900 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-white" data-copy-status>Copiado</span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Chave PIX</p>
                            <p class="mt-2 break-all text-sm font-bold text-slate-700"><?= e($payoutKey) ?></p>
                        </div>
                        <span class="material-symbols-outlined shrink-0 text-slate-500">content_copy</span>
                    </button>
                <?php else: ?>
                    <div class="mt-4 rounded-2xl bg-white px-4 py-4 text-sm text-slate-500 shadow-sm">Nenhuma chave PIX informada neste saque.</div>
                <?php endif; ?>

                <div class="mt-5 grid grid-cols-1 gap-4">
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</span>
                        <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                            <option value="pending" <?= $transactionStatus === 'pending' ? 'selected' : '' ?>>Pendente</option>
                            <option value="processing" <?= $transactionStatus === 'processing' ? 'selected' : '' ?>>Em processamento</option>
                            <option value="paid" <?= $transactionStatus === 'paid' ? 'selected' : '' ?>>Pago</option>
                            <option value="rejected" <?= $transactionStatus === 'rejected' ? 'selected' : '' ?>>Rejeitado</option>
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nota</span>
                        <textarea class="min-h-24 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="admin_note" placeholder="Ex.: PIX pago, em lote ou devolvido."><?= e((string) ($transaction['admin_note'] ?? '')) ?></textarea>
                    </label>
                </div>

                <button class="mt-5 w-full rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar status do saque</button>
            </form>
        <?php endforeach; ?>
    </div>

    <?php if ($payoutTransactions === []): ?><p class="mt-6 rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum saque encontrado com esse filtro.</p><?php endif; ?>

    <?php if ((int) ($payoutPagination['total_pages'] ?? 1) > 1): ?>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">Mostrando <?= e((string) ($payoutPagination['start'] ?? 0)) ?>-<?= e((string) ($payoutPagination['end'] ?? 0)) ?> de <?= e((string) ($payoutPagination['total'] ?? 0)) ?> saques</p>
            <div class="flex flex-wrap gap-2">
                <?php for ($page = 1; $page <= (int) ($payoutPagination['total_pages'] ?? 1); $page++): ?>
                    <a class="<?= $page === (int) ($payoutPagination['page'] ?? 1) ? 'bg-primary text-white' : 'bg-surface-container-low text-slate-600' ?> inline-flex h-11 min-w-[44px] items-center justify-center rounded-full px-4 text-sm font-bold" href="<?= e($buildFinanceUrl(['tab' => 'payouts', 'payout_page' => $page])) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
