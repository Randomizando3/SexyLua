<!DOCTYPE html>

<html lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
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
      .lunar-glass {
        background: rgba(251, 249, 251, 0.7);
        backdrop-filter: blur(24px);
      }
      body {
        font-family: 'Manrope', sans-serif;
        background-color: #fbf9fb;
        color: #1b1c1d;
      }
      h1, h2, h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
      }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col">
<!-- TopNavBar (Shared Component: Updated to brand pink background with white text) -->
<header class="bg-[#D81B60] dark:bg-[#ab1155] docked full-width top-0 sticky z-50 tonal-shift shadow-lg shadow-pink-900/20">
<div class="flex justify-between items-center px-8 py-4 w-full max-w-screen-2xl mx-auto">
<div class="text-2xl font-black italic tracking-tighter text-white">SexyLua</div>
<nav class="hidden md:flex gap-8 items-center font-plus-jakarta-sans tracking-wide text-sm font-bold uppercase">
<a class="text-pink-100/80 hover:text-white hover:scale-105 transition-transform duration-200" href="#">Métricas Lunares</a>
<a class="text-pink-100/80 hover:text-white hover:scale-105 transition-transform duration-200" href="#">Ganhos Estelares</a>
<a class="text-pink-100/80 hover:text-white hover:scale-105 transition-transform duration-200" href="#">Configuração ao Vivo</a>
</nav>
<div class="flex items-center gap-4">
<button class="material-symbols-outlined text-white hover:scale-105 transition-transform duration-200" data-icon="notifications">notifications</button>
<button class="material-symbols-outlined text-white hover:scale-105 transition-transform duration-200" data-icon="account_circle">account_circle</button>
</div>
</div>
</header>
<div class="flex flex-1 w-full max-w-screen-2xl mx-auto">
<!-- SideNavBar (Shared Component) -->
<aside class="bg-zinc-50 dark:bg-zinc-900 h-screen w-64 rounded-r-[3rem] shadow-xl fixed left-0 top-0 h-full hidden lg:flex flex-col py-8 z-40">
<div class="px-8 mb-10">
<h2 class="text-pink-700 font-bold text-xl font-headline">Hub Celestial</h2>
<p class="text-xs text-zinc-500 font-label">Fase: Lua Cheia</p>
</div>
<nav class="flex flex-col gap-1 overflow-y-auto">
<a class="bg-pink-50 dark:bg-pink-900/20 text-pink-700 dark:text-pink-400 rounded-full mx-2 px-4 py-3 flex items-center gap-3 font-['Plus_Jakarta_Sans'] font-medium scale-102 duration-200" href="#">
<span class="material-symbols-outlined" data-icon="brightness_4" style="font-variation-settings: 'FILL' 1;">brightness_4</span>
<span>Meu Conteúdo</span>
</a>
<a class="text-zinc-600 dark:text-zinc-400 mx-2 px-4 py-3 flex items-center gap-3 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors font-['Plus_Jakarta_Sans'] font-medium" href="#">
<span class="material-symbols-outlined" data-icon="insights">insights</span>
<span>Métricas Lunares</span>
</a>
<a class="text-zinc-600 dark:text-zinc-400 mx-2 px-4 py-3 flex items-center gap-3 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors font-['Plus_Jakarta_Sans'] font-medium" href="#">
<span class="material-symbols-outlined" data-icon="settings_input_antenna">settings_input_antenna</span>
<span>Configuração ao Vivo</span>
</a>
<a class="text-zinc-600 dark:text-zinc-400 mx-2 px-4 py-3 flex items-center gap-3 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors font-['Plus_Jakarta_Sans'] font-medium" href="#">
<span class="material-symbols-outlined" data-icon="star" style="font-variation-settings: 'FILL' 1;">star</span>
<span>Minhas Assinaturas</span>
</a>
<a class="text-zinc-600 dark:text-zinc-400 mx-2 px-4 py-3 flex items-center gap-3 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors font-['Plus_Jakarta_Sans'] font-medium" href="#">
<span class="material-symbols-outlined" data-icon="favorite">favorite</span>
<span>Favoritos</span>
</a>
<a class="text-zinc-600 dark:text-zinc-400 mx-2 px-4 py-3 flex items-center gap-3 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors font-['Plus_Jakarta_Sans'] font-medium" href="#">
<span class="material-symbols-outlined" data-icon="account_balance_wallet">account_balance_wallet</span>
<span>Carteira</span>
</a>
</nav>
</aside>
<!-- Main Content Area -->
<main class="flex-1 lg:ml-64 p-8 lg:p-12">
<!-- Dashboard Header / Welcome -->
<section class="mb-12 flex flex-col md:flex-row justify-between items-end gap-6">
<div>
<h1 class="text-4xl font-extrabold tracking-tighter text-on-background mb-2">Olá, Explorador Lunar</h1>
<p class="text-on-surface-variant text-lg">Suas estrelas estão alinhadas hoje.</p>
</div>
<!-- Minha Carteira / Balance (Contextual Feature) -->
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-[0px_20px_40px_rgba(27,28,29,0.04)] flex items-center gap-6">
<div class="bg-secondary-fixed w-14 h-14 rounded-full flex items-center justify-center text-on-secondary-fixed">
<span class="material-symbols-outlined text-3xl" data-icon="diamond" style="font-variation-settings: 'FILL' 1;">diamond</span>
</div>
<div>
<p class="text-xs font-label uppercase tracking-widest text-on-surface-variant mb-1">Minha Carteira</p>
<h3 class="text-2xl font-bold text-primary">2.450 <span class="text-sm font-medium">Tokens</span></h3>
</div>
<button class="bg-primary text-on-primary px-6 py-2 rounded-full font-bold hover:scale-105 transition-transform">Recarregar</button>
</div>
</section>
<!-- Grid Content -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-8">
<!-- Minhas Assinaturas (Bento Large) -->
<section class="md:col-span-8">
<div class="flex justify-between items-center mb-6">
<h2 class="text-2xl font-bold tracking-tight">Minhas Assinaturas</h2>
<a class="text-primary font-bold text-sm hover:underline" href="#">Ver todas</a>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
<!-- Creator Card 1 -->
<div class="group relative bg-surface-container-low rounded-lg overflow-hidden transition-all hover:translate-y-[-4px]">
<img class="w-full h-80 object-cover rounded-lg group-hover:scale-105 transition-transform duration-500" data-alt="Bia Velvet portrait with soft lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDOs-53GhAf_noODoN-XghBmQROrR95HbxOJNTd7Hled_6vMIV4ZatWkvFRHScyVBoQJeFNjdia87UzizadvcZPIcsdT3FRmMihS3jb9-4jK4KhxtVDcPzO1b_sV9zx7kxivln5p6ll3M94YS1utcp53gbakfKrsZ4Oah9ACYkbohx-wWsZpa6g0ZgZmoisrGDdWAbu_OPp44e3auDfJ4QtGeudYP00tPMU6uUCD9rbFAVAJphUOb7gooVGpOa0zYfCSjs-BI746Wo"/>
<div class="absolute inset-0 bg-gradient-to-t from-on-background/80 via-transparent to-transparent"></div>
<div class="absolute bottom-6 left-6 right-6 flex justify-between items-end">
<div>
<span class="bg-primary text-on-primary text-[10px] font-bold px-2 py-1 rounded-full uppercase mb-2 inline-block">Assinado</span>
<h4 class="text-white text-xl font-bold">Bia Velvet</h4>
<p class="text-white/70 text-sm">Próxima renovação: 12 Out</p>
</div>
<button class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-primary transition-colors">
<span class="material-symbols-outlined" data-icon="play_arrow" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</button>
</div>
</div>
<!-- Creator Card 2 -->
<div class="group relative bg-surface-container-low rounded-lg overflow-hidden transition-all hover:translate-y-[-4px]">
<img class="w-full h-80 object-cover rounded-lg group-hover:scale-105 transition-transform duration-500" data-alt="Elegant woman in dramatic studio lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCIfHzCOZkxvRl6pMwUwgM55k2x9bH-Oe-AiosDXKtFd0umKpWluBd266EHMRFAP9E1KDiQDir6SS1WtRk_UYlWSgk1N935HQhI7qwNqrPL6149UEN58G7H8qdKEjCYnjn_HAlbk1zYychcNQAIEtZrtHgBl_FlJdjk_ex1T-cOFzjmqPcEY8z3cKNzQr_ag9LY5V9ZpiJRpSbp0SOMlHb-feB8CqW_o1Yh9oCycRF99mzmjMiJRmsntOvqtUZI_WInOfcRKEqo97M"/>
<div class="absolute inset-0 bg-gradient-to-t from-on-background/80 via-transparent to-transparent"></div>
<div class="absolute bottom-6 left-6 right-6 flex justify-between items-end">
<div>
<span class="bg-primary text-on-primary text-[10px] font-bold px-2 py-1 rounded-full uppercase mb-2 inline-block">Assinado</span>
<h4 class="text-white text-xl font-bold">Luna Star</h4>
<p class="text-white/70 text-sm">Próxima renovação: 25 Set</p>
</div>
<button class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-primary transition-colors">
<span class="material-symbols-outlined" data-icon="play_arrow" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</button>
</div>
</div>
</div>
</section>
<!-- Favoritos do Mês (Bento Sidebar Content) -->
<section class="md:col-span-4 flex flex-col gap-8">
<div>
<h2 class="text-2xl font-bold tracking-tight mb-6">Favoritos do Mês</h2>
<div class="space-y-4">
<div class="bg-surface-container-lowest p-4 rounded-xl flex items-center gap-4 group cursor-pointer hover:bg-surface-container-high transition-colors">
<img class="w-16 h-16 rounded-full object-cover" data-alt="Model profile picture thumbnail" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDLuYDvQN_w57J6gcFk4S1-A0cqQkOXcT9EKoNZ5lv9u9wcuj38YjEt9Q9HFQACWYUwyMhWBKkv3XxEI_eWu3Eb0wWbUilI2HTr0lZYgtSFmuVjHG01oZlry8HntFORzSv7G21P7QaxgbGD2OL-5Sw1FR7aPgUp_OfK_K3CQWBNRtfaAveC3nQOSrv8fgvmaMQ-WzOFWWXQCu24Frahaq07RalyIS-rLaYb41bNeECho604YJl-NGioW0Mk2fjcl9GUJOw_pHwTy8s"/>
<div class="flex-1">
<h5 class="font-bold text-on-background">Maya Dreams</h5>
<p class="text-xs text-on-surface-variant">42 novos conteúdos</p>
</div>
<span class="material-symbols-outlined text-primary" data-icon="star" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<div class="bg-surface-container-lowest p-4 rounded-xl flex items-center gap-4 group cursor-pointer hover:bg-surface-container-high transition-colors">
<img class="w-16 h-16 rounded-full object-cover" data-alt="Fashion portrait of a creator" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAmRXhOMvqiNdcs1GbrMcY4EwZnXx3JZFjnPqwnGzQKjXZvZsd5EZrqDh53khmdzVcVxr_JVJXzPgN3S1cfIJTxumWg8zvxhZSIz8X-y24LbHpDsbq43MVr-k_buskgX3OVXiTlqVTdCyd13vqDNuwXf43tRAnQskBz-t4oLQeyTdyHGtNYwkeNUlRn5uYq5X6GZJPE8zTYfXY1GemdNCHg9MK2NmTEOWmE1LaaRBKFoNqyZLZmQb93ZO1Dv_zm8etSJmfZ_piLZNA"/>
<div class="flex-1">
<h5 class="font-bold text-on-background">Red Velvet</h5>
<p class="text-xs text-on-surface-variant">15 novos conteúdos</p>
</div>
<span class="material-symbols-outlined text-primary" data-icon="star" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
</div>
</div>
<!-- Explorar Novas Luas -->
<div class="bg-primary-container text-on-primary-container p-8 rounded-xl relative overflow-hidden">
<div class="relative z-10">
<h3 class="text-2xl font-extrabold mb-4 leading-tight">Descubra Novas Galáxias</h3>
<p class="text-sm mb-6 opacity-90">Novas criadoras entraram em órbita hoje. Explore talentos exclusivos.</p>
<button class="bg-white text-primary px-6 py-3 rounded-full font-bold flex items-center gap-2 hover:scale-105 transition-transform">
<span>Explorar Novas Luas</span>
<span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
</button>
</div>
<!-- Abstract Moon Pattern -->
<div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/20 rounded-full blur-2xl"></div>
<div class="absolute top-5 right-5 w-20 h-20 border-4 border-white/10 rounded-full"></div>
</div>
</section>
</div>
</main>
</div>
<!-- Footer (Shared Component: Updated to brand pink background with white text) -->
<footer class="bg-[#D81B60] dark:bg-[#ab1155] full-width py-12 soft-shadow-top w-full flex flex-col items-center justify-center gap-6 px-4">
<div class="font-bold text-white text-lg tracking-tighter">SexyLua</div>
<div class="flex flex-wrap justify-center gap-8 font-manrope text-xs tracking-widest uppercase text-pink-100">
<a class="hover:text-white hover:underline transition-opacity" href="#">Termos de Serviço</a>
<a class="hover:text-white hover:underline transition-opacity" href="#">Política de Privacidade</a>
<a class="hover:text-white hover:underline transition-opacity" href="#">Suporte Lunar</a>
</div>
<p class="font-manrope text-[10px] uppercase tracking-widest text-pink-100/60">© 2024 SexyLua Celestial Editorial. All phases reserved.</p>
</footer>
<!-- Mobile Bottom NavBar (Contextual) -->
<div class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-3 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl rounded-t-[3rem] shadow-[0px_-10px_30px_rgba(216,27,96,0.1)]">
<a class="flex flex-col items-center justify-center text-[#D81B60] relative after:content-[''] after:w-1 after:h-1 after:bg-[#D81B60] after:rounded-full after:mt-1 active:scale-110 transition-all duration-300 ease-out" href="#">
<span class="material-symbols-outlined" data-icon="wb_twilight" style="font-variation-settings: 'FILL' 1;">wb_twilight</span>
<span class="font-manrope text-[10px] font-medium">Home</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 hover:text-[#D81B60] dark:hover:text-pink-400 transition-all" href="#">
<span class="material-symbols-outlined" data-icon="explore">explore</span>
<span class="font-manrope text-[10px] font-medium">Explore</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 hover:text-[#D81B60] dark:hover:text-pink-400 transition-all" href="#">
<span class="material-symbols-outlined" data-icon="add_circle">add_circle</span>
<span class="font-manrope text-[10px] font-medium">Create</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 hover:text-[#D81B60] dark:hover:text-pink-400 transition-all" href="#">
<span class="material-symbols-outlined" data-icon="nights_stay">nights_stay</span>
<span class="font-manrope text-[10px] font-medium">Activity</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 hover:text-[#D81B60] dark:hover:text-pink-400 transition-all" href="#">
<span class="material-symbols-outlined" data-icon="account_circle">account_circle</span>
<span class="font-manrope text-[10px] font-medium">Profile</span>
</a>
</div>
</body></html>