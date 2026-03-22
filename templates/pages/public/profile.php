<!DOCTYPE html>

<html lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;1,800&amp;family=Manrope:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "surface-container": "#efedef",
              "secondary": "#ab2c5d",
              "inverse-surface": "#303032",
              "surface-bright": "#fbf9fb",
              "surface-container-highest": "#e3e2e4",
              "secondary-container": "#fd6c9c",
              "on-error-container": "#93000a",
              "on-tertiary-fixed": "#280056",
              "on-primary": "#ffffff",
              "tertiary-fixed-dim": "#d7baff",
              "on-secondary-container": "#6e0034",
              "on-surface-variant": "#5a4044",
              "on-tertiary-container": "#fcf3ff",
              "on-secondary": "#ffffff",
              "on-background": "#1b1c1d",
              "surface-container-high": "#e9e7e9",
              "surface": "#fbf9fb",
              "on-secondary-fixed-variant": "#8b0e45",
              "inverse-primary": "#ffb1c5",
              "on-primary-fixed": "#3f001a",
              "surface-variant": "#e3e2e4",
              "on-tertiary-fixed-variant": "#5a2a9c",
              "on-secondary-fixed": "#3f001b",
              "error-container": "#ffdad6",
              "tertiary-fixed": "#eddcff",
              "on-surface": "#1b1c1d",
              "tertiary": "#6c3eaf",
              "error": "#ba1a1a",
              "outline": "#8e6f74",
              "on-error": "#ffffff",
              "tertiary-container": "#8658ca",
              "surface-tint": "#b41b5c",
              "on-primary-container": "#fff2f4",
              "inverse-on-surface": "#f2f0f2",
              "primary-fixed": "#ffd9e1",
              "surface-dim": "#dbd9db",
              "surface-container-low": "#f5f3f5",
              "primary-container": "#cc326e",
              "primary-fixed-dim": "#ffb1c5",
              "background": "#fbf9fb",
              "surface-container-lowest": "#ffffff",
              "on-tertiary": "#ffffff",
              "secondary-fixed": "#ffd9e1",
              "primary": "#ab1155",
              "secondary-fixed-dim": "#ffb1c5",
              "outline-variant": "#e3bdc3",
              "on-primary-fixed-variant": "#8f0045"
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
        .lunar-blur {
            backdrop-filter: blur(24px);
        }
        body {
            font-family: 'Manrope', sans-serif;
            background-color: #fbf9fb;
        }
    </style>
</head>
<body class="text-on-background selection:bg-primary-fixed selection:text-on-primary-fixed">
<!-- TopNavBar -->
<nav class="fixed top-0 w-full z-50 flex justify-between items-center px-8 h-20 bg-[#D81B60] shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
<div class="text-2xl font-black text-white italic tracking-tighter">SexyLua</div>
<div class="hidden md:flex gap-8 items-center">
<a class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white/80 hover:text-white transition-colors" href="#">Live Cam</a>
<a class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white/80 hover:text-white transition-colors" href="#">Explorar</a>
<a class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white border-b-2 border-white pb-1" href="#">Assinaturas</a>
<a class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white/80 hover:text-white transition-colors" href="#">Mensagens</a>
</div>
<div class="flex gap-4 items-center">
<button class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white/80 hover:scale-105 transition-transform duration-200">Login</button>
<button class="bg-white text-primary px-6 py-2 rounded-full font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold hover:scale-105 transition-transform duration-200">Registro</button>
</div>
</nav>
<main class="pt-20 min-h-screen">
<!-- Hero Banner Section -->
<div class="relative w-full h-[460px] bg-surface-container-high overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Abstract cinematic pink and purple moonlight landscape" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCtH6SGji4tKk0jH3Z-XYQ_FG4Z6glEPaFfksO0EPrhPINihx4CzLBw_9GCsABWLmwYV_O1Xl78CqHCczDRiXCf7ysnG1iP0drdTT1DVz_WxY2vss3ksK374ARdaKaqq3-4dMHlKlwJpXfc50HXAR7p0FNHgzSRwMnIEX00Aumv2MCOV1Eb4mOBXWw2rNsW4tl5w8Jz97Mfuh9j4EafY_cBFlnQscSP61BxBwRBycv7k2lqoswGquOahJI8H-enZ0TAb_d5Y4Jl8Aw"/>
<div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent"></div>
<!-- Floating Moon Elements (Background Decor) -->
<div class="absolute top-10 right-20 w-32 h-32 bg-primary/10 rounded-full blur-3xl"></div>
<div class="absolute bottom-20 left-10 w-48 h-48 bg-secondary/5 rounded-full blur-2xl"></div>
</div>
<!-- Profile Header Area -->
<div class="max-w-7xl mx-auto px-8 -mt-32 relative z-10">
<div class="flex flex-col md:flex-row items-end gap-8 mb-12">
<!-- Avatar -->
<div class="relative">
<div class="p-1 bg-background rounded-full">
<img class="w-48 h-48 rounded-full border-4 border-surface-container-lowest shadow-xl object-cover" data-alt="Close up portrait of Maria Silva smiling softly" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDuJPAxgz4VR7T8ohj6geBS0a1uNpV8LRXK3OSA3i_P18dqd0vgDXDnLprgG6sjjivJK_kXbLqEQtHYxRivkzR3xqmbMD7b2iExze_z4ohs_tFBSxx-TFy4gbPlH2cejYwsJIS3PUtwV_ZSY1ZP-X88Sbn-gr1by8IZAvycVJg44OvdGjQKYAktRMHqHvu86RJ4VOK8iMe0gJkk93ITWkfEHn2Jcj92hV2V9yC4hWTcYcRtAcPk8YfaYTgWxlGHEHKAlZSqspqRvgw"/>
</div>
<div class="absolute bottom-4 right-4 bg-green-500 w-6 h-6 rounded-full border-4 border-background"></div>
</div>
<!-- Info -->
<div class="flex-1 pb-4">
<h1 class="font-headline text-5xl font-extrabold tracking-tight text-on-surface mb-2">Maria Silva</h1>
<p class="font-body text-lg text-on-surface-variant flex items-center gap-2">
                        @maria_lunar 
                        <span class="material-symbols-outlined text-primary text-xl" style="font-variation-settings: 'FILL' 1;">verified</span>
</p>
</div>
<!-- Actions -->
<div class="flex gap-4 pb-4">
<button class="p-4 rounded-full bg-surface-container-low text-on-surface hover:scale-105 transition-all">
<span class="material-symbols-outlined">share</span>
</button>
<button class="bg-primary text-on-primary px-10 py-4 rounded-full font-headline font-bold text-lg hover:bg-primary-container hover:scale-105 transition-all shadow-lg">
                        Assinar Agora
                    </button>
</div>
</div>
<!-- Bento Layout Content -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-20">
<!-- Main Feed Area (8 columns) -->
<div class="lg:col-span-8 space-y-8">
<!-- Bio Card -->
<div class="bg-surface-container-lowest p-10 rounded-lg shadow-sm">
<h2 class="font-headline text-2xl font-bold mb-6 text-primary flex items-center gap-2">
<span class="material-symbols-outlined">nightlight</span> O Despertar Lunar
                        </h2>
<p class="font-body text-xl leading-relaxed text-on-surface-variant mb-6 italic">
                            "Bem-vindo ao meu santuário privado. Aqui, as fases da lua ditam o ritmo dos nossos segredos. Sou a Maria, e convido você a explorar o que acontece quando as luzes se apagam e o desejo floresce."
                        </p>
<div class="flex gap-4 flex-wrap">
<span class="px-4 py-2 bg-surface-container-low rounded-full text-sm font-medium text-on-surface-variant">#Exclusivo</span>
<span class="px-4 py-2 bg-surface-container-low rounded-full text-sm font-medium text-on-surface-variant">#Sensual</span>
<span class="px-4 py-2 bg-surface-container-low rounded-full text-sm font-medium text-on-surface-variant">#SemFiltro</span>
</div>
</div>
<!-- Content Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<!-- Locked Content Card 1 -->
<div class="group relative aspect-[3/4] rounded-lg overflow-hidden bg-surface-container-highest">
<img class="w-full h-full object-cover blur-xl opacity-50 group-hover:scale-110 transition-transform duration-700" data-alt="Blurred artistic silhouette of a woman" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDR2f0TWMQXavdA-EFX7J5yfUZJj5JOyyD866cmH9t7wQnkt94zmqeaQXPPASQU2VsVowl5esCJNyfJh-QQKx1jeriWdhFkuBpjoBiP31uB8Sk8GCpDYg5Cz6dfcXHWd47FRrDyz7fvbwbWx5Xnw19Z3-OfivDNBrVdNZ3-sPw9g69c_3KD5IcVQLULtbJBUdw17Z0YdxSL43RnNX2BbqdmnsYI0RmcXqFBwwVVGPvu8bIo1WQTxYJXbCCFrfJ3fWREbha6WcVkYjs"/>
<div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center p-8 text-center">
<span class="material-symbols-outlined text-white text-5xl mb-4" style="font-variation-settings: 'FILL' 1;">lock</span>
<h3 class="text-white font-headline font-bold text-xl mb-2">Conteúdo Proibido</h3>
<p class="text-white/80 text-sm mb-6">Assine para me ver sem segredos e descobrir o que preparei nesta noite de lua cheia.</p>
<button class="bg-white text-primary px-6 py-2 rounded-full font-bold text-sm uppercase tracking-wider">Desbloquear</button>
</div>
<div class="absolute top-4 left-4 bg-primary text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-tighter">Premium</div>
</div>
<!-- Locked Content Card 2 -->
<div class="group relative aspect-[3/4] rounded-lg overflow-hidden bg-surface-container-highest">
<img class="w-full h-full object-cover blur-md opacity-40 group-hover:scale-110 transition-transform duration-700" data-alt="Abstract blurred textures of satin and skin" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC3qlvGh7lGHxvPIWwJvRUeJBvoXaqLmGgqf3aknQ2bQTSUdx3OVbHJVyO2yTkZ7tQ_WffCGjJBoOsFDu3rmEho_XomZZUety1auUHHSeVClkRYr0orXkclf95TgNXvMooXpnbQ97LpFbXVHdp8anbv3IPR4Qc9T_SHM9DhxCUmpZTZkTjrzIu2JlL6nLEzm6T3os-q7X4RAiXK7yP1v_VXjCzFkWQfLuRQ5nl4TVI2VNpVnjelppN4ghYBBAimrE8p7dMi6HtuL8k"/>
<div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center p-8 text-center">
<span class="material-symbols-outlined text-white text-5xl mb-4" style="font-variation-settings: 'FILL' 1;">movie</span>
<h3 class="text-white font-headline font-bold text-xl mb-2">Bastidores Lunares</h3>
<p class="text-white/80 text-sm mb-6">Um vídeo íntimo de 5 minutos sobre minha última sessão de fotos editorial.</p>
<button class="bg-white text-primary px-6 py-2 rounded-full font-bold text-sm uppercase tracking-wider">Ver Vídeo</button>
</div>
</div>
<!-- Locked Content Card 3 -->
<div class="group relative aspect-square md:col-span-2 rounded-lg overflow-hidden bg-surface-container-highest">
<img class="w-full h-full object-cover blur-2xl opacity-30" data-alt="Very blurred artistic photo of a model in red lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuADG7X7kXHb5YrKdVsTVtpHH1qczWuWK-LVVIHPFheqcPkcRZIgYu7oO-yFP9quPz-H3BEc_3FfvLgBIa4_jzUCSVFxRhkKgBZANtMLCmR_69G_cKGLsIKQb9hM_NNEV__tBWeaYc5I05dg1cD86pGA99b9HXs35auobwWkJ-BGUUxadX_Is6cTyXmgpg4tIuYdJcEjxgVSwJBCRV50bHFouiJaapA-z46bg0ByCVwpWq6UzjXMUfDtkgOCkqJBg2bm6N5szTHg_gE"/>
<div class="absolute inset-0 bg-gradient-to-tr from-primary/60 to-transparent flex flex-col items-center justify-center p-12 text-center">
<div class="bg-white/10 lunar-blur p-8 rounded-xl border border-white/20">
<span class="material-symbols-outlined text-white text-6xl mb-4" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
<h3 class="text-white font-headline font-extrabold text-3xl mb-4 italic">Coleção Eclipse</h3>
<p class="text-white/90 text-lg mb-8 max-w-md mx-auto">24 fotos em alta definição capturando a metamorfose mais intensa da minha carreira.</p>
<button class="bg-primary-container text-white px-10 py-4 rounded-full font-bold text-lg hover:scale-105 transition-all shadow-xl">Assine para Ver</button>
</div>
</div>
</div>
</div>
</div>
<!-- Sidebar Area (4 columns) -->
<div class="lg:col-span-4 space-y-8">
<!-- Metrics Card -->
<div class="bg-surface-container-low p-8 rounded-lg">
<h3 class="font-headline text-lg font-bold mb-6 text-on-surface uppercase tracking-widest">Métricas Lunares</h3>
<div class="grid grid-cols-2 gap-4">
<div class="bg-surface-container-lowest p-4 rounded-md">
<p class="text-xs text-on-surface-variant uppercase font-bold mb-1 tracking-tight">Fotos</p>
<p class="text-2xl font-headline font-black text-primary">142</p>
</div>
<div class="bg-surface-container-lowest p-4 rounded-md">
<p class="text-xs text-on-surface-variant uppercase font-bold mb-1 tracking-tight">Vídeos</p>
<p class="text-2xl font-headline font-black text-primary">38</p>
</div>
<div class="bg-surface-container-lowest p-4 rounded-md">
<p class="text-xs text-on-surface-variant uppercase font-bold mb-1 tracking-tight">Likes</p>
<p class="text-2xl font-headline font-black text-primary">12.4k</p>
</div>
<div class="bg-surface-container-lowest p-4 rounded-md">
<p class="text-xs text-on-surface-variant uppercase font-bold mb-1 tracking-tight">Fãs</p>
<p class="text-2xl font-headline font-black text-primary">850</p>
</div>
</div>
</div>
<!-- Mood Card -->
<div class="bg-primary p-8 rounded-lg text-on-primary relative overflow-hidden">
<div class="relative z-10">
<h3 class="font-headline text-lg font-bold mb-4 uppercase tracking-widest">Lunar Mood</h3>
<div class="flex items-center gap-4 mb-4">
<span class="material-symbols-outlined text-4xl">brightness_2</span>
<div>
<p class="font-bold text-xl italic">Crescente e Ousada</p>
<p class="text-sm text-white/80">Disponível para chat privado até as 02:00</p>
</div>
</div>
<button class="w-full bg-white text-primary py-3 rounded-full font-bold uppercase text-sm tracking-widest hover:bg-on-primary-container transition-colors">
                                Enviar Mensagem
                            </button>
</div>
<div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full"></div>
</div>
<!-- Moon Phase Visualizer -->
<div class="bg-surface-container-lowest p-8 rounded-lg shadow-sm">
<h3 class="font-headline text-sm font-bold mb-6 text-on-surface-variant uppercase tracking-widest">Fase da Lua Atual</h3>
<div class="flex justify-between items-center px-2">
<div class="flex flex-col items-center gap-2 opacity-30">
<span class="material-symbols-outlined text-2xl">circle</span>
<span class="text-[10px] font-bold">Nova</span>
</div>
<div class="flex flex-col items-center gap-2">
<span class="material-symbols-outlined text-4xl text-primary" style="font-variation-settings: 'FILL' 1;">brightness_3</span>
<span class="text-[10px] font-bold text-primary">Crescente</span>
</div>
<div class="flex flex-col items-center gap-2 opacity-30">
<span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">circle</span>
<span class="text-[10px] font-bold">Cheia</span>
</div>
<div class="flex flex-col items-center gap-2 opacity-30">
<span class="material-symbols-outlined text-2xl">brightness_2</span>
<span class="text-[10px] font-bold">Minguante</span>
</div>
</div>
</div>
</div>
</div>
</div>
</main>
<!-- Footer -->
<footer class="w-full flex flex-col items-center gap-6 px-10 py-12 bg-[#D81B60] text-white">
<div class="text-lg font-bold text-white">SexyLua</div>
<div class="flex gap-8">
<a class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70 hover:text-white transition-all duration-300" href="/terms">Termos</a>
<a class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70 hover:text-white transition-all duration-300" href="/privacy">Privacidade</a>
<a class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70 hover:text-white transition-all duration-300" href="/help">Ajuda</a>
<a class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70 hover:text-white transition-all duration-300" href="#">Carreiras</a>
</div>
<div class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70">
            © 2024 SexyLua. The Lunar Metamorphosis.
        </div>
</footer>
<!-- FAB (Suppressed based on rules for Details/Profile page if not matching primary action, 
         but here we use it as a 'Return Top' or 'Chat' if necessary. Suppressing per instructions) -->
</body></html>
