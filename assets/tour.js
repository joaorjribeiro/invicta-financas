/**
 * Invicta Finanças — Tour de Onboarding
 * Inclua este arquivo no final do <body> de todas as páginas:
 * <script src="../assets/js/tour.js"></script>
 *
 * Para resetar o tour (ex: botão "Ver tutorial"):
 * localStorage.removeItem('invicta_tour_visto'); location.reload();
 */

const TOUR_STEPS = {

  // ── Dashboard ──────────────────────────────────────────────
  'dashboard.php': [
    {
      target: 'aside',
      title: '📋 Menu lateral',
      text: 'Aqui você navega entre todas as seções do sistema.',
      position: 'right',
    },
    {
      target: '[data-tour="saldo"]',
      title: '💰 Saldo Atual',
      text: 'Seu saldo inicial somado com entradas e subtraído das saídas.',
      position: 'bottom',
    },
    {
      target: '[data-tour="renda"]',
      title: '📈 Receita Prevista',
      text: 'A renda mensal que você configurou em Valores.',
      position: 'bottom',
    },
    {
      target: '[data-tour="limite"]',
      title: '🚨 Limite de Gastos',
      text: 'O teto de gastos que você definiu. O sininho avisa quando estiver perto.',
      position: 'bottom',
    },
    {
      target: '[data-tour="metas"]',
      title: '🎯 Metas Financeiras',
      text: 'Acompanhe o progresso das suas metas aqui.',
      position: 'top',
    },
    {
      target: '[data-tour="grafico-despesas"]',
      title: '📊 Despesas Mensais',
      text: 'Gráfico com suas saídas mês a mês ao longo do ano.',
      position: 'top',
    },
    {
      target: '[data-tour="grafico-receitas"]',
      title: '📉 Receitas x Despesas',
      text: 'Compare entradas e saídas para entender sua saúde financeira.',
      position: 'top',
    },
    {
      target: '[data-tour="ultimas-transacoes"]',
      title: '🧾 Últimas Transações',
      text: 'As 5 movimentações mais recentes da sua conta.',
      position: 'top',
    },
  ],

  // ── Valores ────────────────────────────────────────────────
  'valores.php': [
    {
      target: 'aside',
      title: '📋 Menu lateral',
      text: 'Navegue entre as seções do sistema por aqui.',
      position: 'right',
    },
    {
      target: '[name="saldo_inicial"]',
      title: '💵 Saldo Inicial',
      text: 'Informe o quanto você já tem guardado. Será a base do seu saldo.',
      position: 'bottom',
    },
    {
      target: '[name="renda_prevista"]',
      title: '📥 Renda Prevista',
      text: 'Sua renda mensal esperada. Aparece no card do Dashboard.',
      position: 'bottom',
    },
    {
      target: '[name="limite_gastos"]',
      title: '🔔 Limite de Gastos',
      text: 'Defina um teto. Quando você atingir 50%, 75% ou 95%, receberá alertas.',
      position: 'bottom',
    },
  ],

  // ── Metas ──────────────────────────────────────────────────
  'metas.php': [
    {
      target: 'aside',
      title: '📋 Menu lateral',
      text: 'Navegue entre as seções do sistema por aqui.',
      position: 'right',
    },
    {
      target: '#openModalNova',
      title: '➕ Nova Meta',
      text: 'Crie uma meta financeira com nome e valor alvo.',
      position: 'bottom',
    },
    {
      target: '[data-tour="lista-metas"]',
      title: '🎯 Suas Metas',
      text: 'Cada card mostra o progresso. Use Editar para adicionar valor e Excluir para remover.',
      position: 'top',
    },
  ],

  // ── Transações ─────────────────────────────────────────────
  'transacoes.php': [
    {
      target: 'aside',
      title: '📋 Menu lateral',
      text: 'Navegue entre as seções do sistema por aqui.',
      position: 'right',
    },
    {
      target: '#openModalNova',
      title: '➕ Nova Transação',
      text: 'Registre entradas (salário, renda extra) ou saídas (contas, compras).',
      position: 'bottom',
    },
    {
      target: '[data-tour="tabela-transacoes"]',
      title: '📋 Histórico',
      text: 'Todas as suas movimentações em ordem cronológica. Você pode editar ou excluir cada uma.',
      position: 'top',
    },
  ],

  // ── Relatórios ─────────────────────────────────────────────
  'relatorios.php': [
    {
      target: 'aside',
      title: '📋 Menu lateral',
      text: 'Navegue entre as seções do sistema por aqui.',
      position: 'right',
    },
    {
      target: '[name="mes"]',
      title: '📅 Filtro de Mês',
      text: 'Selecione o mês que quer analisar e clique em Filtrar.',
      position: 'bottom',
    },
    {
      target: '[data-tour="cards-relatorio"]',
      title: '📊 Resumo do Período',
      text: 'Total de receitas, despesas e saldo do mês selecionado.',
      position: 'bottom',
    },
    {
      target: '[data-tour="graficos-relatorio"]',
      title: '📈 Gráficos',
      text: 'Despesas por categoria (pizza) e comparativo dos últimos 6 meses (linha).',
      position: 'top',
    },
    {
      target: '[data-tour="exportar"]',
      title: '📤 Exportar',
      text: 'Baixe seu relatório em PDF, planilha Excel ou imprima diretamente.',
      position: 'top',
    },
  ],

  // ── Configurações ──────────────────────────────────────────
  'configuracoes.php': [
    {
      target: 'aside',
      title: '📋 Menu lateral',
      text: 'Navegue entre as seções do sistema por aqui.',
      position: 'right',
    },
    {
      target: '[data-tour="foto-perfil"]',
      title: '🖼️ Foto de Perfil',
      text: 'Clique em "Escolher Foto" para trocar seu avatar. JPG ou PNG até 2MB.',
      position: 'bottom',
    },
    {
      target: '[data-tour="perfil-usuario"]',
      title: '✏️ Editar Perfil',
      text: 'Atualize seu nome, e-mail e telefone aqui.',
      position: 'bottom',
    },
    {
      target: '[data-tour="alterar-senha"]',
      title: '🔒 Alterar Senha',
      text: 'Troque sua senha sempre que quiser. Mínimo de 6 caracteres.',
      position: 'top',
    },
  ],
};

// ─────────────────────────────────────────────────────────────
// Engine do Tour
// ─────────────────────────────────────────────────────────────

(function () {
  // Descobre a página atual
  const page = window.location.pathname.split('/').pop() || 'index.php';
  const steps = TOUR_STEPS[page];

  // Sem passos pra essa página ou já visto → sai
  const tourKey = 'invicta_tour_' + page;
  if (!steps || localStorage.getItem(tourKey)) return;

  let currentStep = 0;

  // Overlay de fundo suave
  const overlay = document.createElement('div');
  overlay.id = 'tour-overlay';
  overlay.style.cssText = `
    position: fixed; inset: 0; z-index: 9998;
    background: transparent; pointer-events: none;
  `;
  document.body.appendChild(overlay);

  // Balão do tour
  const balloon = document.createElement('div');
  balloon.id = 'tour-balloon';
  balloon.style.cssText = `
    position: fixed; z-index: 9999; max-width: 280px;
    background: white; border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    padding: 16px 18px 14px;
    font-family: inherit;
    transition: opacity 0.2s ease;
    pointer-events: all;
  `;
  document.body.appendChild(balloon);

  function renderBalloon(step) {
    const total = steps.length;
    balloon.innerHTML = `
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
        <span style="font-size:15px; font-weight:700; color:#111;">${step.title}</span>
        <span style="font-size:11px; color:#9ca3af; margin-left:10px;">${currentStep + 1}/${total}</span>
      </div>
      <p style="font-size:13px; color:#374151; margin:0 0 14px; line-height:1.5;">${step.text}</p>
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <button id="tour-skip" style="
          font-size:12px; color:#9ca3af; background:none; border:none;
          cursor:pointer; padding:0;
        ">Pular tutorial</button>
        <div style="display:flex; gap:8px;">
          ${currentStep > 0 ? `<button id="tour-prev" style="
            font-size:13px; padding:6px 14px; border-radius:7px;
            border:1px solid #e5e7eb; background:white; cursor:pointer; color:#374151;
          ">← Voltar</button>` : ''}
          <button id="tour-next" style="
            font-size:13px; padding:6px 14px; border-radius:7px;
            border:none; background:#EF4B2A; color:white; cursor:pointer; font-weight:600;
          ">${currentStep === total - 1 ? 'Concluir ✓' : 'Próximo →'}</button>
        </div>
      </div>
    `;

    // Eventos dos botões
    document.getElementById('tour-next').addEventListener('click', () => {
      if (currentStep < total - 1) {
        currentStep++;
        showStep(currentStep);
      } else {
        endTour();
      }
    });

    if (currentStep > 0) {
      document.getElementById('tour-prev').addEventListener('click', () => {
        currentStep--;
        showStep(currentStep);
      });
    }

    document.getElementById('tour-skip').addEventListener('click', endTour);
  }

  function positionBalloon(targetEl, position) {
    const rect = targetEl.getBoundingClientRect();
    const bw = 280;
    const margin = 12;

    // Destaque suave no elemento
    targetEl.style.outline = '2px solid #EF4B2A';
    targetEl.style.outlineOffset = '3px';
    targetEl.style.borderRadius = '6px';
    targetEl.style.transition = 'outline 0.2s';

    balloon.style.opacity = '0';
    setTimeout(() => {
      const bh = balloon.offsetHeight;
      let top, left;

      if (position === 'bottom') {
        top = rect.bottom + margin + window.scrollY;
        left = rect.left + rect.width / 2 - bw / 2 + window.scrollX;
      } else if (position === 'top') {
        top = rect.top - bh - margin + window.scrollY;
        left = rect.left + rect.width / 2 - bw / 2 + window.scrollX;
      } else if (position === 'right') {
        top = rect.top + rect.height / 2 - bh / 2 + window.scrollY;
        left = rect.right + margin + window.scrollX;
      } else {
        top = rect.top + rect.height / 2 - bh / 2 + window.scrollY;
        left = rect.left - bw - margin + window.scrollX;
      }

      // Garante que não sai da tela
      const vw = window.innerWidth;
      if (left + bw > vw - 10) left = vw - bw - 10;
      if (left < 10) left = 10;

      balloon.style.top = top + 'px';
      balloon.style.left = left + 'px';
      balloon.style.opacity = '1';

      // Scroll suave até o elemento
      targetEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 50);
  }

  function clearHighlight() {
    document.querySelectorAll('[style*="outline"]').forEach(el => {
      el.style.outline = '';
      el.style.outlineOffset = '';
    });
  }

  function showStep(index) {
    const step = steps[index];
    const target = document.querySelector(step.target);

    clearHighlight();

    if (!target) {
      // Elemento não encontrado nessa página, pula
      if (index < steps.length - 1) {
        currentStep++;
        showStep(currentStep);
      } else {
        endTour();
      }
      return;
    }

    renderBalloon(step);
    positionBalloon(target, step.position);
  }

  function endTour() {
    clearHighlight();
    balloon.remove();
    overlay.remove();
    localStorage.setItem(tourKey, '1');
  }

  // Inicia
  showStep(0);

  // Botão flutuante "?" para rever o tour
  const btnRever = document.createElement('button');
  btnRever.id = 'tour-btn-rever';
  btnRever.title = 'Ver tutorial desta página';
  btnRever.textContent = '?';
  btnRever.style.cssText = `
    position: fixed; bottom: 24px; right: 24px; z-index: 9997;
    width: 40px; height: 40px; border-radius: 50%;
    background: #EF4B2A; color: white; font-size: 20px; font-weight: 700;
    border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
  `;
  btnRever.addEventListener('mouseenter', () => btnRever.style.background = '#D94426');
  btnRever.addEventListener('mouseleave', () => btnRever.style.background = '#EF4B2A');
  btnRever.addEventListener('click', () => {
    localStorage.removeItem(tourKey);
    location.reload();
  });
  document.body.appendChild(btnRever);

})();