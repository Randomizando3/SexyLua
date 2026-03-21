<!DOCTYPE html>

<html class="light" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>SexyLua Admin - Moderação de Conteúdo</title>
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
        body {
            background-color: #fbf9fb;
        }
    </style>
</head>
<body class="font-body text-on-background antialiased">
<!-- TopNavBar -->
<header class="fixed top-0 left-0 w-full h-16 flex items-center justify-between px-6 bg-[#D81B60] dark:bg-[#ab1155] text-white font-['Plus_Jakarta_Sans'] antialiased tracking-wide shadow-lg z-50">
<div class="flex items-center gap-8">
<span class="text-2xl font-bold text-white">SexyLua Admin</span>
<div class="hidden md:flex items-center gap-6">
<button class="text-white border-b-2 border-white pb-1">Moderação</button>
<button class="text-pink-100 hover:text-white transition-colors">Estatísticas</button>
<button class="text-pink-100 hover:text-white transition-colors">Logs</button>
</div>
</div>
<div class="flex items-center gap-4">
<button class="p-2 hover:bg-white/10 rounded-full transition-all scale-95 active:scale-90 duration-200">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
<button class="p-2 hover:bg-white/10 rounded-full transition-all scale-95 active:scale-90 duration-200">
<span class="material-symbols-outlined" data-icon="settings">settings</span>
</button>
<button class="p-2 hover:bg-white/10 rounded-full transition-all scale-95 active:scale-90 duration-200">
<span class="material-symbols-outlined" data-icon="account_circle">account_circle</span>
</button>
</div>
</header>
<!-- SideNavBar -->
<aside class="fixed left-0 top-16 h-[calc(100vh-64px)] w-64 flex flex-col pt-4 bg-slate-50 dark:bg-slate-900 border-r border-slate-200/50 dark:border-slate-800/50 font-['Manrope'] text-sm font-medium z-40">
<div class="px-6 mb-8 flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center overflow-hidden">
<img alt="Admin Avatar" class="w-full h-full object-cover" data-alt="Close-up portrait of a male administrator" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDmk9zQbYc_tid9vqZFaGJMZvG8Y-DALu7a7g9kyN-Pz2wL1tTC6i8Ba-BkQSK62bJ2DW4xp69cJsnE7qLOWES64HR5J7NeoD6UChvwBpsUAqt1COi5cl5UinNUry31zThjdPQGTP98DAuk5KWOv2TGjlHItTea_xozwsbj-YIdcEHIhmWBmRqzTYl45-WwVWKs33V_PWwoS-c3UY4ZkCmtiX3HDsp52tzM4NKGbWCPX-mIZju1NdpUJtieYHGnwBSk1yoLUaqDlxU"/>
</div>
<div>
<p class="text-on-surface font-bold">Administrador</p>
<p class="text-xs text-slate-500">Nível de Acesso Total</p>
</div>
</div>
<nav class="flex-1 space-y-1">
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined text-primary" data-icon="dashboard">dashboard</span>
<span>Painel</span>
</a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined text-primary" data-icon="group">group</span>
<span>Usuários</span>
</a>
<a class="flex items-center gap-3 bg-[#D81B60]/10 text-[#D81B60] dark:text-pink-400 rounded-r-full py-3 px-6 border-l-4 border-[#D81B60] transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined" data-icon="subscriptions">subscriptions</span>
<span>Conteúdo</span>
</a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined text-primary" data-icon="assessment">assessment</span>
<span>Relatórios</span>
</a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined text-primary" data-icon="payments">payments</span>
<span>Pagamentos</span>
</a>
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined text-primary" data-icon="tune">tune</span>
<span>Configurações</span>
</a>
</nav>
<div class="mt-auto pb-8">
<a class="flex items-center gap-3 text-slate-600 dark:text-slate-400 py-3 px-6 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-r-full transition-all hover:translate-x-1 duration-300" href="#">
<span class="material-symbols-outlined" data-icon="logout">logout</span>
<span>Sair</span>
</a>
</div>
</aside>
<!-- Main Content Canvas -->
<main class="ml-64 pt-24 px-8 pb-12 min-h-screen">
<header class="mb-10">
<h1 class="font-headline text-4xl font-extrabold tracking-tight text-on-surface">Fila de Moderação</h1>
<p class="text-on-surface-variant mt-2 font-medium">Analise as denúncias de conteúdo reportadas pela comunidade.</p>
</header>
<!-- Moderation Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
<div class="bg-surface-container-low p-6 rounded-lg">
<span class="text-sm font-bold text-primary uppercase tracking-widest mb-2 block">Pendentes</span>
<div class="flex items-baseline gap-2">
<span class="text-4xl font-extrabold font-headline">128</span>
<span class="text-sm text-on-surface-variant font-medium">arquivos</span>
</div>
</div>
<div class="bg-surface-container-low p-6 rounded-lg">
<span class="text-sm font-bold text-secondary uppercase tracking-widest mb-2 block">Tempo Médio</span>
<div class="flex items-baseline gap-2">
<span class="text-4xl font-extrabold font-headline">14</span>
<span class="text-sm text-on-surface-variant font-medium">minutos</span>
</div>
</div>
<div class="bg-surface-container-low p-6 rounded-lg border-l-4 border-primary">
<span class="text-sm font-bold text-primary uppercase tracking-widest mb-2 block">Nível de Risco</span>
<div class="flex items-baseline gap-2">
<span class="text-4xl font-extrabold font-headline">Alto</span>
<span class="material-symbols-outlined text-primary self-center" data-icon="warning" style="font-variation-settings: 'FILL' 1;">warning</span>
</div>
</div>
</div>
<!-- Bento Grid Moderation Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<!-- Card 1 -->
<div class="bg-surface-container-lowest rounded-lg shadow-sm overflow-hidden flex flex-col group transition-transform hover:-translate-y-1">
<div class="relative h-64 overflow-hidden">
<img alt="Reported Content" class="w-full h-full object-cover" data-alt="Artistic fashion photography shot in studio" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCnNIBHA6uVIjOIh1H-o5Z0EsRSmS4Yn8wKHS679zyVkwvvVFlLaAjWEdQ_aqAVVmOgvtaoHjoQxdFfUJmzzhHJ_sWGGLzowDF3hLrwRCtQpKku7t_qrens5zhPygXweNQu_MCwr5rHSfR2mXnE4O1Uzh4frm-0WSNx0pAxWexP_7iII82empjJUf5de80fMZOCLp2AWMNAz_IvBA2thTA6Uz_hJnoVYRoz3rKtG_bxLKymkaSnc7u7V-IxNi3-FC8qjmvVt3GGGbM"/>
<div class="absolute top-4 left-4">
<span class="bg-error text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">Altamente Sensível</span>
</div>
<div class="absolute bottom-4 left-4 flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/30">
<span class="material-symbols-outlined text-white text-sm" data-icon="videocam" style="font-variation-settings: 'FILL' 1;">videocam</span>
<span class="text-white text-xs font-bold uppercase">Vídeo 0:15</span>
</div>
</div>
<div class="p-6 flex-1 flex flex-col">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-full overflow-hidden border-2 border-primary-container">
<img alt="User Profile" class="w-full h-full object-cover" data-alt="Portrait of a female user" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA5Vm2OfLYCEsxQdp9egxS517IUxgKOxtNnZm-mL0xjGVtt2Yqgu2Do-za-N0b9g5eXSlpb2lJtBP4yB5o4bRf1mqMdPQjKG5eDEJGR56Af5AlJQ7ihlmW4q6tObIQ4SfKlR-yveYd2HvNLG3ahKhnxb3fAA6H1oz3jqtR-aWLxtWyXixmm8U_9mu5_rSvgnSVqc0_LHd8dXKZALEYh5XTVvL6WuRhi1qfhDQTx7pKZEV8Cm1926ofH-HUPm3GvvBy71ZGokEgbKNo"/>
</div>
<div>
<p class="text-sm font-bold">@vanessa_luna</p>
<p class="text-xs text-on-surface-variant">Postado há 12 min</p>
</div>
</div>
<div class="mb-6">
<p class="text-xs font-bold text-primary uppercase tracking-widest mb-1">Motivo da Denúncia</p>
<p class="text-on-surface font-medium leading-relaxed">Violação dos Termos de Uso: Conteúdo sexualmente explícito não permitido nesta categoria.</p>
</div>
<div class="mt-auto flex items-center gap-3">
<button class="flex-1 bg-surface-container-high text-on-surface font-bold py-3 rounded-full hover:bg-surface-container-highest transition-all active:scale-95">Remover</button>
<button class="flex-1 bg-primary text-white font-bold py-3 rounded-full hover:bg-primary-container transition-all active:scale-95">Aprovar</button>
</div>
</div>
</div>
<!-- Card 2 -->
<div class="bg-surface-container-lowest rounded-lg shadow-sm overflow-hidden flex flex-col group transition-transform hover:-translate-y-1">
<div class="relative h-64 overflow-hidden">
<img alt="Reported Content" class="w-full h-full object-cover" data-alt="Modern abstract design with dark textures" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBqBADPkqkzeN9DREqbw1pit6u6S6C8FqimQtDlAOJvi847XHJigL4a5qNZbBjU4tCf3LgzePMY4fCvfMgGtiMJ_VGsU9P3EOxR8oiXccT4Wsv4A-6D2LZCj3g7sDxvqPQuirns5FI50pnrmS6kjt6h8WWs8ZChPcphH8Ec44-cZfB7DpXtx7IVxAGNepfNF57w3v5BxWrfhNDAahiCinkDeXP7GFCJNJ2jIsQD6R-in6mS-Ljkr-nz8aF4GkBjoZbk-5NHJAOMoM4"/>
<div class="absolute top-4 left-4">
<span class="bg-secondary text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">Spam</span>
</div>
</div>
<div class="p-6 flex-1 flex flex-col">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-full overflow-hidden border-2 border-primary-container">
<img alt="User Profile" class="w-full h-full object-cover" data-alt="Portrait of a male user" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBAg0FmRAGhZdOMzV1uROqk66yim0B8G5ZBeIKWctk0X9yQM64j13yAPq6VJFe8rzk3mP2JpzfUaNlVfKgbDxrG6LkBgMSYB5tO9pRwJ2OkKDieTal5Ygj9C_slvZJtFXSUw006_NyJW0KNUyVRnDRlg6yjIh4y63xVaHiwoC5qHg_4gG2zFqqN5I-fa2IeX6nSytHf7HmdfoWoam_cZpUF-ie5-usbv3p0Ntlvqte6kUGFY_1RlnnR4ok9R-k91RHVe4fco2R8HA8"/>
</div>
<div>
<p class="text-sm font-bold">@marcos_vinicius</p>
<p class="text-xs text-on-surface-variant">Postado há 28 min</p>
</div>
</div>
<div class="mb-6">
<p class="text-xs font-bold text-primary uppercase tracking-widest mb-1">Motivo da Denúncia</p>
<p class="text-on-surface font-medium leading-relaxed">Publicidade em massa. Vários posts idênticos detectados pelo sistema em curto intervalo.</p>
</div>
<div class="mt-auto flex items-center gap-3">
<button class="flex-1 bg-surface-container-high text-on-surface font-bold py-3 rounded-full hover:bg-surface-container-highest transition-all active:scale-95">Remover</button>
<button class="flex-1 bg-primary text-white font-bold py-3 rounded-full hover:bg-primary-container transition-all active:scale-95">Aprovar</button>
</div>
</div>
</div>
<!-- Card 3 -->
<div class="bg-surface-container-lowest rounded-lg shadow-sm overflow-hidden flex flex-col group transition-transform hover:-translate-y-1">
<div class="relative h-64 overflow-hidden">
<img alt="Reported Content" class="w-full h-full object-cover" data-alt="Portrait of a woman posing elegantly" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDDVMYXrnjyO3S4e_hQITobEp2IZh1B_arpUpqHKXyx-u8facI-COCOiChAtVQz0I6qdBBa5Mz-UsyzzQOi65My8CNNo62QiPj53sqprVpdTfnfWk4S2pFEdZJvOIcaIOIiQL-YVtGymIUJnOr_BUpPjri8i1dIxkP2P5EVjIX25hH3xMTMx1f4onpQD7vfTrkcWt3_tMPNtEoOA0sz9W88d4wYYxQiOMcWUqjeTevgQq97zrXYb88ImmxYPovjsVx5DXr2pbL3avI"/>
<div class="absolute top-4 left-4">
<span class="bg-tertiary text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">Outros</span>
</div>
</div>
<div class="p-6 flex-1 flex flex-col">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-full overflow-hidden border-2 border-primary-container">
<img alt="User Profile" class="w-full h-full object-cover" data-alt="Close-up portrait of a woman" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA53VdZVrMOMiXGeUsx9Fp0Wlidu611wKgvJLNAMmWJwm9179gvrWOrszeC6boDOISgdxsocMdYUpaCR60LJWppEcCQzHWr2bnqNEj_6gV3ffcLBWI8AyaUJWkKXb-wMdJWR3NS1KY4TwCVEU5kwlE1t4SEgnylUo9fII2lA5joAS_lNVbVRmbBZ2sxSxwMnWbB3y1MU2CxtaCGksVd41XHiH-bKoISuPM5tm_nnqEBPvKCIYWSZy02h0mnuWNR5xY59Q6ebexl2CE"/>
</div>
<div>
<p class="text-sm font-bold">@eliza_model</p>
<p class="text-xs text-on-surface-variant">Postado há 45 min</p>
</div>
</div>
<div class="mb-6">
<p class="text-xs font-bold text-primary uppercase tracking-widest mb-1">Motivo da Denúncia</p>
<p class="text-on-surface font-medium leading-relaxed">Direitos autorais. O denunciante afirma ser o proprietário legal da imagem original.</p>
</div>
<div class="mt-auto flex items-center gap-3">
<button class="flex-1 bg-surface-container-high text-on-surface font-bold py-3 rounded-full hover:bg-surface-container-highest transition-all active:scale-95">Remover</button>
<button class="flex-1 bg-primary text-white font-bold py-3 rounded-full hover:bg-primary-container transition-all active:scale-95">Aprovar</button>
</div>
</div>
</div>
<!-- Card 4 (Horizontal Bento Style) -->
<div class="lg:col-span-2 bg-surface-container-lowest rounded-lg shadow-sm overflow-hidden flex flex-col md:flex-row group transition-transform hover:-translate-y-1">
<div class="relative w-full md:w-1/2 h-64 md:h-auto overflow-hidden">
<img alt="Reported Content" class="w-full h-full object-cover" data-alt="Full body shot of a man in urban fashion" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCuYLVCcL2dlP02Q03ZwzAiUfjxDvDbivjqIkJU9av5DLnSt1k2ZtaOXJ-JwxkzVEAyr1oAgjRj19kFDgbXXZZ13yYmb7Q3FwfRhsOCfXHEZZCFvqlVnvJdDLpn240UQXiYHQqmu0ettsl4nhlXF3czSBziEJJTopcq-42Xaf3rMN1z52j-ihiif5XRI5vzRXcxsR4gU6K-PVVZ5b3_oBGubt2uyqxOmWRt2MCHv5-oi5_K1wQQURqaoSA0vpLfV4wuvO46EyOIuUY"/>
<div class="absolute top-4 left-4">
<span class="bg-error text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">Falsificação de Identidade</span>
</div>
</div>
<div class="p-8 flex-1 flex flex-col justify-center">
<div class="flex items-center gap-4 mb-6">
<div class="w-14 h-14 rounded-full overflow-hidden border-4 border-primary-container/30">
<img alt="User Profile" class="w-full h-full object-cover" data-alt="Portrait of a smiling man" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA5lJPAgw2fXyiQrUStm1pMCYC8T5x5CsNNt2-AJ52VzYe_BP2eKjjZXN0zZrO1L4sFbMFThPtZm9C3Z6doGM3ZoWb5HXN_7Rv6uIEW-F3yheL1YRK4gd0NPVpSB6FJulFEb97OY2M-F_3g21feqWBoczccs4DZWRIRYGUib0n4PN5niLaHhSO00oYdVn69wK9jT6UVtwKdd7F9A58576tnTvsRTW54JZCs6WA4wnnrav8W4wWbNk4HzpV51_NTysxX9ikmYX3aaUs"/>
</div>
<div>
<p class="text-lg font-bold">@felipe_oficial_fake</p>
<p class="text-sm text-on-surface-variant">Postado há 1 hora</p>
</div>
</div>
<div class="mb-8">
<p class="text-xs font-bold text-primary uppercase tracking-widest mb-1">Análise do Sistema</p>
<p class="text-on-surface font-medium leading-relaxed text-lg">Este usuário está se passando por uma figura pública. A conta foi criada há menos de 24 horas e utiliza fotos de terceiros sem permissão.</p>
</div>
<div class="flex items-center gap-4">
<button class="px-8 bg-surface-container-high text-on-surface font-bold py-4 rounded-full hover:bg-surface-container-highest transition-all active:scale-95">Rejeitar Denúncia</button>
<button class="flex-1 bg-error text-white font-bold py-4 rounded-full hover:bg-red-700 transition-all active:scale-95 shadow-lg shadow-error/20">Banir Conta Imediatamente</button>
</div>
</div>
</div>
<!-- Card 5 (Info card) -->
<div class="bg-primary-container text-white rounded-lg p-8 flex flex-col justify-between relative overflow-hidden">
<div class="relative z-10">
<span class="material-symbols-outlined text-5xl mb-6 opacity-80" data-icon="policy">policy</span>
<h3 class="text-2xl font-bold font-headline mb-4">Atualização de Diretrizes</h3>
<p class="text-on-primary-container/80 font-medium leading-relaxed">
                        Revisamos as regras para conteúdo de 'Lifestyle' e 'Moda Praia'. Certifique-se de aplicar as novas métricas de exposição antes de aprovar.
                    </p>
</div>
<button class="mt-8 bg-white text-primary font-bold py-3 rounded-full hover:bg-pink-50 transition-all active:scale-95 relative z-10">
                    Ler Novas Regras
                </button>
<!-- Decorative Circle -->
<div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
</div>
</div>
<!-- Floating Action Button (FAB) - Suppression Logic Applied (Suppressed because focused task) -->
<!-- No FAB rendered here as per "Shell Visibility & Relevance" rules for specific tasks -->
</main>
<!-- Footer Space -->
<footer class="ml-64 px-8 py-8 border-t border-outline-variant/10 text-on-surface-variant text-sm flex justify-between">
<p>© 2024 SexyLua Admin Ecosystem. Todos os direitos reservados.</p>
<div class="flex gap-6">
<a class="hover:text-primary transition-colors" href="#">Privacidade</a>
<a class="hover:text-primary transition-colors" href="#">Suporte Técnico</a>
</div>
</footer>
</body></html>