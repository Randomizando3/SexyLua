<?php

declare(strict_types=1);

$user = $app->auth->user() ?? [];
$balance = (int) ($data['balance'] ?? 0);
$inflow = (int) ($data['inflow'] ?? 0);
$outflow = (int) ($data['outflow'] ?? 0);
$transactions = $data['filtered_transactions'] ?? $data['transactions'] ?? [];
$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$selectedTopUp = is_array($data['selected_topup'] ?? null) ? $data['selected_topup'] : null;
$platformSettings = $app->repository->settings();
$luacoinPrice = (float) ($platformSettings['luacoin_price_brl'] ?? 0.07);
$minimumDeposit = max(1, (int) ($platformSettings['deposit_min_luacoins'] ?? 100));
$syncPayEnabled = (bool) ($data['syncpay_enabled'] ?? false);
$siteBaseUrl = (string) ($platformSettings['site_base_url'] ?? app_base_url($app->config, $platformSettings));
$paymentStatus = (string) ($_GET['payment_status'] ?? '');
$selectedTopUpStatus = strtolower((string) ($selectedTopUp['status'] ?? ''));
$selectedTopUpPixCode = (string) ($selectedTopUp['pix_code'] ?? '');
if ($selectedTopUpPixCode === '' && $selectedTopUp !== null) {
    $pixCodeBase64 = trim((string) ($selectedTopUp['pix_code_base64'] ?? ''));
    $decodedPixCode = $pixCodeBase64 !== '' ? base64_decode($pixCodeBase64, true) : false;
    if (is_string($decodedPixCode) && trim($decodedPixCode) !== '') {
        $selectedTopUpPixCode = trim($decodedPixCode);
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Carteira do Assinante</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <?php if ($selectedTopUpPixCode !== ''): ?><script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script><?php endif; ?>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#ab1155",
                        background: "#fbf9fb",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f5f3f5",
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044",
                    },
                    fontFamily: {
                        headline: ["Plus Jakarta Sans"],
                        body: ["Manrope"],
                    },
                    borderRadius: {
                        DEFAULT: "1rem",
                        lg: "2rem",
                        xl: "3rem",
                        full: "9999px",
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        body { background: #fbf9fb; color: #1b1c1d; font-family: "Manrope", sans-serif; }
        h1, h2, h3, h4 { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%); }
    </style>
</head>
<body class="min-h-screen">
<?php
$subscriberTopbarUser = $user;
$subscriberTopbarAction = ['href' => '/subscriber/subscriptions', 'label' => 'Assinaturas'];
require BASE_PATH . '/templates/partials/subscriber_topbar.php';
$subscriberSidebarCurrent = 'wallet';
require BASE_PATH . '/templates/partials/subscriber_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">LuaCoins e historico</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Carteira do <span class="italic text-primary">Assinante</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Recarregue LuaCoins, acompanhe gastos com assinaturas e tenha visibilidade completa da sua movimentacao.</p>
        </div>
        <div class="signature-glow rounded-3xl px-6 py-5 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Saldo disponivel</p>
            <div class="mt-3 text-4xl font-extrabold"><?= luacoin_amount_html($balance, 'inline-flex items-center gap-3 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Entradas</p><div class="mt-3 text-3xl font-extrabold text-emerald-600"><?= luacoin_amount_html($inflow, 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Saidas</p><div class="mt-3 text-3xl font-extrabold text-rose-700"><?= luacoin_amount_html($outflow, 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Recargas</p><div class="mt-3 text-3xl font-extrabold text-primary"><?= luacoin_amount_html((int) ($summary['top_up_total'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinaturas</p><div class="mt-3 text-3xl font-extrabold text-primary"><?= luacoin_amount_html((int) ($summary['subscription_spend'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
    </section>

    <?php if ($paymentStatus !== ''): ?>
        <section class="mb-8 rounded-3xl <?= $paymentStatus === 'success' ? 'bg-emerald-50 text-emerald-900' : ($paymentStatus === 'pending' ? 'bg-amber-50 text-amber-900' : 'bg-rose-50 text-rose-900') ?> p-5 shadow-sm">
            <p class="text-sm font-bold">
                <?= e($paymentStatus === 'success' ? 'Pagamento SyncPay confirmado. As LuaCoins devem entrar na sua carteira em instantes.' : ($paymentStatus === 'pending' ? 'PIX criado com sucesso. Assim que a SyncPay confirmar o pagamento, as LuaCoins entram na carteira.' : 'Nao foi possivel gerar ou concluir o pagamento. Confira os dados e tente novamente.')) ?>
            </p>
        </section>
    <?php endif; ?>

    <?php if ($selectedTopUp !== null): ?>
        <section class="mb-8 rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Recarga PIX</p>
                    <h3 class="mt-3 text-3xl font-extrabold">Pagamento via SyncPay</h3>
                    <p class="mt-2 max-w-2xl text-sm text-on-surface-variant">Use o codigo PIX abaixo para pagar. A liberacao das LuaCoins acontece somente quando a SyncPay confirmar o pagamento pelo sistema.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <span class="rounded-full px-4 py-2 text-xs font-bold uppercase tracking-[0.25em] <?= $selectedTopUpStatus === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($selectedTopUpStatus === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') ?>">
                        <?= e($selectedTopUpStatus === 'approved' ? 'Aprovado' : ($selectedTopUpStatus === 'pending' ? 'Aguardando pagamento' : ucfirst($selectedTopUpStatus))) ?>
                    </span>
                    <?php if ($selectedTopUpStatus === 'pending'): ?>
                        <a class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" href="<?= e(path_with_query('/subscriber/wallet', ['topup' => (int) ($selectedTopUp['id'] ?? 0), 'refresh' => 1])) ?>">Atualizar status</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <article class="rounded-3xl bg-surface-container-low p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">LuaCoins</p>
                    <div class="mt-3 text-3xl font-extrabold text-primary"><?= luacoin_amount_html((int) ($selectedTopUp['amount'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div>
                </article>
                <article class="rounded-3xl bg-surface-container-low p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Valor em BRL</p>
                    <p class="mt-3 text-3xl font-extrabold text-primary"><?= e(brl_amount((float) ($selectedTopUp['amount_brl_expected'] ?? 0))) ?></p>
                </article>
                <article class="rounded-3xl bg-surface-container-low p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Referencia</p>
                    <p class="mt-3 break-all text-sm font-bold text-on-surface"><?= e((string) ($selectedTopUp['external_reference'] ?? '')) ?></p>
                </article>
            </div>

            <div class="mt-6 rounded-3xl bg-surface-container-low p-6">
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <div>
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Codigo PIX copia e cola</p>
                                <p class="mt-2 text-sm text-on-surface-variant">Cole no banco, app ou carteira de pagamento para concluir a recarga.</p>
                            </div>
                            <?php if ($selectedTopUpPixCode !== ''): ?>
                                <button class="rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-700" data-copy-target="syncpay-pix-code" type="button">Copiar codigo</button>
                            <?php endif; ?>
                        </div>

                        <?php if ($selectedTopUpPixCode !== ''): ?>
                            <textarea class="mt-4 min-h-36 w-full rounded-3xl border-none bg-white px-5 py-4 text-sm text-slate-700 shadow-sm focus:ring-2 focus:ring-primary/20" id="syncpay-pix-code" readonly><?= e($selectedTopUpPixCode) ?></textarea>
                            <p class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">Enquanto o status estiver pendente, essas LuaCoins ainda nao entram no saldo. O credito so acontece apos a confirmacao da SyncPay.</p>
                        <?php else: ?>
                            <p class="mt-4 rounded-2xl bg-white px-5 py-4 text-sm text-on-surface-variant">O codigo PIX ainda nao foi retornado pela SyncPay. Atualize o status em alguns segundos.</p>
                        <?php endif; ?>
                    </div>

                    <div class="rounded-3xl bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">QR Code PIX</p>
                        <?php if ($selectedTopUpPixCode !== ''): ?>
                            <div class="mt-4 flex items-center justify-center rounded-3xl bg-surface-container-low p-4">
                                <div class="h-[220px] w-[220px]" data-syncpay-qrcode data-syncpay-pix="<?= e($selectedTopUpPixCode) ?>"></div>
                            </div>
                            <p class="mt-4 text-sm text-on-surface-variant">Abra o app do seu banco e escaneie o QR Code para pagar a recarga.</p>
                        <?php else: ?>
                            <div class="mt-4 flex min-h-[220px] items-center justify-center rounded-3xl bg-surface-container-low p-4 text-center text-sm text-on-surface-variant">
                                O QR Code vai aparecer aqui assim que a SyncPay retornar o PIX.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[0.8fr_1.2fr]">
        <section class="space-y-6">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <h3 class="text-2xl font-extrabold">Recarregar LuaCoins</h3>
                <form action="/subscriber/wallet/add-funds" class="mt-6 space-y-4" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="<?= e((string) $minimumDeposit) ?>" name="luacoins" placeholder="Quantidade em LuaCoins" required type="number" value="<?= e((string) $minimumDeposit) ?>">
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" maxlength="18" name="cpf" placeholder="CPF ou CNPJ do pagador" required type="text" value="">
                    <p class="text-sm text-on-surface-variant">Minimo atual: <?= luacoin_amount_html($minimumDeposit, 'inline-flex items-center gap-1.5 whitespace-nowrap font-bold text-primary', '', 'h-4 w-4 shrink-0') ?>. Valor estimado: <?= e(brl_amount($minimumDeposit * $luacoinPrice)) ?> para a menor recarga permitida.</p>
                    <?php if (! $syncPayEnabled): ?>
                        <p class="rounded-2xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Configure a SyncPay no admin para liberar a geracao de PIX nesta carteira.</p>
                    <?php elseif ($siteBaseUrl === ''): ?>
                        <p class="rounded-2xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Preencha a Site URL no admin para deixar o webhook de recarga pronto quando publicar a plataforma.</p>
                    <?php endif; ?>
                    <button class="signature-glow w-full rounded-full px-5 py-4 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" data-prototype-skip="1" type="submit" <?= ! $syncPayEnabled ? 'disabled' : '' ?>>Gerar PIX na SyncPay</button>
                </form>
            </div>
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <h3 class="text-2xl font-extrabold">Resumo de uso</h3>
                <div class="mt-5 space-y-4 text-sm">
                    <div class="rounded-3xl bg-surface-container-low p-5"><p class="text-on-surface-variant">Gasto com assinaturas</p><div class="mt-2 text-xl font-bold"><?= luacoin_amount_html((int) ($summary['subscription_spend'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div></div>
                    <div class="rounded-3xl bg-surface-container-low p-5"><p class="text-on-surface-variant">Gasto com gorjetas</p><div class="mt-2 text-xl font-bold"><?= luacoin_amount_html((int) ($summary['tip_spend'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div></div>
                    <div class="rounded-3xl bg-surface-container-low p-5"><p class="text-on-surface-variant">Gasto com microconteudos</p><div class="mt-2 text-xl font-bold"><?= luacoin_amount_html((int) ($summary['instant_content_spend'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div></div>
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <form action="/subscriber/wallet" class="grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-lowest p-6 shadow-sm xl:grid-cols-[1fr_0.45fr_auto]" method="get">
                <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar transacao..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="type">
                    <option value="">Todos os tipos</option>
                    <option value="subscription" <?= (string) ($filters['type'] ?? '') === 'subscription' ? 'selected' : '' ?>>Assinaturas</option>
                    <option value="tip" <?= (string) ($filters['type'] ?? '') === 'tip' ? 'selected' : '' ?>>Gorjetas</option>
                    <option value="top_up" <?= (string) ($filters['type'] ?? '') === 'top_up' ? 'selected' : '' ?>>Recargas</option>
                </select>
                <div class="flex items-center gap-3">
                    <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                    <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/subscriber/wallet">Reset</a>
                </div>
            </form>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <h3 class="text-2xl font-extrabold">Historico financeiro</h3>
                <div class="mt-6 space-y-4">
                    <?php foreach ($transactions as $transaction): ?>
                        <?php
                        $isIn = (string) ($transaction['direction'] ?? 'in') === 'in';
                        $countsForBalance = (bool) ($transaction['counts_for_balance'] ?? false);
                        $transactionStatus = strtolower((string) ($transaction['status'] ?? 'completed'));
                        $statusLabel = match ($transactionStatus) {
                            'approved', 'completed', 'paid' => 'Aprovado',
                            'pending', 'processing', 'created' => 'Pendente',
                            'failed', 'rejected', 'cancelled', 'canceled', 'expired' => 'Falhou',
                            default => ucfirst($transactionStatus),
                        };
                        $statusClass = match ($transactionStatus) {
                            'approved', 'completed', 'paid' => 'bg-emerald-100 text-emerald-700',
                            'pending', 'processing', 'created' => 'bg-amber-100 text-amber-700',
                            'failed', 'rejected', 'cancelled', 'canceled', 'expired' => 'bg-rose-100 text-rose-700',
                            default => 'bg-slate-200 text-slate-700',
                        };
                        $amountClass = $countsForBalance
                            ? ($isIn ? 'text-emerald-600' : 'text-rose-700')
                            : 'text-slate-500';
                        $amountPrefix = $countsForBalance
                            ? ($isIn ? '+' : '-')
                            : '';
                        ?>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-bold"><?= e((string) ($transaction['note'] ?? 'Movimentacao')) ?></p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['type'] ?? 'mov')) ?> • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] <?= $statusClass ?>"><?= e($statusLabel) ?></span>
                                    <strong class="mt-2 block <?= $amountClass ?>"><?= e($amountPrefix) ?><?= luacoin_amount_html((int) ($transaction['amount'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.85em] w-[0.85em] shrink-0') ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($transactions === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma transacao encontrada nesse filtro.</p><?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>
<script>
    document.querySelectorAll('[data-copy-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('data-copy-target');
            var target = targetId ? document.getElementById(targetId) : null;
            if (!target) {
                return;
            }

            target.select();
            target.setSelectionRange(0, target.value.length);

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(target.value).catch(function () {});
            } else {
                document.execCommand('copy');
            }

            button.textContent = 'Codigo copiado';
            window.setTimeout(function () {
                button.textContent = 'Copiar codigo';
            }, 1800);
        });
    });

    document.querySelectorAll('[data-syncpay-qrcode]').forEach(function (element) {
        var pixCode = element.getAttribute('data-syncpay-pix') || '';
        if (!pixCode || typeof QRCode === 'undefined') {
            return;
        }

        element.innerHTML = '';
        new QRCode(element, {
            text: pixCode,
            width: 220,
            height: 220,
            colorDark: '#1b1c1d',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    });

    <?php if ($selectedTopUp !== null && $selectedTopUpStatus === 'pending'): ?>
    (function () {
        var refreshed = false;
        window.setInterval(function () {
            if (refreshed || document.hidden) {
                return;
            }

            refreshed = true;
            window.location.href = <?= json_encode(path_with_query('/subscriber/wallet', ['topup' => (int) ($selectedTopUp['id'] ?? 0), 'refresh' => 1])) ?>;
        }, 12000);
    })();
    <?php endif; ?>
</script>
</body>
</html>
