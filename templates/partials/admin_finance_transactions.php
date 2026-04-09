<section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h3 class="text-2xl font-extrabold">Transacoes</h3>
            <p class="mt-2 text-sm text-on-surface-variant">Linha do tempo completa de entradas e saidas da plataforma.</p>
        </div>
        <span class="text-sm font-bold text-primary"><?= e((string) ($transactionPagination['total'] ?? 0)) ?> registros</span>
    </div>

    <form action="/admin/finance" class="mt-6 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-low p-5 xl:grid-cols-[1fr_0.35fr_0.35fr_auto]" method="get">
        <input name="tab" type="hidden" value="transactions">
        <input class="rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="transaction_q" placeholder="Buscar por @usuario, nota, tipo, e-mail ou chave PIX..." type="search" value="<?= e((string) ($transactionFilters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="transaction_type">
            <option value="">Todos os tipos</option>
            <option value="top_up" <?= (string) ($transactionFilters['type'] ?? '') === 'top_up' ? 'selected' : '' ?>>Recarga</option>
            <option value="top_up_pending" <?= (string) ($transactionFilters['type'] ?? '') === 'top_up_pending' ? 'selected' : '' ?>>Recarga pendente</option>
            <option value="subscription" <?= (string) ($transactionFilters['type'] ?? '') === 'subscription' ? 'selected' : '' ?>>Assinatura</option>
            <option value="tip" <?= (string) ($transactionFilters['type'] ?? '') === 'tip' ? 'selected' : '' ?>>Gorjeta</option>
            <option value="instant_content" <?= (string) ($transactionFilters['type'] ?? '') === 'instant_content' ? 'selected' : '' ?>>Microconteudo</option>
            <option value="payout_request" <?= (string) ($transactionFilters['type'] ?? '') === 'payout_request' ? 'selected' : '' ?>>Saque</option>
            <option value="admin_" <?= (string) ($transactionFilters['type'] ?? '') === 'admin_' ? 'selected' : '' ?>>Ajuste manual</option>
        </select>
        <select class="rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="transaction_status">
            <option value="">Todos os status</option>
            <option value="completed" <?= (string) ($transactionFilters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Concluido</option>
            <option value="approved" <?= (string) ($transactionFilters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Aprovado</option>
            <option value="pending" <?= (string) ($transactionFilters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
            <option value="processing" <?= (string) ($transactionFilters['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Em processamento</option>
            <option value="paid" <?= (string) ($transactionFilters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Pago</option>
            <option value="rejected" <?= (string) ($transactionFilters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeitado</option>
        </select>
        <div class="flex items-center gap-3 xl:col-span-4 xl:justify-end">
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-white px-6 py-4 text-sm font-bold text-slate-600" href="/admin/finance?tab=transactions">Reset</a>
        </div>
    </form>

    <div class="mt-6 space-y-4">
        <?php foreach ($transactionRows as $transaction): ?>
            <?php
            $direction = (string) ($transaction['direction'] ?? 'in');
            $isIn = $direction === 'in';
            $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
            $creator = is_array($transaction['creator'] ?? null) ? $transaction['creator'] : [];
            $transactionStatus = (string) ($transaction['status'] ?? 'completed');
            ?>
            <article class="rounded-3xl bg-surface-container-low p-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-lg font-bold"><?= e((string) ($transaction['note'] ?? 'Transacao')) ?></p>
                            <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] <?= e($financeStatusClass($transactionStatus)) ?>"><?= e($financeStatusLabel($transactionStatus)) ?></span>
                        </div>
                        <p class="mt-1 text-sm text-on-surface-variant"><?= e(user_handle($user, 'usuario')) ?><?php if (trim(user_handle($creator, '')) !== ''): ?> • <?= e(user_handle($creator, '')) ?><?php endif; ?></p>
                        <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['type'] ?? 'mov')) ?> • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                        <?php if (trim((string) ($transaction['admin_note'] ?? '')) !== ''): ?>
                            <p class="mt-3 text-sm text-slate-500"><?= e((string) ($transaction['admin_note'] ?? '')) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <div class="<?= $isIn ? 'text-emerald-600' : 'text-rose-700' ?> text-xl font-extrabold"><?= $isIn ? '+' : '-' ?><?= luacoin_amount_html((int) ($transaction['amount'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.85em] w-[0.85em] shrink-0') ?></div>
                        <p class="mt-1 text-xs font-bold text-slate-500"><?= e(brl_amount(luacoin_to_brl((int) ($transaction['amount'] ?? 0), $luacoinPriceBrl))) ?></p>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($transactionRows === []): ?><p class="mt-6 rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma transacao encontrada com esse filtro.</p><?php endif; ?>

    <?php if ((int) ($transactionPagination['total_pages'] ?? 1) > 1): ?>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">Mostrando <?= e((string) ($transactionPagination['start'] ?? 0)) ?>-<?= e((string) ($transactionPagination['end'] ?? 0)) ?> de <?= e((string) ($transactionPagination['total'] ?? 0)) ?> transacoes</p>
            <div class="flex flex-wrap gap-2">
                <?php for ($page = 1; $page <= (int) ($transactionPagination['total_pages'] ?? 1); $page++): ?>
                    <a class="<?= $page === (int) ($transactionPagination['page'] ?? 1) ? 'bg-primary text-white' : 'bg-surface-container-low text-slate-600' ?> inline-flex h-11 min-w-[44px] items-center justify-center rounded-full px-4 text-sm font-bold" href="<?= e($buildFinanceUrl(['tab' => 'transactions', 'transaction_page' => $page])) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
