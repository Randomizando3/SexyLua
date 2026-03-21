<!DOCTYPE html>

<html lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Manrope', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, .headline {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Custom scrollbar for clean UI */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #e3bdc3;
            border-radius: 10px;
        }
    </style>
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
</head>
<body class="bg-background text-on-background min-h-screen">
<!-- TopNavBar -->
<header class="fixed top-0 left-0 w-full h-16 flex items-center justify-between px-6 bg-[#D81B60] dark:bg-[#ab1155] text-white font-['Plus_Jakarta_Sans'] antialiased tracking-wide shadow-lg z-50">
<div class="flex items-center gap-8">
<span class="text-2xl font-bold text-white">SexyLua Admin</span>
<div class="hidden md:flex items-center gap-6">
<a class="text-white border-b-2 border-white pb-1" href="#">Dashboard</a>
<a class="text-pink-100 hover:text-white transition-colors" href="#">Relatórios</a>
<a class="text-pink-100 hover:text-white transition-colors" href="#">Segurança</a>
</div>
</div>
<div class="flex items-center gap-4">
<div class="relative group">
<button class="p-2 hover:bg-white/10 rounded-full transition-all active:scale-90 duration-200">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
</div>
<button class="p-2 hover:bg-white/10 rounded-full transition-all active:scale-90 duration-200">
<span class="material-symbols-outlined" data-icon="settings">settings</span>
</button>
<button class="flex items-center gap-2 pl-2 pr-1 py-1 hover:bg-white/10 rounded-full transition-all active:scale-90 duration-200">
<span class="text-sm font-medium">Admin</span>
<span class="material-symbols-outlined text-3xl" data-icon="account_circle">account_circle</span>
</button>
</div>
</header>
<!-- SideNavBar -->
<aside class="fixed left-0 top-16 h-[calc(100vh-64px)] w-64 flex flex-col pt-4 bg-slate-50 dark:bg-slate-900 border-r border-slate-200/50 dark:border-slate-800/50 font-['Manrope'] text-sm font-medium">
<div class="px-6 mb-8 flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container">
<span class="material-symbols-outlined" data-icon="shield">shield</span>
</div>
<div>
<p class="text-on-surface font-bold leading-tight">Administrador</p>
<p class="text-xs text-slate-500">Nível de Acesso Total</p>
</div>
</div>
<nav class="flex-1 space-y-1">
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
<span>Painel</span>
</a>
<a class="flex items-center gap-3 bg-[#D81B60]/10 text-[#D81B60] dark:text-pink-400 rounded-r-full py-3 px-6 border-l-4 border-[#D81B60]" href="#">
<span class="material-symbols-outlined" data-icon="group" style="font-variation-settings: 'FILL' 1;">group</span>
<span>Usuários</span>
</a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined" data-icon="subscriptions">subscriptions</span>
<span>Conteúdo</span>
</a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined" data-icon="assessment">assessment</span>
<span>Relatórios</span>
</a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined" data-icon="payments">payments</span>
<span>Pagamentos</span>
</a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined" data-icon="tune">tune</span>
<span>Configurações</span>
</a>
</nav>
<div class="mt-auto pb-6">
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-r-full transition-all" href="#">
<span class="material-symbols-outlined text-error" data-icon="logout">logout</span>
<span class="text-error">Sair</span>
</a>
</div>
</aside>
<!-- Main Content Canvas -->
<main class="ml-64 pt-16 min-h-screen">
<div class="p-8 max-w-[1600px] mx-auto">
<!-- Header Section -->
<div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
<div>
<h1 class="text-4xl font-extrabold text-on-surface tracking-tight mb-2">Gestão de Usuários</h1>
<p class="text-outline text-lg">Gerencie permissões, analise métricas e monitore atividades da plataforma.</p>
</div>
<div class="flex gap-3">
<button class="px-6 py-3 bg-surface-container-highest text-on-surface font-semibold rounded-full hover:scale-105 transition-all duration-200 flex items-center gap-2">
<span class="material-symbols-outlined text-xl" data-icon="file_download">file_download</span>
                        Exportar CSV
                    </button>
<button class="px-6 py-3 bg-primary text-on-primary font-semibold rounded-full shadow-lg shadow-primary/20 hover:bg-primary-container hover:scale-105 transition-all duration-200 flex items-center gap-2">
<span class="material-symbols-outlined text-xl" data-icon="person_add">person_add</span>
                        Novo Usuário
                    </button>
</div>
</div>
<!-- Bento Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm flex flex-col justify-between group transition-all duration-300">
<div class="flex justify-between items-start mb-4">
<div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-2xl" data-icon="groups">groups</span>
</div>
<span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">+12%</span>
</div>
<div>
<p class="text-sm font-medium text-outline uppercase tracking-wider">Total de Usuários</p>
<h3 class="text-3xl font-extrabold text-on-surface mt-1">12.450</h3>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm flex flex-col justify-between group transition-all duration-300">
<div class="flex justify-between items-start mb-4">
<div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-2xl" data-icon="star">star</span>
</div>
<span class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded-full">VIP</span>
</div>
<div>
<p class="text-sm font-medium text-outline uppercase tracking-wider">Criadores Ativos</p>
<h3 class="text-3xl font-extrabold text-on-surface mt-1">842</h3>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm flex flex-col justify-between group transition-all duration-300">
<div class="flex justify-between items-start mb-4">
<div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-2xl" data-icon="pending_actions">pending_actions</span>
</div>
<span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Ação</span>
</div>
<div>
<p class="text-sm font-medium text-outline uppercase tracking-wider">Pendentes</p>
<h3 class="text-3xl font-extrabold text-on-surface mt-1">124</h3>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm flex flex-col justify-between group transition-all duration-300">
<div class="flex justify-between items-start mb-4">
<div class="w-12 h-12 bg-red-50 text-red-600 rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-2xl" data-icon="block">block</span>
</div>
<span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded-full">Risco</span>
</div>
<div>
<p class="text-sm font-medium text-outline uppercase tracking-wider">Banidos</p>
<h3 class="text-3xl font-extrabold text-on-surface mt-1">18</h3>
</div>
</div>
</div>
<!-- Filters & Actions Area -->
<div class="bg-surface-container-low p-6 rounded-xl mb-6 flex flex-wrap items-center justify-between gap-6">
<div class="flex flex-wrap items-center gap-4">
<div class="relative min-w-[300px]">
<span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline" data-icon="search">search</span>
<input class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-sm" placeholder="Buscar por nome, email ou ID..." type="text"/>
</div>
<select class="pl-4 pr-10 py-3 bg-surface-container-lowest border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-sm font-medium appearance-none cursor-pointer">
<option>Tipo: Todos</option>
<option>Criador</option>
<option>Assinante</option>
</select>
<select class="pl-4 pr-10 py-3 bg-surface-container-lowest border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-sm font-medium appearance-none cursor-pointer">
<option>Status: Todos</option>
<option>Ativo</option>
<option>Pendente</option>
<option>Banido</option>
</select>
</div>
<div class="flex items-center gap-2">
<span class="text-sm text-outline">Exibindo 25 de 12.450</span>
<div class="flex gap-1">
<button class="w-8 h-8 rounded-md bg-surface-container-lowest flex items-center justify-center text-outline hover:text-primary transition-colors">
<span class="material-symbols-outlined text-sm" data-icon="chevron_left">chevron_left</span>
</button>
<button class="w-8 h-8 rounded-md bg-surface-container-lowest flex items-center justify-center text-outline hover:text-primary transition-colors">
<span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
</button>
</div>
</div>
</div>
<!-- Main Data Table Card -->
<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant/10">
<th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Usuário</th>
<th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Tipo</th>
<th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider">Ganhos / Gastos</th>
<th class="px-6 py-4 text-xs font-bold text-outline uppercase tracking-wider text-right">Ações</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
<!-- Row 1 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<img class="w-10 h-10 rounded-full object-cover" data-alt="Female profile photo with warm lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA7gBKrMpA6rZPkPqzaPsMNDozU4ielsZtUlwA5aLfMcI8cSccf_LSeMFquaCAvPMNicBMVT0286KLX809Xq2hLux08eQ-Mw1iXcqL8mQiENsm7R0P8ymh3KmFwmDmupMHKn-zwjLyFLIsPTKHmo-kGKHpXVfaj_N1bTcC4TiVU2m1e5v7EM7nxFbWshGVAz5QrCxRMFIV_h7SxZP3jmTAh0Bt3ARRtHYxDOtNPQyHmEusv9o5u-f3K2ibwBlFH7wOyo7uy3tOklmg"/>
<div>
<div class="text-sm font-bold text-on-surface">Mariana Silva</div>
<div class="text-xs text-outline">mariana.s@email.com</div>
</div>
</div>
</td>
<td class="px-6 py-4">
<span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-bold">Criador</span>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-green-500"></span>
<span class="text-sm font-medium text-on-surface">Ativo</span>
</div>
</td>
<td class="px-6 py-4 text-sm font-bold text-green-600">R$ 14.250,00</td>
<td class="px-6 py-4 text-right">
<button class="p-2 text-outline hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</td>
</tr>
<!-- Row 2 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<img class="w-10 h-10 rounded-full object-cover" data-alt="Male profile photo business professional" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAEP-xc1sqdy01I27HrbP2yc0ucUbl0tOmNTTJHaLyUUPG8eK8K1fa8kC7RhbEjegxYUnljw3OXiE7txv9bWCi6LTnimQ9B6FxX8CT58CrnTguy4AfkGDGzWojItLUS-IWiXzxm1bOufowucapQtrIMmSKhDqrgaJfXOgtLytcMZAiHKOLAEuVzmKL3RBLxPC4O5f5rle3EMlobBm_kHmS959i4W5zdTusbAYeO7TFZhnykZnhWIVje17JMS82QU1H3uOj4yeJ2bzw"/>
<div>
<div class="text-sm font-bold text-on-surface">Ricardo Gomes</div>
<div class="text-xs text-outline">ricardo.g@email.com</div>
</div>
</div>
</td>
<td class="px-6 py-4">
<span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold">Assinante</span>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-green-500"></span>
<span class="text-sm font-medium text-on-surface">Ativo</span>
</div>
</td>
<td class="px-6 py-4 text-sm font-bold text-on-surface">R$ 450,00</td>
<td class="px-6 py-4 text-right">
<button class="p-2 text-outline hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</td>
</tr>
<!-- Row 3 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<img class="w-10 h-10 rounded-full object-cover" data-alt="Cheerful young woman smiling profile" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCNmzbehjwQH9z3NHq7-j1spNzwLXKK_4ZWC68kYNJP14oDHxPCo6zmgIgpHg-Xd32zLwDZGoJFvn6ZIRn_Kn6CN_KHQxSjEvoTFdYBpbWMosYCz948W7AndM_-tMWRJL9-GQuXGON0LVCFlvHwcdjb8RgsqFHssGVPvpnecGkGcl9JNiIgtG-SkUncgdmcaISEWcncvXGtXNIVWpszuMXc__zAhze2lFxC4HVBLF9-WGccwYNvdxNMhi5rZNnW86Z9nJ0RFIasChc"/>
<div>
<div class="text-sm font-bold text-on-surface">Ana Paula Castro</div>
<div class="text-xs text-outline">ana.castro@email.com</div>
</div>
</div>
</td>
<td class="px-6 py-4">
<span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-bold">Criador</span>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-amber-500"></span>
<span class="text-sm font-medium text-on-surface">Pendente</span>
</div>
</td>
<td class="px-6 py-4 text-sm font-bold text-on-surface">R$ 0,00</td>
<td class="px-6 py-4 text-right">
<button class="p-2 text-outline hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</td>
</tr>
<!-- Row 4 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<img class="w-10 h-10 rounded-full object-cover grayscale" data-alt="Portrait of a focused male" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA88ChF6YDZoLXXdVnRn2zTrPMgVbuhBriyhb-7PYEPRJ8GjglH0197SS7WVY7mhizBMWbETI7JF18ksMCv3DW2FeP7hpmnk0vMCabbyYUBqo4BO6p6gl4Wm4Z9lrJ96y_xAXvAYoS3kzAd8hLzFlw8dt2SI-H3N2UwGJhKMtYsFzyHhyG-tDZtLZmIIboVCP6R8bUnSuvC5T7ZKbyayvfn6gD0i2XHBnj3YwCv_TUMpPn-zyL342eT8dgljTFeqtECKkcukaR6yuc"/>
<div>
<div class="text-sm font-bold text-on-surface">João Mendes</div>
<div class="text-xs text-outline">joao.m@email.com</div>
</div>
</div>
</td>
<td class="px-6 py-4">
<span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold">Assinante</span>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-red-500"></span>
<span class="text-sm font-medium text-on-surface">Banido</span>
</div>
</td>
<td class="px-6 py-4 text-sm font-bold text-red-600">Bloqueado</td>
<td class="px-6 py-4 text-right">
<button class="p-2 text-outline hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</td>
</tr>
<!-- Row 5 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<img class="w-10 h-10 rounded-full object-cover" data-alt="Stylish woman looking at camera" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCQZbxbHbehTA8chDCJ1eXssEyeRjbLzG-JsAJHEgbb0G-4pTFcdOQyMXsSof0sfQFvAp1-qE8kT7SVxXQp31p-kLPkjJCP0Vm1jOD6zSAGjL_cYLfd0FHHbdPSrvgOgEOkzcbJU8A-xBR2OQ6wE1gti4t_4UlRVVFCrlOYleZF8Jp2ODXZ9bUpUIGXNWE1RJvr61H9LfiiZyA5RQcLJtLN0fa4QocXKqiAs0t9BzeXgrX5gZ41ohgbd8tGMs9cr6XziLi-Lth629E"/>
<div>
<div class="text-sm font-bold text-on-surface">Beatriz Oliveira</div>
<div class="text-xs text-outline">bia.oliveira@email.com</div>
</div>
</div>
</td>
<td class="px-6 py-4">
<span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-bold">Criador</span>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-green-500"></span>
<span class="text-sm font-medium text-on-surface">Ativo</span>
</div>
</td>
<td class="px-6 py-4 text-sm font-bold text-green-600">R$ 8.920,00</td>
<td class="px-6 py-4 text-right">
<button class="p-2 text-outline hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</td>
</tr>
</tbody>
</table>
</div>
<!-- Dashboard Insights -->
<div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-8">
<div class="bg-surface-container-low p-8 rounded-lg relative overflow-hidden">
<div class="relative z-10">
<h4 class="text-xl font-extrabold mb-4">Tendência de Crescimento</h4>
<div class="h-48 flex items-end gap-2 px-2">
<div class="w-full bg-primary/20 h-[30%] rounded-t-lg transition-all hover:bg-primary"></div>
<div class="w-full bg-primary/20 h-[45%] rounded-t-lg transition-all hover:bg-primary"></div>
<div class="w-full bg-primary/20 h-[40%] rounded-t-lg transition-all hover:bg-primary"></div>
<div class="w-full bg-primary/20 h-[65%] rounded-t-lg transition-all hover:bg-primary"></div>
<div class="w-full bg-primary/20 h-[80%] rounded-t-lg transition-all hover:bg-primary"></div>
<div class="w-full bg-primary/20 h-[95%] rounded-t-lg transition-all hover:bg-primary"></div>
<div class="w-full bg-primary/20 h-[85%] rounded-t-lg transition-all hover:bg-primary"></div>
</div>
<div class="flex justify-between mt-4 text-xs font-bold text-outline">
<span>SEG</span><span>TER</span><span>QUA</span><span>QUI</span><span>SEX</span><span>SAB</span><span>DOM</span>
</div>
</div>
</div>
<div class="bg-primary text-on-primary p-8 rounded-lg flex flex-col justify-between">
<div>
<h4 class="text-xl font-extrabold mb-2">Suporte Prioritário</h4>
<p class="text-on-primary/80 mb-6">Existem 12 criadores aguardando verificação manual de identidade.</p>
</div>
<button class="w-full py-4 bg-white text-primary font-bold rounded-full hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                        Verificar Agora
                        <span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
</button>
</div>
</div>
</div>
</main>
<!-- FAB Suppression Rule followed: No FAB for settings/admin management screens as per instructions -->
</body></html>