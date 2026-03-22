<?php

declare(strict_types=1);

$creator = $data['creator'] ?? $app->repository->findCreatorBySlugOrId(null, (int) ($app->auth->id() ?? 0)) ?? [];
?>
<!DOCTYPE html>

<html lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>SexyLua - Painel do Criador</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "error": "#ba1a1a",
                        "background": "#fbf9fb",
                        "on-primary-container": "#fff2f4",
                        "on-background": "#1b1c1d",
                        "inverse-surface": "#303032",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f5f3f5",
                        "on-tertiary-fixed-variant": "#5a2a9c",
                        "on-secondary-fixed": "#3f001b",
                        "surface-bright": "#fbf9fb",
                        "on-tertiary": "#ffffff",
                        "on-secondary-fixed-variant": "#8b0e45",
                        "on-error-container": "#93000a",
                        "surface-tint": "#b41b5c",
                        "surface": "#fbf9fb",
                        "surface-variant": "#e3e2e4",
                        "tertiary-fixed": "#eddcff",
                        "outline": "#8e6f74",
                        "tertiary-fixed-dim": "#d7baff",
                        "surface-container-high": "#e9e7e9",
                        "secondary-fixed-dim": "#ffb1c5",
                        "primary": "#ab1155",
                        "secondary-fixed": "#ffd9e1",
                        "on-tertiary-fixed": "#280056",
                        "on-surface-variant": "#5a4044",
                        "on-primary-fixed-variant": "#8f0045",
                        "secondary-container": "#fd6c9c",
                        "surface-container-highest": "#e3e2e4",
                        "error-container": "#ffdad6",
                        "primary-fixed": "#ffd9e1",
                        "inverse-on-surface": "#f2f0f2",
                        "on-tertiary-container": "#fcf3ff",
                        "on-primary": "#ffffff",
                        "on-secondary": "#ffffff",
                        "surface-dim": "#dbd9db",
                        "primary-container": "#cc326e",
                        "secondary": "#ab2c5d",
                        "inverse-primary": "#ffb1c5",
                        "tertiary": "#6c3eaf",
                        "outline-variant": "#e3bdc3",
                        "on-surface": "#1b1c1d",
                        "on-error": "#ffffff",
                        "tertiary-container": "#8658ca",
                        "on-secondary-container": "#6e0034",
                        "on-primary-fixed": "#3f001a",
                        "surface-container": "#efedef",
                        "primary-fixed-dim": "#ffb1c5"
                    },
                    fontFamily: {
                        "headline": ["Plus Jakarta Sans"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    },
                    borderRadius: {"DEFAULT": "1rem", "lg": "2rem", "xl": "3rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Manrope', sans-serif; }
        h1, h2, h3 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .lunar-glass {
            background: rgba(251, 249, 251, 0.7);
            backdrop-filter: blur(24px);
        }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen">
<?php
$creatorShellCreator = $creator;
$creatorShellCurrent = 'dashboard';
$creatorTopbarLabel = 'Metricas Lunares';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Configurar Live'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>
<!-- Main Content -->
<main class="min-h-screen pt-20 lg:pl-64">
<div class="max-w-7xl mx-auto px-8 py-12">
<!-- Header Section -->
<header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
<div>
<h2 class="text-4xl font-extrabold text-on-background tracking-tight mb-2">OlÃ¡, Ana Silva ðŸ‘‹</h2>
<p class="text-on-surface-variant font-medium">Sua influÃªncia celestial estÃ¡ crescendo. Veja o que mudou hoje.</p>
</div>
<div class="flex items-center gap-3">
<button class="bg-surface-container-highest px-6 py-3 rounded-full font-bold text-sm hover:scale-105 transition-transform active:opacity-80">
                        Baixar RelatÃ³rio
                    </button>
<button class="bg-primary text-on-primary px-8 py-3 rounded-full font-bold text-sm shadow-lg shadow-primary/20 hover:scale-105 transition-transform active:opacity-80 flex items-center gap-2">
<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">sensors</span>
                        Iniciar Live
                    </button>
</div>
</header>
<!-- Bento Grid Layout -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-8">
<!-- Ganhos da Lua (Earnings) -->
<section class="md:col-span-8 bg-surface-container-lowest rounded-xl p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
<div class="flex items-center justify-between mb-8">
<div>
<h3 class="text-xl font-bold mb-1">Ganhos da Lua</h3>
<p class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold">Rendimento Mensal</p>
</div>
<div class="text-right">
<span class="text-3xl font-black text-primary tracking-tighter">R$ 14.850,00</span>
<p class="text-xs text-emerald-600 font-bold flex items-center justify-end gap-1">
<span class="material-symbols-outlined text-xs">trending_up</span> +12% vs mÃªs anterior
                            </p>
</div>
</div>
<!-- Abstract Chart Representation -->
<div class="h-64 flex items-end justify-between gap-2 px-2">
<div class="w-full bg-surface-container-low rounded-t-full h-1/2 relative group hover:bg-primary-container/20 transition-colors">
<div class="absolute bottom-0 w-full bg-primary-container rounded-t-full h-1/3 opacity-40"></div>
</div>
<div class="w-full bg-surface-container-low rounded-t-full h-3/4 relative group hover:bg-primary-container/20 transition-colors">
<div class="absolute bottom-0 w-full bg-primary-container rounded-t-full h-2/3 opacity-40"></div>
</div>
<div class="w-full bg-surface-container-low rounded-t-full h-2/3 relative group hover:bg-primary-container/20 transition-colors">
<div class="absolute bottom-0 w-full bg-primary-container rounded-t-full h-1/2 opacity-40"></div>
</div>
<div class="w-full bg-surface-container-low rounded-t-full h-5/6 relative group hover:bg-primary-container/20 transition-colors">
<div class="absolute bottom-0 w-full bg-primary rounded-t-full h-3/4 shadow-[0_0_20px_rgba(171,17,85,0.3)]"></div>
</div>
<div class="w-full bg-surface-container-low rounded-t-full h-3/5 relative group hover:bg-primary-container/20 transition-colors">
<div class="absolute bottom-0 w-full bg-primary-container rounded-t-full h-2/5 opacity-40"></div>
</div>
<div class="w-full bg-surface-container-low rounded-t-full h-full relative group hover:bg-primary-container/20 transition-colors">
<div class="absolute bottom-0 w-full bg-primary-container rounded-t-full h-4/5 opacity-40"></div>
</div>
<div class="w-full bg-surface-container-low rounded-t-full h-4/6 relative group hover:bg-primary-container/20 transition-colors">
<div class="absolute bottom-0 w-full bg-primary-container rounded-t-full h-1/3 opacity-40"></div>
</div>
</div>
<div class="flex justify-between mt-4 px-2 text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">
<span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sab</span><span>Dom</span>
</div>
</section>
<!-- MÃ©tricas Estelares (Viewers/Followers) -->
<section class="md:col-span-4 space-y-8">
<div class="bg-primary text-on-primary rounded-xl p-8 shadow-xl relative overflow-hidden">
<!-- Moon Motif Ornament -->
<div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
<div class="relative z-10">
<span class="material-symbols-outlined text-3xl mb-4">stars</span>
<h3 class="text-lg font-bold opacity-80 mb-4">Seguidores Estelares</h3>
<div class="flex items-end gap-3">
<span class="text-5xl font-black tracking-tighter">84.2K</span>
<span class="text-xs bg-white/20 px-2 py-1 rounded-full mb-2">+2.4k</span>
</div>
</div>
</div>
<div class="bg-surface-container-low rounded-xl p-8">
<h3 class="text-sm font-bold text-on-surface-variant uppercase tracking-widest mb-6">MÃ©tricas de Engajamento</h3>
<div class="space-y-6">
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-surface-container-lowest flex items-center justify-center">
<span class="material-symbols-outlined text-primary text-xl">visibility</span>
</div>
<span class="text-sm font-bold">VisualizaÃ§Ãµes</span>
</div>
<span class="font-black text-lg">128k</span>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-surface-container-lowest flex items-center justify-center">
<span class="material-symbols-outlined text-primary text-xl">favorite</span>
</div>
<span class="text-sm font-bold">Curtidas</span>
</div>
<span class="font-black text-lg">42.5k</span>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-surface-container-lowest flex items-center justify-center">
<span class="material-symbols-outlined text-primary text-xl">chat_bubble</span>
</div>
<span class="text-sm font-bold">ComentÃ¡rios</span>
</div>
<span class="font-black text-lg">8.9k</span>
</div>
</div>
</div>
</section>
<!-- Configurar Live -->
<section class="md:col-span-5 bg-surface-container-low rounded-xl p-8">
<div class="flex items-center justify-between mb-8">
<h3 class="text-xl font-bold">Configurar Live</h3>
<span class="material-symbols-outlined text-pink-600">settings</span>
</div>
<form class="space-y-6">
<div>
<label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">TÃ­tulo da TransmissÃ£o</label>
<input class="w-full bg-surface-container-lowest border-none rounded-md py-4 px-4 focus:ring-1 focus:ring-primary focus:outline-none transition-all" placeholder="Ex: Noite de Gala no Luau" type="text"/>
</div>
<div>
<label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">PreÃ§o de Acesso (Moedas Lunares)</label>
<div class="relative">
<input class="w-full bg-surface-container-lowest border-none rounded-md py-4 px-4 focus:ring-1 focus:ring-primary focus:outline-none transition-all" placeholder="150" type="number"/>
<span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary">toll</span>
</div>
</div>
<div class="pt-4">
<button class="w-full py-4 bg-primary-container text-on-primary font-bold rounded-full hover:scale-102 transition-transform shadow-md" type="button">Salvar PrÃ©-definiÃ§Ã£o</button>
</div>
</form>
</section>
<!-- Meus ConteÃºdos (Horizontal Scroll-like Bento) -->
<section class="md:col-span-7 bg-surface-container-lowest rounded-xl p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.04)]">
<div class="flex items-center justify-between mb-8">
<h3 class="text-xl font-bold">Meus ConteÃºdos</h3>
<a class="text-primary text-sm font-bold hover:underline" href="#">Ver tudo</a>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
<div class="group cursor-pointer">
<div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-3">
<img alt="Criadora de moda posando com iluminaÃ§Ã£o rosa" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD9b0sipK2Ibp_t_8EHvXGGXneYrdYmJza9kLn1JcpriOEu5AgR3FdDQed57q4QIhIw3gaaHqyq_q3c_Pkgnpu8RRAkiCPwo9zsuevqz8s7h_eQmbUAuisAcWPSnAHuHIrV0whulFm7Pp-77FwKEyckJXS0oBFzRlEzYzJjL1VaHiA05o3h-N5_IyDpzp4cCKSrEB--1H1RX22KZxP9K3Ld6XntqhJOFB4hmwyaYYdP8UPUuwcK1gGMRiIvKkGPKslzgIdH75j2gpY"/>
<div class="absolute top-3 left-3 bg-primary px-3 py-1 rounded-full text-[10px] font-bold text-white">R$ 50</div>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
<span class="text-white text-xs font-bold">Editar MÃ­dia</span>
</div>
</div>
<p class="text-sm font-bold truncate">Editorial Outono</p>
<p class="text-[10px] text-on-surface-variant font-bold uppercase">2.4k VisualizaÃ§Ãµes</p>
</div>
<div class="group cursor-pointer">
<div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-3">
<img alt="Retrato do criador com iluminaÃ§Ã£o artÃ­stica" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCJu3x9OeL94sVLi94VxE1794kznQc89xOfHHMkcngI5DULr7j2K9FEbkJDxbMTXyF11bGXXFUzblgOLKf8MUE5tcS-T_LJQSJW53r9SstFKSr3Bn6dodKEefpKgF9hpcAx_T6rhhImtEpV-303yYBBmtOt635L4AHhfmjsjhVNSEett2iFSAncTYm0BDODZjJrsnDClJOQ7jo5YMoeJRvaK1ANKTrqsGAmE7iW9DXJOnCn3_dvGPscUWNa79i9JlgaCHr1TyUNL5E"/>
<div class="absolute top-3 left-3 bg-zinc-800 px-3 py-1 rounded-full text-[10px] font-bold text-white">GrÃ¡tis</div>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
<span class="text-white text-xs font-bold">Editar MÃ­dia</span>
</div>
</div>
<p class="text-sm font-bold truncate">Bastidores Live #4</p>
<p class="text-[10px] text-on-surface-variant font-bold uppercase">8.1k VisualizaÃ§Ãµes</p>
</div>
<div class="group cursor-pointer hidden sm:block">
<div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-3">
<img alt="Modelo posando em estÃºdio artÃ­stico" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://lh3.googleusercontent.com/aida-public/AB6AXuACZ1Mtw0LqJ5iQMMdu2NZvz0eVr6svj0S8D83xyzVUT26ydMcg3gFKUmwR0T2KYLhE9NKa15IswGkVEWAT1HpfVWzq9laKkOZvOa8TBbqW20W9ZO4BexE6uyEmMIiI7ApDG1o_ndvaVDOvsehXdpgJ7fIJFHNLeJMxPP6fnFVf_TXEO9-tCklIUQbZWBfHfKywYzJO5_60xedaA6EoGBCtN9m2lMf8ivxbc0eBDawOiYytHOWifOG3NLq8jIXepe_39DJosuMw2R8"/>
<div class="absolute top-3 left-3 bg-primary px-3 py-1 rounded-full text-[10px] font-bold text-white">R$ 120</div>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
<span class="text-white text-xs font-bold">Editar MÃ­dia</span>
</div>
</div>
<p class="text-sm font-bold truncate">Premium Orbit</p>
<p class="text-[10px] text-on-surface-variant font-bold uppercase">1.5k VisualizaÃ§Ãµes</p>
</div>
</div>
</section>
</div>
</div>
</main>
<!-- Footer (Authority: JSON) -->
<footer class="lg:pl-64 bg-zinc-50 dark:bg-zinc-950 full-width py-12 border-t border-pink-100 dark:border-pink-900/20 w-full flex flex-col items-center justify-center gap-6 px-4">
<div class="font-black text-pink-700 text-xl tracking-tighter">SexyLua</div>
<div class="flex flex-wrap justify-center gap-8 font-['Manrope'] text-xs uppercase tracking-widest">
<a class="text-zinc-400 hover:text-pink-500 hover:underline transition-all" href="#">Termos de ServiÃ§o</a>
<a class="text-zinc-400 hover:text-pink-500 hover:underline transition-all" href="#">Privacidade</a>
<a class="text-zinc-400 hover:text-pink-500 hover:underline transition-all" href="#">Suporte Lunar</a>
</div>
<p class="text-zinc-400 font-['Manrope'] text-[10px] uppercase tracking-[0.3em]">Â© 2024 SexyLua Editorial Celestial</p>
</footer>
<!-- FAB for quick actions (Contextual Mobile UI) -->
<button class="md:hidden fixed bottom-8 right-8 w-16 h-16 bg-primary text-on-primary rounded-full shadow-2xl z-50 flex items-center justify-center hover:scale-110 active:scale-95 transition-transform">
<span class="material-symbols-outlined text-3xl">add</span>
</button>
</body></html>
