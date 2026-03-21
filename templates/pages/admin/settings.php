<!DOCTYPE html>

<html class="light" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Configurações do Sistema - SexyLua Admin</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "on-tertiary-fixed-variant": "#5a2a9c",
              "on-tertiary": "#ffffff",
              "on-primary-fixed": "#3f001a",
              "tertiary-container": "#8658ca",
              "on-tertiary-container": "#fcf3ff",
              "error": "#ba1a1a",
              "on-surface-variant": "#5a4044",
              "background": "#fbf9fb",
              "surface-container-highest": "#e3e2e4",
              "on-secondary": "#ffffff",
              "on-primary-container": "#fff2f4",
              "tertiary-fixed-dim": "#d7baff",
              "tertiary-fixed": "#eddcff",
              "secondary-container": "#fd6c9c",
              "inverse-on-surface": "#f2f0f2",
              "tertiary": "#6c3eaf",
              "secondary-fixed": "#ffd9e1",
              "surface-variant": "#e3e2e4",
              "on-surface": "#1b1c1d",
              "on-background": "#1b1c1d",
              "secondary": "#ab2c5d",
              "inverse-surface": "#303032",
              "surface": "#fbf9fb",
              "surface-tint": "#b41b5c",
              "surface-dim": "#dbd9db",
              "on-error": "#ffffff",
              "error-container": "#ffdad6",
              "on-secondary-fixed-variant": "#8b0e45",
              "on-secondary-fixed": "#3f001b",
              "on-primary-fixed-variant": "#8f0045",
              "surface-bright": "#fbf9fb",
              "inverse-primary": "#ffb1c5",
              "outline": "#8e6f74",
              "on-secondary-container": "#6e0034",
              "surface-container-high": "#e9e7e9",
              "on-tertiary-fixed": "#280056",
              "on-primary": "#ffffff",
              "surface-container-low": "#f5f3f5",
              "primary-fixed-dim": "#ffb1c5",
              "secondary-fixed-dim": "#ffb1c5",
              "primary-fixed": "#ffd9e1",
              "primary": "#ab1155",
              "on-error-container": "#93000a",
              "outline-variant": "#e3bdc3",
              "primary-container": "#cc326e",
              "surface-container": "#efedef",
              "surface-container-lowest": "#ffffff"
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
      ::-webkit-scrollbar { width: 6px; }
      ::-webkit-scrollbar-track { background: transparent; }
      ::-webkit-scrollbar-thumb { background: #e3bdc3; border-radius: 10px; }
      
      .lunar-glass {
        background: rgba(251, 249, 251, 0.7);
        backdrop-filter: blur(24px);
      }
    </style>
</head>
<body class="bg-background font-body text-on-background antialiased">
<!-- TopNavBar Implementation from JSON -->
<header class="fixed top-0 left-0 w-full h-16 flex items-center justify-between px-6 bg-[#D81B60] dark:bg-[#ab1155] text-white font-['Plus_Jakarta_Sans'] antialiased tracking-wide shadow-lg z-50">
<div class="flex items-center gap-8">
<span class="text-2xl font-bold text-white">SexyLua Admin</span>
<div class="hidden md:flex items-center gap-6">
<nav class="flex gap-4">
<a class="text-pink-100 hover:text-white transition-colors" href="#">Geral</a>
<a class="text-white border-b-2 border-white pb-1" href="#">Configurações</a>
<a class="text-pink-100 hover:text-white transition-colors" href="#">Segurança</a>
</nav>
</div>
</div>
<div class="flex items-center gap-4">
<button class="hover:bg-white/10 rounded-full transition-all p-2 scale-95 active:scale-90 duration-200">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="hover:bg-white/10 rounded-full transition-all p-2 scale-95 active:scale-90 duration-200">
<span class="material-symbols-outlined">settings</span>
</button>
<button class="hover:bg-white/10 rounded-full transition-all p-2 scale-95 active:scale-90 duration-200">
<span class="material-symbols-outlined">account_circle</span>
</button>
</div>
</header>
<!-- SideNavBar Implementation from JSON -->
<aside class="fixed left-0 top-16 h-[calc(100vh-64px)] w-64 flex flex-col pt-4 bg-slate-50 dark:bg-slate-900 border-r border-slate-200/50 dark:border-slate-800/50 font-['Manrope'] text-sm font-medium transition-all duration-300">
<div class="px-6 py-4 flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-white overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Avatar de administrador profissional masculino" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDiBcuoYrQBCzvyBticheQCMictSu_sD7VoTIAwWV35GE0SFzEUYBsOdtPsJi-K1RLicqRrecP7mm2viGqgR3_gHOYhsSSZr0IOQFu5_5IYhiDocyST1G-uVQzb2emEvuskBlRfeNazpfRZhzS_gxPb7gNXMklbWADz5CPTgN9yjqh4Cb3Hqko7hZU-xyOe_SD8EOJOHUnARI2e0oNzPsjLQzqWqEuiflIwWAhWN5q7-xNn_cPeaePhtjJr_gOyYimrS1NkzyzgEa0"/>
</div>
<div>
<p class="font-bold text-on-background">Administrador</p>
<p class="text-[10px] text-slate-500 uppercase tracking-tighter">Nível de Acesso Total</p>
</div>
</div>
<nav class="flex-1 mt-4">
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined">dashboard</span>
                Painel
            </a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined">group</span>
                Usuários
            </a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined">subscriptions</span>
                Conteúdo
            </a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined">assessment</span>
                Relatórios
            </a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined">payments</span>
                Pagamentos
            </a>
<a class="flex items-center gap-3 bg-[#D81B60]/10 text-[#D81B60] dark:text-pink-400 rounded-r-full py-3 px-6 border-l-4 border-[#D81B60] hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined">tune</span>
                Configurações
            </a>
</nav>
<div class="mt-auto border-t border-slate-200/50 dark:border-slate-800/50 mb-6">
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-4 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all" href="#">
<span class="material-symbols-outlined text-error">logout</span>
                Sair
            </a>
</div>
</aside>
<!-- Main Content Canvas -->
<main class="ml-64 pt-16 min-h-screen">
<div class="max-w-7xl mx-auto p-10">
<!-- Header Editorial Style -->
<header class="mb-12">
<h1 class="font-headline text-5xl font-extrabold tracking-tight text-on-background mb-2">Configurações do Sistema</h1>
<p class="text-on-surface-variant font-body text-lg">Gerencie as regras de negócio, limites operacionais e a estética da plataforma.</p>
</header>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
<!-- Bento Grid Left Column -->
<div class="lg:col-span-7 space-y-8">
<!-- Taxas e Comissões Section -->
<section class="bg-surface-container-lowest rounded-xl p-8 shadow-sm">
<div class="flex items-center gap-4 mb-8">
<div class="p-3 bg-primary-container/10 rounded-full">
<span class="material-symbols-outlined text-primary">payments</span>
</div>
<h2 class="font-headline text-2xl font-bold">Taxas e Comissões</h2>
</div>
<div class="space-y-6">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="space-y-2">
<label class="font-label text-sm text-on-surface-variant px-1">Comissão Administrativa (%)</label>
<div class="relative">
<input class="w-full bg-surface-container-low border-none rounded-md px-4 py-3 focus:ring-1 focus:ring-primary transition-all" placeholder="20" type="number"/>
<span class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant">%</span>
</div>
</div>
<div class="space-y-2">
<label class="font-label text-sm text-on-surface-variant px-1">Taxa de Processamento (Fixo)</label>
<div class="relative">
<input class="w-full bg-surface-container-low border-none rounded-md px-4 py-3 focus:ring-1 focus:ring-primary transition-all" placeholder="R$ 1,50" type="text"/>
</div>
</div>
</div>
<div class="p-6 bg-surface-container rounded-lg border-l-4 border-primary">
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary">info</span>
<p class="text-sm text-on-surface-variant leading-relaxed">
                                        Estas taxas são aplicadas automaticamente em todas as transações de venda de conteúdo e gorjetas. Alterações entram em vigor imediatamente para novas vendas.
                                    </p>
</div>
</div>
</div>
</section>
<!-- Limites de Saque Section -->
<section class="bg-surface-container-lowest rounded-xl p-8 shadow-sm">
<div class="flex items-center gap-4 mb-8">
<div class="p-3 bg-secondary-container/10 rounded-full">
<span class="material-symbols-outlined text-secondary">account_balance_wallet</span>
</div>
<h2 class="font-headline text-2xl font-bold">Limites de Saque</h2>
</div>
<div class="space-y-8">
<div class="space-y-4">
<div class="flex justify-between items-end px-1">
<label class="font-label text-sm text-on-surface-variant">Limite Mínimo Diário</label>
<span class="font-bold text-primary">R$ 50,00</span>
</div>
<input class="w-full h-2 bg-surface-container rounded-full appearance-none cursor-pointer accent-primary" max="500" min="20" type="range" value="50"/>
</div>
<div class="space-y-4">
<div class="flex justify-between items-end px-1">
<label class="font-label text-sm text-on-surface-variant">Limite Máximo Mensal</label>
<span class="font-bold text-primary">R$ 25.000,00</span>
</div>
<input class="w-full h-2 bg-surface-container rounded-full appearance-none cursor-pointer accent-primary" max="100000" min="5000" type="range" value="25000"/>
</div>
<div class="flex items-center justify-between p-4 bg-surface-container-low rounded-lg">
<div>
<p class="font-bold">Aprovação Manual</p>
<p class="text-xs text-on-surface-variant">Saques acima de R$ 5.000 exigem revisão</p>
</div>
<label class="relative inline-flex items-center cursor-pointer">
<input checked="" class="sr-only peer" type="checkbox"/>
<div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
</label>
</div>
</div>
</section>
</div>
<!-- Right Column Side Modules -->
<div class="lg:col-span-5 space-y-8">
<!-- Configurações de Chat -->
<section class="bg-surface-container-lowest rounded-xl p-8 shadow-sm">
<div class="flex items-center gap-4 mb-8">
<div class="p-3 bg-tertiary-container/10 rounded-full">
<span class="material-symbols-outlined text-tertiary">chat_bubble</span>
</div>
<h2 class="font-headline text-2xl font-bold">Configurações de Chat</h2>
</div>
<div class="space-y-6">
<div class="flex items-center justify-between">
<div>
<p class="font-bold">Modo Slow (Lento)</p>
<p class="text-xs text-on-surface-variant">Intervalo entre mensagens dos fãs</p>
</div>
<div class="flex items-center gap-2">
<input class="w-12 text-center text-xs font-bold bg-surface-container border-none rounded-full py-1" type="text" value="3s"/>
<label class="relative inline-flex items-center cursor-pointer">
<input class="sr-only peer" type="checkbox"/>
<div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
</label>
</div>
</div>
<div class="flex items-center justify-between">
<div>
<p class="font-bold">Inteligência Artificial (IA)</p>
<p class="text-xs text-on-surface-variant">Sugestões de resposta automáticas</p>
</div>
<label class="relative inline-flex items-center cursor-pointer">
<input checked="" class="sr-only peer" type="checkbox"/>
<div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
</label>
</div>
<div class="flex items-center justify-between">
<div>
<p class="font-bold">Filtro Antispam</p>
<p class="text-xs text-on-surface-variant">Bloquear links externos no chat</p>
</div>
<label class="relative inline-flex items-center cursor-pointer">
<input checked="" class="sr-only peer" type="checkbox"/>
<div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
</label>
</div>
</div>
</section>
<!-- Temas Visuais -->
<section class="bg-surface-container-lowest rounded-xl p-8 shadow-sm overflow-hidden relative">
<!-- Abstract Gradient Pattern -->
<div class="absolute -right-10 -top-10 w-40 h-40 bg-primary/10 rounded-full blur-3xl"></div>
<div class="flex items-center gap-4 mb-8">
<div class="p-3 bg-primary-container/10 rounded-full">
<span class="material-symbols-outlined text-primary">palette</span>
</div>
<h2 class="font-headline text-2xl font-bold">Temas Visuais</h2>
</div>
<div class="grid grid-cols-2 gap-4">
<button class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-primary bg-primary/5 transition-all">
<div class="w-full h-20 bg-white rounded-lg shadow-inner overflow-hidden flex flex-col gap-1 p-2">
<div class="w-full h-4 bg-primary rounded"></div>
<div class="w-2/3 h-2 bg-surface-container rounded"></div>
<div class="w-1/2 h-2 bg-surface-container rounded"></div>
</div>
<span class="text-sm font-bold text-primary">Lunar Light</span>
</button>
<button class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-transparent hover:border-surface-variant transition-all">
<div class="w-full h-20 bg-slate-900 rounded-lg shadow-inner overflow-hidden flex flex-col gap-1 p-2">
<div class="w-full h-4 bg-[#ab1155] rounded"></div>
<div class="w-2/3 h-2 bg-slate-700 rounded"></div>
<div class="w-1/2 h-2 bg-slate-700 rounded"></div>
</div>
<span class="text-sm font-bold text-on-surface-variant">Midnight Lua</span>
</button>
<button class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-transparent hover:border-surface-variant transition-all">
<div class="w-full h-20 bg-pink-50 rounded-lg shadow-inner overflow-hidden flex flex-col gap-1 p-2">
<div class="w-full h-4 bg-[#D81B60] rounded"></div>
<div class="w-2/3 h-2 bg-pink-200 rounded"></div>
<div class="w-1/2 h-2 bg-pink-200 rounded"></div>
</div>
<span class="text-sm font-bold text-on-surface-variant">Barbie Core</span>
</button>
<button class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-transparent hover:border-surface-variant transition-all">
<div class="w-full h-20 bg-white rounded-lg border-2 border-dashed border-slate-300 flex items-center justify-center">
<span class="material-symbols-outlined text-slate-400">add_circle</span>
</div>
<span class="text-sm font-bold text-on-surface-variant">Personalizado</span>
</button>
</div>
</section>
<!-- Save Actions -->
<div class="flex gap-4">
<button class="flex-1 py-4 bg-primary text-white rounded-full font-bold hover:bg-primary-container transition-all shadow-lg hover:scale-105 active:scale-95 duration-200">
                            Salvar Alterações
                        </button>
<button class="px-8 py-4 bg-surface-container-high text-on-surface rounded-full font-bold hover:bg-surface-variant transition-all">
                            Descartar
                        </button>
</div>
</div>
</div>
</div>
</main>
</body></html>