<?php

declare(strict_types=1);

$liveToolsContext = (string) ($liveToolsContext ?? 'manager');
$liveToolsIngestServer = trim((string) ($liveToolsIngestServer ?? ''));
$liveToolsStreamKey = trim((string) ($liveToolsStreamKey ?? ''));
$liveToolsHasCredentials = $liveToolsIngestServer !== '' && $liveToolsStreamKey !== '';
$liveToolsGeneralHint = $liveToolsContext === 'studio'
    ? 'Escolha um app gratuito, copie os dados desta live e siga o passo a passo abaixo.'
    : 'Escolha um app gratuito, crie ou abra uma live no estúdio e depois copie o servidor RTMP e a chave da transmissão.';

$liveToolsPlatforms = [
    'windows' => [
        'label' => 'Windows',
        'icon' => 'desktop_windows',
        'headline' => 'Apps gratuitos para transmitir no Windows',
        'description' => 'Se você vai transmitir do notebook ou PC, estas são as opções mais estáveis para lives no SexyLua.',
        'apps' => [
            [
                'name' => 'OBS Studio',
                'icon' => 'videocam',
                'download_label' => 'Baixar OBS Studio',
                'download_url' => 'https://obsproject.com/pt-br/download',
                'summary' => 'Ótimo para webcam, capturas de tela, cenas e áudio com mais controle.',
                'steps' => [
                    'Instale o OBS Studio e abra Configurações > Transmissão.',
                    'Em Serviço, escolha “Personalizado”.',
                    'Cole o servidor RTMP e a chave do SexyLua.',
                    'Em Saída/Vídeo, ajuste para 854x480, 800 kbps, 30 fps, keyframe 2s e áudio AAC em 96 kbps.',
                    'Clique em Iniciar transmissão e volte ao estúdio para conferir o preview.',
                ],
            ],
            [
                'name' => 'PRISM Live Studio',
                'icon' => 'cast',
                'download_label' => 'Baixar PRISM Live Studio',
                'download_url' => 'https://prismlive.com/en_us/pcapp/',
                'summary' => 'Mais simples para começar rápido, com câmera, chat e overlays básicos.',
                'steps' => [
                    'Instale o PRISM Live Studio e abra a área de transmissão.',
                    'Escolha a opção de RTMP personalizado.',
                    'Cole o servidor RTMP e a chave da live.',
                    'Use a qualidade 480p e 30 fps para manter a transmissão leve e estável.',
                    'Inicie a live e confira no estúdio se o sinal chegou corretamente.',
                ],
            ],
        ],
    ],
    'android' => [
        'label' => 'Android',
        'icon' => 'android',
        'headline' => 'Apps gratuitos para transmitir no Android',
        'description' => 'Ideal para lives pelo celular com câmera traseira, frontal ou rede móvel.',
        'apps' => [
            [
                'name' => 'PRISM Live Studio',
                'icon' => 'smartphone',
                'download_label' => 'Abrir no Google Play',
                'download_url' => 'https://play.google.com/store/apps/details?id=com.prism.live',
                'summary' => 'Interface simples e rápida para entrar ao vivo pelo celular.',
                'steps' => [
                    'Instale o PRISM Live Studio no Android.',
                    'Abra a opção de transmissão RTMP personalizada.',
                    'Cole o servidor RTMP e a chave da live do SexyLua.',
                    'Ajuste para 480p e 30 fps quando a opção aparecer.',
                    'Inicie a transmissão e acompanhe o preview no estúdio.',
                ],
            ],
            [
                'name' => 'Larix Broadcaster',
                'icon' => 'cell_tower',
                'download_label' => 'Abrir Larix no Google Play',
                'download_url' => 'https://play.google.com/store/apps/details?id=com.wmspanel.larix_broadcaster',
                'summary' => 'Muito bom para RTMP puro, com conexão estável e configuração direta.',
                'steps' => [
                    'Instale o Larix Broadcaster no Android.',
                    'Entre em Connections > New connection.',
                    'Cole o servidor RTMP e a chave do SexyLua.',
                    'Defina 854x480, 800 kbps e 30 fps.',
                    'Salve, volte para a tela inicial e toque em Start.',
                ],
            ],
        ],
    ],
    'ios' => [
        'label' => 'iPhone / iPad',
        'icon' => 'phone_iphone',
        'headline' => 'Apps gratuitos para transmitir no iPhone e iPad',
        'description' => 'Boa opção para lives com câmera móvel, usando Wi-Fi ou rede de dados.',
        'apps' => [
            [
                'name' => 'PRISM Live Studio',
                'icon' => 'phone_iphone',
                'download_label' => 'Abrir na App Store',
                'download_url' => 'https://apps.apple.com/br/app/prism-live-streaming-app/id1319056339',
                'summary' => 'App simples para iniciar transmissão RTMP com poucos toques.',
                'steps' => [
                    'Instale o PRISM Live Studio no iPhone ou iPad.',
                    'Abra a criação de live e escolha RTMP personalizado.',
                    'Cole o servidor RTMP e a chave da live.',
                    'Use qualidade 480p / 30 fps para mais estabilidade.',
                    'Comece a transmissão e confirme o sinal no estúdio.',
                ],
            ],
            [
                'name' => 'Larix Broadcaster',
                'icon' => 'cell_tower',
                'download_label' => 'Abrir Larix na App Store',
                'download_url' => 'https://apps.apple.com/app/larix-broadcaster/id1042474385',
                'summary' => 'Excelente para RTMP direto, com configuração manual e fluxo mais técnico.',
                'steps' => [
                    'Instale o Larix Broadcaster no iOS.',
                    'Abra Connections > New connection.',
                    'Cole o servidor RTMP e a chave do SexyLua.',
                    'Ajuste vídeo para 854x480, 800 kbps e 30 fps.',
                    'Volte à câmera principal e toque em Start.',
                ],
            ],
        ],
    ],
];
?>
<div class="fixed inset-0 z-[90] hidden items-start justify-center overflow-y-auto bg-slate-950/55 px-4 py-8" data-live-tools-modal>
    <div class="relative w-full max-w-6xl rounded-[2rem] bg-white p-6 shadow-[0px_30px_80px_rgba(27,28,29,0.2)] sm:p-8">
        <button aria-label="Fechar ferramentas" class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#f5f3f5] text-slate-500 transition hover:bg-[#ede7ec]" data-live-tools-close type="button">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="max-w-4xl pr-12">
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Ferramentas para transmitir</p>
            <h3 class="headline mt-3 text-3xl font-extrabold">Escolha seu dispositivo e siga o passo a passo</h3>
            <p class="mt-3 text-sm leading-7 text-slate-500"><?= e($liveToolsGeneralHint) ?></p>
        </div>

        <?php if ($liveToolsHasCredentials): ?>
            <div class="mt-6 rounded-[1.75rem] bg-[#f8f4f7] p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#D81B60]">Dados desta live</p>
                        <p class="mt-2 text-sm text-slate-500">Toque nos campos para copiar e colar no app escolhido.</p>
                    </div>
                    <div class="grid min-w-0 flex-1 grid-cols-1 gap-3 md:grid-cols-2">
                        <button class="relative rounded-2xl bg-white p-4 text-left shadow-sm transition hover:bg-[#fdfbfc]" data-live-tools-copy="<?= e($liveToolsIngestServer) ?>" data-live-tools-copy-label="Servidor copiado" type="button">
                            <span class="absolute right-4 top-4 hidden rounded-full bg-slate-900 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-white" data-live-tools-copy-status>Copiado</span>
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Servidor RTMP</p>
                            <p class="mt-2 break-all text-sm font-bold text-slate-700"><?= e($liveToolsIngestServer) ?></p>
                        </button>
                        <button class="relative rounded-2xl bg-white p-4 text-left shadow-sm transition hover:bg-[#fdfbfc]" data-live-tools-copy="<?= e($liveToolsStreamKey) ?>" data-live-tools-copy-label="Chave copiada" type="button">
                            <span class="absolute right-4 top-4 hidden rounded-full bg-slate-900 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-white" data-live-tools-copy-status>Copiado</span>
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Chave da transmissão</p>
                            <p class="mt-2 break-all text-sm font-bold text-slate-700"><?= e($liveToolsStreamKey) ?></p>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-6 flex flex-wrap gap-3">
            <?php $liveToolsTabIndex = 0; ?>
            <?php foreach ($liveToolsPlatforms as $platformKey => $platform): ?>
                <button
                    class="<?= $liveToolsTabIndex === 0 ? 'signature-glow text-white shadow-[0px_18px_36px_rgba(171,17,85,0.2)]' : 'bg-[#f5f3f5] text-slate-600' ?> inline-flex h-12 items-center gap-2 rounded-full px-5 text-sm font-bold transition"
                    data-live-tools-tab="<?= e($platformKey) ?>"
                    type="button"
                >
                    <span class="material-symbols-outlined text-[20px]"><?= e((string) $platform['icon']) ?></span>
                    <?= e((string) $platform['label']) ?>
                </button>
                <?php $liveToolsTabIndex++; ?>
            <?php endforeach; ?>
        </div>

        <div class="mt-6">
            <?php $liveToolsPanelIndex = 0; ?>
            <?php foreach ($liveToolsPlatforms as $platformKey => $platform): ?>
                <section class="<?= $liveToolsPanelIndex === 0 ? '' : 'hidden ' ?>space-y-5" data-live-tools-panel="<?= e($platformKey) ?>">
                    <div class="rounded-[1.75rem] bg-[#fbf9fb] p-5">
                        <h4 class="headline text-2xl font-extrabold"><?= e((string) $platform['headline']) ?></h4>
                        <p class="mt-3 text-sm leading-7 text-slate-500"><?= e((string) $platform['description']) ?></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <?php foreach ((array) ($platform['apps'] ?? []) as $appTool): ?>
                            <article class="rounded-[1.75rem] bg-[#f8f4f7] p-5">
                                <div class="flex items-start gap-4">
                                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-[#D81B60] shadow-sm">
                                        <span class="material-symbols-outlined text-[26px]"><?= e((string) ($appTool['icon'] ?? 'apps')) ?></span>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <h5 class="text-lg font-extrabold text-slate-900"><?= e((string) ($appTool['name'] ?? 'App')) ?></h5>
                                                <p class="mt-2 text-sm leading-6 text-slate-500"><?= e((string) ($appTool['summary'] ?? '')) ?></p>
                                            </div>
                                            <a class="inline-flex items-center justify-center rounded-full bg-white px-4 py-2 text-sm font-bold text-[#D81B60] shadow-sm" href="<?= e((string) ($appTool['download_url'] ?? '#')) ?>" rel="noreferrer noopener" target="_blank">
                                                <?= e((string) ($appTool['download_label'] ?? 'Baixar')) ?>
                                            </a>
                                        </div>
                                        <ol class="mt-5 space-y-3 text-sm text-slate-600">
                                            <?php foreach ((array) ($appTool['steps'] ?? []) as $stepIndex => $stepText): ?>
                                                <li class="flex items-start gap-3">
                                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-[11px] font-bold text-[#D81B60] shadow-sm"><?= e((string) ($stepIndex + 1)) ?></span>
                                                    <span class="leading-6"><?= e((string) $stepText) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php $liveToolsPanelIndex++; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    (() => {
        const modal = document.querySelector('[data-live-tools-modal]');
        if (!modal) {
            return;
        }

        const body = document.body;
        const tabs = Array.from(modal.querySelectorAll('[data-live-tools-tab]'));
        const panels = Array.from(modal.querySelectorAll('[data-live-tools-panel]'));
        const openButtons = Array.from(document.querySelectorAll('[data-live-tools-open]'));
        const closeButtons = Array.from(modal.querySelectorAll('[data-live-tools-close]'));

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            body.classList.remove('overflow-hidden');
        };

        const activateTab = (platform) => {
            tabs.forEach((tab) => {
                const active = tab.dataset.liveToolsTab === platform;
                tab.classList.toggle('signature-glow', active);
                tab.classList.toggle('text-white', active);
                tab.classList.toggle('shadow-[0px_18px_36px_rgba(171,17,85,0.2)]', active);
                tab.classList.toggle('bg-[#f5f3f5]', !active);
                tab.classList.toggle('text-slate-600', !active);
            });

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.liveToolsPanel !== platform);
            });
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', openModal);
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activateTab(tab.dataset.liveToolsTab || 'windows');
            });
        });

        modal.querySelectorAll('[data-live-tools-copy]').forEach((button) => {
            button.addEventListener('click', async () => {
                const text = button.getAttribute('data-live-tools-copy') || '';
                if (!text) {
                    return;
                }

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

                const badge = button.querySelector('[data-live-tools-copy-status]');
                if (!(badge instanceof HTMLElement)) {
                    return;
                }

                badge.textContent = button.getAttribute('data-live-tools-copy-label') || 'Copiado';
                badge.classList.remove('hidden');
                window.clearTimeout(button.__liveToolsCopyTimer);
                button.__liveToolsCopyTimer = window.setTimeout(() => {
                    badge.classList.add('hidden');
                }, 1800);
            });
        });
    })();
</script>
