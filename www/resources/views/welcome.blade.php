<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Beyond MRP | Transforme sua operacao com automacao inteligente</title>
  <meta name="description" content="Centralize operacoes, elimine retrabalho e ganhe visibilidade em tempo real com uma plataforma SaaS enterprise. Teste gratis por 14 dias, sem cartao de credito." />
  <meta property="og:type" content="website" />
  <meta property="og:locale" content="pt_BR" />
  <meta property="og:title" content="Beyond MRP | Controle total da operacao em uma unica plataforma" />
  <meta property="og:description" content="Automatize workflows, reduza custos e aumente produtividade com dashboards em tempo real. Inicie seu teste gratis por 14 dias." />
  <meta property="og:image" content="https://images.unsplash.com/photo-1551281044-8d8d7a58f0f6?auto=format&fit=crop&w=1600&q=80" />
  <meta property="og:url" content="https://beyondmrp.com/" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="theme-color" content="#0A1224" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root {
      --grad-hero: radial-gradient(circle at 8% 10%, rgba(0, 179, 255, 0.25), transparent 42%),
                   radial-gradient(circle at 84% 12%, rgba(25, 216, 157, 0.20), transparent 36%),
                   linear-gradient(120deg, #081021 0%, #101d3a 58%, #0b1832 100%);
      --grad-panel: linear-gradient(140deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.03));
      --ring: 0 0 0 3px rgba(0, 179, 255, 0.25);
    }

    body {
      font-family: "Manrope", sans-serif;
      background: #f3f7fc;
      color: #0f172a;
      text-rendering: geometricPrecision;
    }

    h1, h2, h3, h4 {
      font-family: "Space Grotesk", sans-serif;
      letter-spacing: -0.02em;
    }

    .noise-bg::before {
      content: "";
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(255, 255, 255, 0.15) 0.5px, transparent 0.5px);
      background-size: 4px 4px;
      opacity: 0.15;
      pointer-events: none;
    }

    .glass {
      background: var(--grad-panel);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.16);
    }

    .reveal {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 700ms ease, transform 700ms ease;
    }

    .reveal.on {
      opacity: 1;
      transform: translateY(0);
    }

    .cta-focus:focus-visible,
    .faq-btn:focus-visible {
      outline: none;
      box-shadow: var(--ring);
      border-radius: 999px;
    }

    .section-pattern {
      background-image:
        radial-gradient(circle at 2px 2px, rgba(15, 23, 42, 0.08) 1px, transparent 0),
        linear-gradient(180deg, rgba(248, 250, 252, 1), rgba(236, 245, 255, 1));
      background-size: 24px 24px, 100% 100%;
    }
  </style>
</head>

<body class="antialiased">
  <header class="bg-night text-white relative overflow-hidden noise-bg">
    <div class="absolute inset-0" style="background: var(--grad-hero);"></div>

    <nav class="relative max-w-7xl mx-auto px-6 lg:px-10 py-6 flex items-center justify-between z-20">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center">
          <svg class="w-6 h-6 text-skycore" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 5h7v6H4V5Zm9 0h7v14h-7V5ZM4 13h7v6H4v-6Z" fill="currentColor"></path>
          </svg>
        </div>
        <div>
          <p class="font-display text-lg font-bold">Beyond MRP</p>
          <p class="text-xs text-mist/80">Enterprise Operations SaaS</p>
        </div>
      </div>

      <div class="hidden md:flex items-center gap-7 text-sm text-mist/90">
        <a href="#beneficios" class="hover:text-white transition">Beneficios</a>
        <a href="#recursos" class="hover:text-white transition">Recursos</a>
        <a href="#depoimentos" class="hover:text-white transition">Resultados</a>
        <a href="#faq" class="hover:text-white transition">FAQ</a>
      </div>

      <a href="{{ route('start-trial') }}" class="cta-focus hidden sm:inline-flex px-5 py-3 rounded-full bg-coral font-semibold text-sm hover:brightness-110 transition shadow-soft">TESTE GRATIS POR 14 DIAS</a>
    </nav>

    <section class="relative max-w-7xl mx-auto px-6 lg:px-10 pb-20 pt-8 lg:pt-16 z-20">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-8 reveal">
          <span class="inline-flex items-center px-4 py-2 rounded-full bg-white/10 border border-white/20 text-xs font-semibold tracking-wide text-mist">
            Plataforma de alta performance para operacoes criticas
          </span>

          <div class="space-y-5">
            <h1 class="text-4xl md:text-5xl xl:text-6xl font-bold leading-tight">
              Transforme sua operacao com automacao inteligente e controle total em tempo real.
            </h1>
            <p class="text-lg text-mist/95 max-w-xl leading-relaxed">
              Elimine processos manuais, reduza custos ocultos e acelere decisoes com dashboards executivos, workflows inteligentes e rastreabilidade ponta a ponta em uma unica plataforma.
            </p>
          </div>

          <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('start-trial') }}" class="cta-focus inline-flex justify-center items-center px-7 py-4 rounded-full bg-coral text-white font-bold text-sm tracking-wide hover:brightness-110 transition shadow-glow">TESTE GRATIS POR 14 DIAS</a>
            <a href="#produto-visual" class="inline-flex justify-center items-center px-7 py-4 rounded-full border border-white/30 text-white font-semibold text-sm hover:bg-white/10 transition">Ver produto em acao</a>
          </div>

          <div class="grid sm:grid-cols-3 gap-3 text-sm text-mist/90">
            <div class="glass rounded-2xl px-4 py-3">Sem cartao de credito</div>
            <div class="glass rounded-2xl px-4 py-3">Cancelamento simples</div>
            <div class="glass rounded-2xl px-4 py-3">Onboarding rapido</div>
          </div>

          <div class="flex flex-wrap gap-x-8 gap-y-3 items-center text-xs text-mist/80 pt-2">
            <span class="uppercase tracking-widest">Confiado por equipes de alta exigencia</span>
            <span>Atlas Industrial</span>
            <span>NovaMec Group</span>
            <span>PrimeForge</span>
            <span>Helix Logistics</span>
          </div>
        </div>

        <div id="produto-visual" class="relative reveal">
          <div class="absolute -top-8 -left-8 w-32 h-32 rounded-full bg-skycore/25 blur-2xl animate-pulseSoft"></div>
          <div class="absolute -bottom-8 -right-8 w-32 h-32 rounded-full bg-emeraldcore/25 blur-2xl animate-pulseSoft"></div>

          <div class="glass rounded-3xl p-5 shadow-glow animate-float">
            <div class="rounded-2xl bg-slate-950/80 border border-white/10 p-4">
              <div class="flex items-center justify-between text-xs text-mist/80 mb-4">
                <span>Dashboard Operacional</span>
                <span class="px-2 py-1 rounded-full bg-emeraldcore/20 text-emeraldcore">SLA: 98.7%</span>
              </div>
              <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="rounded-xl bg-white/5 border border-white/10 p-3">
                  <p class="text-xs text-mist/80">Ordens concluidas</p>
                  <p class="text-2xl font-bold text-white mt-1" data-counter="1842">0</p>
                </div>
                <div class="rounded-xl bg-white/5 border border-white/10 p-3">
                  <p class="text-xs text-mist/80">Lead time medio</p>
                  <p class="text-2xl font-bold text-white mt-1">-37%</p>
                </div>
              </div>
              <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                <p class="text-xs text-mist/80 mb-3">Efetividade por celula</p>
                <div class="space-y-2">
                  <div>
                    <div class="h-2 rounded-full bg-white/10 overflow-hidden"><div class="h-full w-[86%] bg-skycore rounded-full"></div></div>
                  </div>
                  <div>
                    <div class="h-2 rounded-full bg-white/10 overflow-hidden"><div class="h-full w-[72%] bg-emeraldcore rounded-full"></div></div>
                  </div>
                  <div>
                    <div class="h-2 rounded-full bg-white/10 overflow-hidden"><div class="h-full w-[93%] bg-coral rounded-full"></div></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </header>

  <main>
    <section class="section-pattern py-20 lg:py-24">
      <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-3xl reveal">
          <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-900 text-white">Dores que travam o crescimento</p>
          <h2 class="text-3xl md:text-4xl font-bold text-graphite mt-5">Se a operacao depende de planilhas e processos manuais, voce esta perdendo margem todos os dias.</h2>
          <p class="mt-4 text-slate-600 text-lg">Retrabalho, erros humanos e falta de visibilidade criam atrasos, elevam custos e impedem sua equipe de escalar com confianca.</p>
        </div>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <article class="reveal rounded-2xl bg-white p-6 shadow-soft border border-slate-100"><h3 class="font-display text-lg">Processos manuais e lentos</h3><p class="mt-2 text-slate-600">Seu time gasta horas em tarefas repetitivas que poderiam ser automatizadas.</p></article>
          <article class="reveal rounded-2xl bg-white p-6 shadow-soft border border-slate-100"><h3 class="font-display text-lg">Retrabalho e erros recorrentes</h3><p class="mt-2 text-slate-600">Falta de padronizacao gera falhas que consomem recursos e comprometem prazos.</p></article>
          <article class="reveal rounded-2xl bg-white p-6 shadow-soft border border-slate-100"><h3 class="font-display text-lg">Rastreabilidade fraca</h3><p class="mt-2 text-slate-600">Dificuldade para auditar eventos e identificar rapidamente causa raiz.</p></article>
          <article class="reveal rounded-2xl bg-white p-6 shadow-soft border border-slate-100"><h3 class="font-display text-lg">Baixa produtividade operacional</h3><p class="mt-2 text-slate-600">Equipes sobrecarregadas sem fluxo de trabalho inteligente e integrado.</p></article>
          <article class="reveal rounded-2xl bg-white p-6 shadow-soft border border-slate-100"><h3 class="font-display text-lg">Integracoes complexas</h3><p class="mt-2 text-slate-600">Sistemas desconectados aumentam friccao e risco de perda de informacao.</p></article>
          <article class="reveal rounded-2xl bg-white p-6 shadow-soft border border-slate-100"><h3 class="font-display text-lg">Custos operacionais altos</h3><p class="mt-2 text-slate-600">Ineficiencia acumulada reduz lucro e dificulta crescimento sustentavel.</p></article>
        </div>
      </div>
    </section>

    <section id="beneficios" class="py-20 lg:py-24 bg-white">
      <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-3xl reveal">
          <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800">Beneficios principais</p>
          <h2 class="text-3xl md:text-4xl font-bold text-graphite mt-5">Ganhos imediatos em produtividade, controle e margem operacional.</h2>
          <p class="mt-4 text-slate-600 text-lg">Projetado para empresas que precisam de execucao impecavel com menos custo e mais previsibilidade.</p>
        </div>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <article class="reveal rounded-2xl p-6 bg-slate-50 border border-slate-200 hover:shadow-soft transition">
            <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h3 class="mt-4 font-display text-xl">Automacao inteligente</h3>
            <p class="mt-2 text-slate-600">Elimine tarefas repetitivas com workflows que executam regras automaticamente.</p>
          </article>

          <article class="reveal rounded-2xl p-6 bg-slate-50 border border-slate-200 hover:shadow-soft transition">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M4 12h5l2-7 4 14 2-7h3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h3 class="mt-4 font-display text-xl">Produtividade escalavel</h3>
            <p class="mt-2 text-slate-600">Aumente a capacidade da equipe sem inflar estrutura ou aumentar complexidade.</p>
          </article>

          <article class="reveal rounded-2xl p-6 bg-slate-50 border border-slate-200 hover:shadow-soft transition">
            <div class="w-11 h-11 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M3 3v18h18M7 14l3-3 3 2 4-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h3 class="mt-4 font-display text-xl">Dashboards em tempo real</h3>
            <p class="mt-2 text-slate-600">Monitore indicadores criticos e antecipe gargalos antes de virar problema.</p>
          </article>

          <article class="reveal rounded-2xl p-6 bg-slate-50 border border-slate-200 hover:shadow-soft transition">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <h3 class="mt-4 font-display text-xl">Integracao sem friccao</h3>
            <p class="mt-2 text-slate-600">Conecte ERP, MES e sistemas legados com API robusta e documentada.</p>
          </article>

          <article class="reveal rounded-2xl p-6 bg-slate-50 border border-slate-200 hover:shadow-soft transition">
            <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M12 2l8 4v6c0 5.2-3.4 10-8 11-4.6-1-8-5.8-8-11V6l8-4Z" stroke="currentColor" stroke-width="2"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h3 class="mt-4 font-display text-xl">Seguranca enterprise</h3>
            <p class="mt-2 text-slate-600">Controle de acesso granular, logs auditaveis e governanca para ambientes criticos.</p>
          </article>

          <article class="reveal rounded-2xl p-6 bg-slate-50 border border-slate-200 hover:shadow-soft transition">
            <div class="w-11 h-11 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M8 7V4h8v3M4 10h16v10H4z" stroke="currentColor" stroke-width="2"/><path d="M12 14v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <h3 class="mt-4 font-display text-xl">Reducao de custos</h3>
            <p class="mt-2 text-slate-600">Centralize operacoes, reduza desperdicios e aumente retorno sobre cada processo.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="recursos" class="py-20 lg:py-24 bg-slate-900 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_85%_0%,rgba(0,179,255,.20),transparent_30%)]"></div>
      <div class="relative max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-3xl reveal">
          <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-white/10 border border-white/20 text-mist">Recursos completos para crescimento sustentavel</p>
          <h2 class="text-3xl md:text-4xl font-bold mt-5">Tudo que sua operacao precisa para executar melhor, com previsibilidade e escala.</h2>
        </div>

        <div class="mt-12 grid md:grid-cols-2 xl:grid-cols-3 gap-5">
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Gestao operacional unificada</h3><p class="text-mist/90 mt-2">Consolide dados de producao, estoque e qualidade em uma visao unica para decisoes mais rapidas.</p><p class="text-emeraldcore text-sm mt-3">Ganho: menos retrabalho e mais sincronismo entre equipes.</p></article>
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Workflows inteligentes</h3><p class="text-mist/90 mt-2">Automatize etapas criticas com regras e gatilhos por evento, prazo ou excecao operacional.</p><p class="text-emeraldcore text-sm mt-3">Ganho: execucao padronizada com alta confiabilidade.</p></article>
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Dashboards e analytics</h3><p class="text-mist/90 mt-2">Monitore KPIs em tempo real com visoes por unidade, turno, celula ou produto.</p><p class="text-emeraldcore text-sm mt-3">Ganho: decisao baseada em dados, nao em intuicao.</p></article>
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Notificacoes proativas</h3><p class="text-mist/90 mt-2">Alertas por anomalias, desvios de SLA e eventos de risco antes de impactar o cliente.</p><p class="text-emeraldcore text-sm mt-3">Ganho: resposta rapida e menor impacto operacional.</p></article>
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Integracao API + SSO</h3><p class="text-mist/90 mt-2">Conecte sistemas existentes e autentique com SSO para experiencia segura e fluida.</p><p class="text-emeraldcore text-sm mt-3">Ganho: implantacao simplificada e governanca centralizada.</p></article>
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Permissoes e multiusuario</h3><p class="text-mist/90 mt-2">Defina acesso por perfil, area, filial ou unidade, com segregacao robusta de responsabilidades.</p><p class="text-emeraldcore text-sm mt-3">Ganho: seguranca e compliance em escala enterprise.</p></article>
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Relatorios e auditoria</h3><p class="text-mist/90 mt-2">Registre cada alteracao com trilha auditavel para suporte a compliance e melhoria continua.</p><p class="text-emeraldcore text-sm mt-3">Ganho: rastreabilidade total para decisoes e investigacoes.</p></article>
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Monitoramento e observabilidade</h3><p class="text-mist/90 mt-2">Visibilidade de saude operacional e eventos criticos com indicadores acionaveis.</p><p class="text-emeraldcore text-sm mt-3">Ganho: estabilidade e previsibilidade operacional.</p></article>
          <article class="reveal glass rounded-2xl p-5"><h3 class="font-display text-xl">Multi-site e mobile responsive</h3><p class="text-mist/90 mt-2">Gerencie varias plantas e acompanhe a operacao em qualquer dispositivo com performance.</p><p class="text-emeraldcore text-sm mt-3">Ganho: controle central com execucao local eficiente.</p></article>
        </div>
      </div>
    </section>

    <section class="py-20 lg:py-24 bg-white">
      <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="grid lg:grid-cols-2 gap-8 items-center">
          <div class="reveal">
            <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-900 text-white">Sofisticacao visivel</p>
            <h2 class="text-3xl md:text-4xl font-bold mt-5 text-graphite">Telas modernas para lideranca e operacao trabalharem em sincronia.</h2>
            <p class="mt-4 text-slate-600 text-lg">Widgets operacionais, graficos executivos e indicadores em camadas para transformar dados em acao imediata.</p>
            <div class="mt-8 space-y-4 text-slate-700">
              <p>Mockups com foco em execucao real, nao apenas visual bonito.</p>
              <p>Paineis claros para times de operacao, engenharia e gestao.</p>
              <p>Estrutura preparada para crescimento de usuarios e unidades.</p>
            </div>
            <a href="{{ route('start-trial') }}" class="cta-focus mt-8 inline-flex px-7 py-4 rounded-full bg-coral text-white font-bold text-sm hover:brightness-110 transition">TESTE GRATIS POR 14 DIAS</a>
          </div>

          <div class="grid gap-4 reveal">
            <div class="rounded-2xl border border-slate-200 shadow-soft p-5 bg-slate-50">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-lg">Indicadores em tempo real</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">Atualizado ha 12s</span>
              </div>
              <div class="grid sm:grid-cols-3 gap-3">
                <div class="rounded-xl p-3 bg-white border border-slate-200"><p class="text-xs text-slate-500">Produtividade</p><p class="text-xl font-bold">+29%</p></div>
                <div class="rounded-xl p-3 bg-white border border-slate-200"><p class="text-xs text-slate-500">Erros operacionais</p><p class="text-xl font-bold">-41%</p></div>
                <div class="rounded-xl p-3 bg-white border border-slate-200"><p class="text-xs text-slate-500">Custo por ordem</p><p class="text-xl font-bold">-18%</p></div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 shadow-soft p-5 bg-white">
              <h3 class="font-display text-lg mb-4">Fluxo de aprovacoes inteligentes</h3>
              <div class="grid grid-cols-3 gap-2 text-xs sm:text-sm">
                <div class="p-3 rounded-xl bg-sky-50 border border-sky-100">Solicitacao</div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-100">Validacao</div>
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">Execucao</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="depoimentos" class="py-20 lg:py-24 section-pattern">
      <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-3xl reveal">
          <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-900 text-white">Prova social</p>
          <h2 class="text-3xl md:text-4xl font-bold mt-5 text-graphite">Empresas estao reduzindo gargalos e acelerando resultado operacional.</h2>
        </div>

        <div class="mt-10 grid md:grid-cols-3 gap-5">
          <article class="reveal rounded-2xl bg-white p-6 border border-slate-200 shadow-soft">
            <p class="text-slate-700">"Em 90 dias, reduzimos o tempo operacional em 34% e passamos a ter previsibilidade real do chao de fabrica."</p>
            <p class="mt-4 font-semibold">Fernanda M.</p>
            <p class="text-sm text-slate-500">Diretora Industrial, Atlas Industrial</p>
          </article>
          <article class="reveal rounded-2xl bg-white p-6 border border-slate-200 shadow-soft">
            <p class="text-slate-700">"A rastreabilidade completa eliminou discussoes improdutivas e aumentou nosso SLA em 22 pontos."</p>
            <p class="mt-4 font-semibold">Gustavo R.</p>
            <p class="text-sm text-slate-500">COO, NovaMec Group</p>
          </article>
          <article class="reveal rounded-2xl bg-white p-6 border border-slate-200 shadow-soft">
            <p class="text-slate-700">"A implantacao foi rapida, a adocao da equipe foi alta e o ROI apareceu no primeiro trimestre."</p>
            <p class="mt-4 font-semibold">Bianca L.</p>
            <p class="text-sm text-slate-500">Head de Operacoes, Helix Logistics</p>
          </article>
        </div>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="reveal rounded-2xl p-5 bg-white border border-slate-200 text-center"><p class="text-3xl font-bold">-37%</p><p class="text-slate-600">Tempo operacional</p></div>
          <div class="reveal rounded-2xl p-5 bg-white border border-slate-200 text-center"><p class="text-3xl font-bold">+29%</p><p class="text-slate-600">Produtividade media</p></div>
          <div class="reveal rounded-2xl p-5 bg-white border border-slate-200 text-center"><p class="text-3xl font-bold">-41%</p><p class="text-slate-600">Erros recorrentes</p></div>
          <div class="reveal rounded-2xl p-5 bg-white border border-slate-200 text-center"><p class="text-3xl font-bold">+22 pts</p><p class="text-slate-600">Melhoria de SLA</p></div>
        </div>
      </div>
    </section>

    <section class="py-20 lg:py-24 bg-white">
      <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-3xl reveal">
          <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-cyan-100 text-cyan-800">Diferenciais competitivos</p>
          <h2 class="text-3xl md:text-4xl font-bold mt-5 text-graphite">Por que a Beyond MRP entrega resultado mais rapido que alternativas tradicionais.</h2>
        </div>

        <div class="mt-10 grid md:grid-cols-2 gap-5">
          <article class="reveal rounded-2xl border border-slate-200 p-6"><h3 class="font-display text-xl">Implantacao rapida e assistida</h3><p class="mt-2 text-slate-600">Equipe especializada garante configuracao acelerada e onboarding orientado a resultado.</p></article>
          <article class="reveal rounded-2xl border border-slate-200 p-6"><h3 class="font-display text-xl">Arquitetura moderna e escalavel</h3><p class="mt-2 text-slate-600">Plataforma preparada para alta disponibilidade, crescimento de usuarios e multiplos sites.</p></article>
          <article class="reveal rounded-2xl border border-slate-200 p-6"><h3 class="font-display text-xl">Integracao facilitada</h3><p class="mt-2 text-slate-600">APIs robustas e flexiveis para conectar processos sem interromper operacao em andamento.</p></article>
          <article class="reveal rounded-2xl border border-slate-200 p-6"><h3 class="font-display text-xl">Seguranca enterprise por padrao</h3><p class="mt-2 text-slate-600">SSO, trilhas auditaveis e controle fino de acesso para ambientes com exigencia de compliance.</p></article>
        </div>
      </div>
    </section>

    <section class="py-20 lg:py-24 bg-slate-100">
      <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-3xl reveal">
          <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-900 text-white">Antes x Depois</p>
          <h2 class="text-3xl md:text-4xl font-bold mt-5 text-graphite">Comparativo claro de impacto operacional.</h2>
        </div>

        <div class="mt-10 overflow-x-auto reveal">
          <table class="min-w-full rounded-2xl overflow-hidden border border-slate-200 bg-white">
            <thead class="bg-slate-900 text-white">
              <tr>
                <th class="text-left px-6 py-4">Cenario</th>
                <th class="text-left px-6 py-4">Antes do sistema</th>
                <th class="text-left px-6 py-4">Depois do sistema</th>
              </tr>
            </thead>
            <tbody class="text-slate-700">
              <tr class="border-t border-slate-200"><td class="px-6 py-4 font-semibold">Planejamento</td><td class="px-6 py-4">Planilhas desconectadas</td><td class="px-6 py-4">Automacao integrada e centralizada</td></tr>
              <tr class="border-t border-slate-200 bg-slate-50"><td class="px-6 py-4 font-semibold">Execucao</td><td class="px-6 py-4">Processos manuais e lentos</td><td class="px-6 py-4">Workflows inteligentes e padronizados</td></tr>
              <tr class="border-t border-slate-200"><td class="px-6 py-4 font-semibold">Visibilidade</td><td class="px-6 py-4">Baixa visibilidade operacional</td><td class="px-6 py-4">Dashboards em tempo real por unidade e processo</td></tr>
              <tr class="border-t border-slate-200 bg-slate-50"><td class="px-6 py-4 font-semibold">Confiabilidade</td><td class="px-6 py-4">Erros humanos recorrentes</td><td class="px-6 py-4">Rastreabilidade e auditoria ponta a ponta</td></tr>
              <tr class="border-t border-slate-200"><td class="px-6 py-4 font-semibold">Custos</td><td class="px-6 py-4">Custos operacionais crescentes</td><td class="px-6 py-4">Reducao de desperdicios e ganho de margem</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section id="cta-final" class="py-20 lg:py-24 bg-night text-white relative overflow-hidden">
      <div class="absolute inset-0" style="background: var(--grad-hero);"></div>
      <div class="relative max-w-5xl mx-auto px-6 lg:px-10 text-center reveal">
        <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-white/10 border border-white/20 text-mist">Pronto para evoluir sua operacao?</p>
        <h2 class="text-4xl md:text-5xl font-bold mt-5">Ative a plataforma em dias e comece a capturar ganhos operacionais ja no primeiro ciclo.</h2>
        <p class="mt-5 text-lg text-mist/95 max-w-3xl mx-auto">Sem compromisso de longo prazo, com suporte incluso, onboarding assistido e configuracao rapida para sua realidade.</p>

        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('start-trial') }}" class="cta-focus inline-flex justify-center items-center px-8 py-4 rounded-full bg-coral text-white font-bold tracking-wide text-sm hover:brightness-110 transition shadow-glow">TESTE GRATIS POR 14 DIAS</a>
          <a href="#" class="inline-flex justify-center items-center px-8 py-4 rounded-full border border-white/30 text-white font-semibold text-sm hover:bg-white/10 transition">Agendar demonstracao executiva</a>
        </div>

        <div class="mt-8 grid sm:grid-cols-4 gap-3 text-sm text-mist/90">
          <div class="glass rounded-2xl px-4 py-3">Configuracao rapida</div>
          <div class="glass rounded-2xl px-4 py-3">Sem compromisso</div>
          <div class="glass rounded-2xl px-4 py-3">Suporte incluso</div>
          <div class="glass rounded-2xl px-4 py-3">Onboarding assistido</div>
        </div>
      </div>
    </section>

    <section id="faq" class="py-20 lg:py-24 bg-white">
      <div class="max-w-4xl mx-auto px-6 lg:px-10">
        <div class="max-w-3xl reveal">
          <p class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-900 text-white">FAQ estrategico</p>
          <h2 class="text-3xl md:text-4xl font-bold mt-5 text-graphite">Perguntas frequentes antes de iniciar seu teste.</h2>
        </div>

        <div class="mt-10 space-y-3" id="faq-list">
          <article class="reveal border border-slate-200 rounded-2xl overflow-hidden">
            <button class="faq-btn w-full flex items-center justify-between text-left px-5 py-4 font-semibold">Preciso de cartao de credito?<span class="faq-icon text-slate-400">+</span></button>
            <div class="faq-content hidden px-5 pb-5 text-slate-600">Nao. Voce pode iniciar agora o TESTE GRATIS POR 14 DIAS sem inserir dados de pagamento.</div>
          </article>
          <article class="reveal border border-slate-200 rounded-2xl overflow-hidden">
            <button class="faq-btn w-full flex items-center justify-between text-left px-5 py-4 font-semibold">Quanto tempo leva a implantacao?<span class="faq-icon text-slate-400">+</span></button>
            <div class="faq-content hidden px-5 pb-5 text-slate-600">A configuracao inicial acontece em poucos dias, com onboarding assistido para aceleracao de valor.</div>
          </article>
          <article class="reveal border border-slate-200 rounded-2xl overflow-hidden">
            <button class="faq-btn w-full flex items-center justify-between text-left px-5 py-4 font-semibold">O sistema possui suporte?<span class="faq-icon text-slate-400">+</span></button>
            <div class="faq-content hidden px-5 pb-5 text-slate-600">Sim. O suporte especializado acompanha sua equipe durante trial e operacao recorrente.</div>
          </article>
          <article class="reveal border border-slate-200 rounded-2xl overflow-hidden">
            <button class="faq-btn w-full flex items-center justify-between text-left px-5 py-4 font-semibold">Posso integrar com meus sistemas?<span class="faq-icon text-slate-400">+</span></button>
            <div class="faq-content hidden px-5 pb-5 text-slate-600">Sim. A plataforma oferece API robusta, conectores e suporte para ambientes legados e modernos.</div>
          </article>
          <article class="reveal border border-slate-200 rounded-2xl overflow-hidden">
            <button class="faq-btn w-full flex items-center justify-between text-left px-5 py-4 font-semibold">E seguro para ambiente enterprise?<span class="faq-icon text-slate-400">+</span></button>
            <div class="faq-content hidden px-5 pb-5 text-slate-600">Sim. Inclui SSO, controle de permissao granular, logs auditaveis e arquitetura preparada para escala.</div>
          </article>
          <article class="reveal border border-slate-200 rounded-2xl overflow-hidden">
            <button class="faq-btn w-full flex items-center justify-between text-left px-5 py-4 font-semibold">Existe fidelidade?<span class="faq-icon text-slate-400">+</span></button>
            <div class="faq-content hidden px-5 pb-5 text-slate-600">Nao. O trial e sem compromisso, para voce validar valor real antes de qualquer decisao.</div>
          </article>
          <article class="reveal border border-slate-200 rounded-2xl overflow-hidden">
            <button class="faq-btn w-full flex items-center justify-between text-left px-5 py-4 font-semibold">Posso cancelar quando quiser?<span class="faq-icon text-slate-400">+</span></button>
            <div class="faq-content hidden px-5 pb-5 text-slate-600">Sim. Cancelamento simples e transparente, sem burocracia.</div>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="bg-slate-950 text-slate-400 py-10">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 flex flex-col md:flex-row gap-4 justify-between">
      <p>Beyond MRP - Plataforma SaaS para operacoes enterprise.</p>
      <p>2026 Beyond MRP. Todos os direitos reservados.</p>
    </div>
  </footer>

  <script>
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("on");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );

    document.querySelectorAll(".reveal").forEach((el) => observer.observe(el));

    const counter = document.querySelector("[data-counter]");
    if (counter) {
      let value = 0;
      const target = Number(counter.dataset.counter || 0);
      const step = Math.max(8, Math.ceil(target / 80));
      const interval = setInterval(() => {
        value += step;
        if (value >= target) {
          counter.textContent = target.toLocaleString("pt-BR");
          clearInterval(interval);
        } else {
          counter.textContent = value.toLocaleString("pt-BR");
        }
      }, 24);
    }

    const faqButtons = document.querySelectorAll(".faq-btn");
    faqButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const content = btn.nextElementSibling;
        const icon = btn.querySelector(".faq-icon");
        const isOpen = !content.classList.contains("hidden");

        faqButtons.forEach((otherBtn) => {
          const otherContent = otherBtn.nextElementSibling;
          const otherIcon = otherBtn.querySelector(".faq-icon");
          otherContent.classList.add("hidden");
          otherIcon.textContent = "+";
        });

        if (!isOpen) {
          content.classList.remove("hidden");
          icon.textContent = "-";
        }
      });
    });
  </script>
</body>
</html>