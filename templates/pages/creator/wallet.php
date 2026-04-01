<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$transactions = $data['transactions_filtered'] ?? $data['transactions'] ?? [];
$filters = $data['filters'] ?? [];
$summary = $data['summary'] ?? [];
$balance = (int) ($data['balance'] ?? 0);
$minWithdrawal = (int) ($data['min_withdrawal'] ?? 50);
$payoutProfile = $data['payout_profile'] ?? [];
$typeLabels = [
    'subscription' => 'Assinatura',
    'tip' => 'Gorjeta',
    'payout' => 'Saque',
    'top_up' => 'Recarga',
    'manual_adjustment' => 'Ajuste',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SexyLua - Carteira Lunar</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: Manrope, sans-serif; background: #fbf9fb; color: #1b1c1d; }
        .headline { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #D81B60 0%, #ab1155 100%); }
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body>
<?php
$creatorShellCreator = $creator;
$creatorShellCurrent = 'wallet';
$creatorTopbarLabel = 'Carteira Lunar';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Go Live'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>
<main class="px-6 pb-12 pt-24 lg:ml-64 lg:px-10">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Financeiro do criador</p>
            <h2 class="headline mt-2 text-4xl font-extrabold">Carteira e saques</h2>
            <p class="mt-3 max-w-3xl text-slate-500">Saldo em LuaCoins, receita por assinatura, gorjetas e solicitações de saque com chave de pagamento real do criador.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Saldo</p>
                <div class="headline mt-2 text-xl font-extrabold text-[#D81B60] xl:text-2xl"><?= luacoin_amount_html($balance, 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Assinaturas</p>
                <div class="headline mt-2 text-xl font-extrabold text-[#D81B60] xl:text-2xl"><?= luacoin_amount_html((int) ($summary['subscription_income'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Gorjetas</p>
                <div class="headline mt-2 text-xl font-extrabold text-[#D81B60] xl:text-2xl"><?= luacoin_amount_html((int) ($summary['tips_income'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Pendentes</p>
                <div class="headline mt-2 text-xl font-extrabold text-[#D81B60] xl:text-2xl"><?= luacoin_amount_html((int) ($summary['pending_payouts'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="signature-glow flex h-full flex-col justify-between rounded-3xl p-8 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)]">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Disponível para saque</p>
                <div class="headline mt-4 text-4xl font-extrabold sm:text-5xl"><?= luacoin_amount_html($balance, 'inline-flex items-center gap-3 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div>
                <p class="mt-3 text-sm text-white/80">Aproximadamente <?= e(brl_amount((float) ($summary['available_brl'] ?? 0))) ?>, respeitando saque mínimo de <?= e(luacoins_amount($minWithdrawal)) ?>.</p>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-white/12 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/65">Receita líquida</p>
                    <div class="mt-2 text-lg font-extrabold"><?= luacoin_amount_html((int) ($summary['net_income'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></div>
                </div>
                <div class="rounded-2xl bg-white/12 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/65">Pedidos</p>
                    <p class="mt-2 text-lg font-extrabold"><?= e((string) count(array_filter($transactions, static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'payout'))) ?></p>
                </div>
            </div>
        </section>

        <form action="/creator/wallet/payout" class="flex h-full flex-col rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]" method="post">
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <div>
                <h3 class="headline text-2xl font-extrabold">Solicitar saque</h3>
                <p class="mt-2 text-sm text-slate-500">Defina o valor e a chave de recebimento. O histórico completo fica logo abaixo.</p>
            </div>
            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" min="<?= e((string) $minWithdrawal) ?>" name="luacoins" placeholder="Quantidade em LuaCoins" required type="number" value="<?= e((string) $minWithdrawal) ?>">
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="payout_method">
                    <option value="pix" <?= (string) ($payoutProfile['method'] ?? 'pix') === 'pix' ? 'selected' : '' ?>>PIX</option>
                    <option value="bank" <?= (string) ($payoutProfile['method'] ?? '') === 'bank' ? 'selected' : '' ?>>Conta bancária</option>
                    <option value="wallet" <?= (string) ($payoutProfile['method'] ?? '') === 'wallet' ? 'selected' : '' ?>>Carteira digital</option>
                </select>
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 md:col-span-2" name="payout_key" placeholder="Chave PIX, banco ou conta" required type="text" value="<?= e((string) ($payoutProfile['key'] ?? '')) ?>">
                <textarea class="min-h-[118px] rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 md:col-span-2" name="note" placeholder="Observação para o financeiro">Saque solicitado pelo painel do criador.</textarea>
            </div>
            <div class="mt-6 flex-1"></div>
            <button class="signature-glow w-full rounded-full px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Enviar solicitação</button>
        </form>
    </div>

    <section class="mt-6 rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Extrato do criador</p>
                <h3 class="headline mt-2 text-2xl font-extrabold">Histórico financeiro</h3>
                <p class="mt-3 max-w-2xl text-sm text-slate-500">Filtre entradas e saídas e acompanhe o status de cada movimentação em tabela.</p>
            </div>
            <div class="rounded-2xl bg-[#f5f3f5] px-5 py-4 text-sm text-slate-600">
                <span class="headline text-2xl font-extrabold text-slate-900"><?= e((string) count($transactions)) ?></span>
                <span class="ml-2 font-semibold">lançamentos</span>
            </div>
        </div>

        <form action="/creator/wallet" class="mt-6 grid grid-cols-1 gap-4 rounded-3xl bg-[#f7f4f7] p-5 md:grid-cols-[minmax(0,1fr)_minmax(0,0.5fr)_auto_auto]" method="get">
            <input class="rounded-2xl border-none bg-white px-5 py-4" name="q" placeholder="Buscar transação..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
            <select class="rounded-2xl border-none bg-white px-5 py-4" name="type">
                <option value="">Todos os tipos</option>
                <option value="subscription" <?= (string) ($filters['type'] ?? '') === 'subscription' ? 'selected' : '' ?>>Assinaturas</option>
                <option value="tip" <?= (string) ($filters['type'] ?? '') === 'tip' ? 'selected' : '' ?>>Gorjetas</option>
                <option value="payout" <?= (string) ($filters['type'] ?? '') === 'payout' ? 'selected' : '' ?>>Saques</option>
                <option value="top_up" <?= (string) ($filters['type'] ?? '') === 'top_up' ? 'selected' : '' ?>>Recargas</option>
            </select>
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-white px-6 py-4 text-center text-sm font-bold text-slate-600" href="/creator/wallet">Reset</a>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-[#f0e8ee]">
                <thead>
                <tr class="text-left text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">
                    <th class="px-4 py-4">Movimento</th>
                    <th class="px-4 py-4">Tipo</th>
                    <th class="px-4 py-4">Método</th>
                    <th class="px-4 py-4">Data</th>
                    <th class="px-4 py-4 text-right">Valor</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e8ee]">
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                    $isIn = (string) ($transaction['direction'] ?? 'in') === 'in';
                    $type = (string) ($transaction['type'] ?? 'manual_adjustment');
                    ?>
                    <tr class="align-top">
                        <td class="px-4 py-4">
                            <p class="font-bold text-slate-900"><?= e((string) ($transaction['note'] ?? ($typeLabels[$type] ?? 'Movimentação'))) ?></p>
                            <?php if (trim((string) ($transaction['admin_note'] ?? '')) !== ''): ?>
                                <p class="mt-1 text-sm text-slate-500"><?= e((string) ($transaction['admin_note'] ?? '')) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4">
                            <span class="rounded-full bg-[#f7f4f7] px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-slate-600"><?= e($typeLabels[$type] ?? 'Movimentação') ?></span>
                        </td>
                        <td class="px-4 py-4 text-sm font-semibold text-slate-600"><?= e((string) ($transaction['payout_method'] ?? '—')) ?></td>
                        <td class="px-4 py-4 text-sm text-slate-500"><?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></td>
                        <td class="px-4 py-4 text-right">
                            <span class="headline text-lg font-extrabold <?= $isIn ? 'text-emerald-600' : 'text-rose-700' ?>">
                                <?= $isIn ? '+' : '-' ?><?= luacoin_amount_html((int) ($transaction['amount'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.85em] w-[0.85em] shrink-0') ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($transactions === []): ?>
                    <tr>
                        <td class="px-4 py-8 text-sm text-slate-500" colspan="5">Nenhuma transação encontrada com esse filtro.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
