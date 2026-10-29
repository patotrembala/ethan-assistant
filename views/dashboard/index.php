<?php
/**
 * Ethan Assistant - Dashboard Operacional (RF15 / RF16 / Seção 12)
 * @var array $metricas Dados consolidados de indicadores
 * @var array $atividadesRecentes Lista de OS, chamados ou pendências que demandam atenção
 * @var array $tecnicos Lista de técnicos para filtro do administrador
 * @var string $tecnicoFiltroId ID do técnico selecionado no filtro
 * @var array $graficoChamados Série de chamados resolvidos nos últimos sete dias
 */
$pageTitle = 'Dashboard Operacional - Ethan Assistant';
$currentRoute = 'dashboard';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['nome' => 'Administrador', 'perfil' => 'admin']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';

// Valores padrão caso ainda não venham do controller do Codex
$metricas = $metricas ?? [
  'servicos_concluidos' => 0,
  'chamados_resolvidos' => 0,
  'pendencias_concluidas' => 0,
  'tarefas_atrasadas' => 0,
  'tempo_medio' => '0h',
  'conclusao_no_prazo' => '100%'
];
$graficoChamados = $graficoChamados ?? [
  'labels' => [],
  'valores' => [],
  'tendencia' => 'estavel',
  'variacao' => 0
];

$chartValues = array_map('intval', $graficoChamados['valores'] ?? []);
$chartLabels = array_values($graficoChamados['labels'] ?? []);
$chartMax = max(1, ...($chartValues ?: [1]));
$chartWidth = 760;
$chartHeight = 220;
$chartPaddingX = 42;
$chartPaddingY = 24;
$chartPlotWidth = $chartWidth - ($chartPaddingX * 2);
$chartPlotHeight = $chartHeight - ($chartPaddingY * 2);
$chartPoints = [];
foreach ($chartValues as $index => $value) {
  $x = $chartPaddingX + ($index * ($chartPlotWidth / max(1, count($chartValues) - 1)));
  $y = $chartPaddingY + $chartPlotHeight - (($value / $chartMax) * $chartPlotHeight);
  $chartPoints[] = round($x, 2) . ',' . round($y, 2);
}
$chartTrend = in_array(($graficoChamados['tendencia'] ?? ''), ['alta', 'queda'], true)
  ? $graficoChamados['tendencia']
  : 'estavel';
$chartTrendText = match ($chartTrend) {
  'alta' => 'Desempenho em alta',
  'queda' => 'Desempenho em queda',
  default => 'Desempenho estável'
};
$chartTrendDetail = match ($chartTrend) {
  'alta' => 'Mais chamados foram resolvidos ao final do período.',
  'queda' => 'Menos chamados foram resolvidos ao final do período.',
  default => 'O ritmo de resoluções permaneceu equilibrado.'
};

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content dashboard-view">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1>Painel de Operações</h1>
        <p class="page-subtitle">Acompanhamento de fluxo de ordens de serviço, atendimentos e indicadores operacionais</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/ordens/nova" class="btn btn-primary">
          <span>+ Nova Ordem de Serviço</span>
        </a>
        <a href="<?= $baseUrl ?>/chamados/novo" class="btn btn-secondary">
          <span>+ Chamado Online</span>
        </a>
        <a href="<?= $baseUrl ?>/pendencias/nova" class="btn btn-secondary">
          <span>+ Pendência Interna</span>
        </a>
      </div>
    </div>

    <!-- Filtro de Técnico para Administrador (sem ranking competitivo, RN10) -->
    <?php if ($isAdmin): ?>
      <div class="filter-bar" style="margin-bottom: var(--spacing-5);">
        <div style="font-weight: 600; font-size: var(--font-size-sm); color: var(--color-slate-700);">
          Visão da Equipe:
        </div>
        <form method="GET" action="<?= $baseUrl ?>/dashboard" style="display: flex; gap: var(--spacing-3); align-items: center;">
          <select name="tecnico_id" class="form-select" onchange="this.form.submit()" style="min-width: 240px;">
            <option value="">Todos os Técnicos (Visão Geral)</option>
            <?php foreach (($tecnicos ?? []) as $tec): ?>
              <option value="<?= $tec['id'] ?>" <?= ($tecnicoFiltroId ?? '') == $tec['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($tec['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($tecnicoFiltroId)): ?>
            <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline btn-sm">Limpar filtro</a>
          <?php endif; ?>
        </form>
      </div>
    <?php endif; ?>

    <!-- Indicadores Principais (RF16) -->
    <div class="grid-cards-3">
      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">Serviços Concluídos</span>
          <span>🛠️</span>
        </div>
        <div class="metric-value"><?= (int)$metricas['servicos_concluidos'] ?></div>
        <div class="metric-subtext">Ordens finalizadas no período</div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">Chamados Resolvidos</span>
          <span>💬</span>
        </div>
        <div class="metric-value"><?= (int)$metricas['chamados_resolvidos'] ?></div>
        <div class="metric-subtext">Suportes remotos concluídos</div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">Pendências Concluídas</span>
          <span>📋</span>
        </div>
        <div class="metric-value"><?= (int)$metricas['pendencias_concluidas'] ?></div>
        <div class="metric-subtext">Demandas internas finalizadas</div>
      </div>
    </div>

    <!-- Indicadores de Prazo e Desempenho Construtivo -->
    <div class="grid-cards-3">
      <div class="metric-card <?= ((int)$metricas['tarefas_atrasadas'] > 0) ? 'alert-card' : '' ?>">
        <div class="metric-header">
          <span class="metric-label">Tarefas em Atraso</span>
          <?php if ((int)$metricas['tarefas_atrasadas'] > 0): ?>
            <span class="badge-prazo-violado">Prazo violado</span>
          <?php else: ?>
            <span>✓</span>
          <?php endif; ?>
        </div>
        <div class="metric-value" style="<?= ((int)$metricas['tarefas_atrasadas'] > 0) ? 'color: var(--color-danger);' : '' ?>">
          <?= (int)$metricas['tarefas_atrasadas'] ?>
        </div>
        <div class="metric-subtext">Requerem atenção ou apoio imediato</div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <span class="metric-label">Tempo Médio de Atendimento</span>
          <span>⏱️</span>
        </div>
        <div class="metric-value"><?= htmlspecialchars($metricas['tempo_medio']) ?></div>
        <div class="metric-subtext">Média entre abertura e entrega</div>
      </div>

      <div class="metric-card success-card">
        <div class="metric-header">
          <span class="metric-label">Conclusão no Prazo</span>
          <span>📈</span>
        </div>
        <div class="metric-value" style="color: var(--color-success);"><?= htmlspecialchars($metricas['conclusao_no_prazo']) ?></div>
        <div class="metric-subtext">Índice de pontualidade operacional</div>
      </div>
    </div>

    <!-- Tabela de Atividades Prioritárias e Pendentes -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Atividades em Aberto e Atenção a Prazos</h2>
        <a href="<?= $baseUrl ?>/ordens" class="btn btn-outline btn-sm">Ver todas as ordens →</a>
      </div>
      <div class="table-responsive">
        <table class="table-corporate">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Referência / Cliente</th>
              <th>Responsável</th>
              <th>Prioridade</th>
              <th>Status</th>
              <th>Prazo Limite</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($atividadesRecentes)): ?>
              <tr class="empty-row">
                <td colspan="7" class="empty-state">
                  <div class="empty-state-title">Nenhuma atividade pendente no momento</div>
                  <p>Todas as ordens de serviço e chamados atribuídos estão em dia.</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($atividadesRecentes as $item): ?>
                <?php
                  $isAtrasado = !empty($item['prazo_violado']);
                ?>
                <tr>
                  <td>
                    <span class="badge" style="background: var(--color-slate-100); border: 1px solid var(--color-slate-300);">
                      <?= htmlspecialchars($item['tipo_registro']) ?>
                    </span>
                  </td>
                  <td>
                    <strong>#<?= (int)$item['id'] ?></strong> - <?= htmlspecialchars($item['titulo_ou_cliente']) ?>
                  </td>
                  <td><?= htmlspecialchars($item['tecnico_nome'] ?? 'Não atribuído') ?></td>
                  <td>
                    <?php if (!empty($item['prioridade'])): ?>
                      <span class="badge badge-prioridade-<?= strtolower($item['prioridade']) ?>">
                        <?= ucfirst(htmlspecialchars($item['prioridade'])) ?>
                      </span>
                    <?php else: ?>
                      <span style="color: var(--color-slate-400); font-size: var(--font-size-xs);">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?= renderStatusBadge($item['status'] ?? 'Aberta') ?>
                  </td>
                  <td>
                    <span><?= htmlspecialchars($item['prazo_formatado'] ?? '-') ?></span>
                    <?php if ($isAtrasado): ?>
                      <span class="badge-prazo-violado" style="margin-left: 6px;">Prazo violado</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= $baseUrl ?>/<?= $item['url_modulo'] ?>/<?= (int)$item['id'] ?>" class="btn btn-outline btn-sm">
                      Detalhes
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <section class="card performance-chart-card trend-<?= htmlspecialchars($chartTrend) ?>" aria-labelledby="performance-chart-title">
      <div class="performance-chart-header">
        <div>
          <span class="performance-chart-eyebrow">Evolução operacional</span>
          <h2 id="performance-chart-title" class="card-title">Chamados resolvidos — últimos 7 dias</h2>
          <p>Quantidade de chamados concluídos por dia<?= !empty($tecnicoFiltroId) ? ' para o técnico selecionado' : ' pela equipe' ?>.</p>
        </div>
        <div class="performance-trend" aria-label="<?= htmlspecialchars($chartTrendText) ?>">
          <span class="performance-trend-icon" aria-hidden="true"><?= $chartTrend === 'alta' ? '↗' : ($chartTrend === 'queda' ? '↘' : '→') ?></span>
          <span>
            <strong><?= htmlspecialchars($chartTrendText) ?></strong>
            <small><?= htmlspecialchars($chartTrendDetail) ?></small>
          </span>
        </div>
      </div>

      <div class="performance-chart" role="img" aria-label="Gráfico de linha: <?= htmlspecialchars(implode(', ', array_map(static fn($label, $value) => $label . ': ' . $value, $chartLabels, $chartValues))) ?>">
        <svg viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight ?>" preserveAspectRatio="none" aria-hidden="true" focusable="false">
          <line class="chart-grid-line" x1="<?= $chartPaddingX ?>" y1="<?= $chartPaddingY ?>" x2="<?= $chartWidth - $chartPaddingX ?>" y2="<?= $chartPaddingY ?>" />
          <line class="chart-grid-line" x1="<?= $chartPaddingX ?>" y1="<?= $chartHeight / 2 ?>" x2="<?= $chartWidth - $chartPaddingX ?>" y2="<?= $chartHeight / 2 ?>" />
          <line class="chart-grid-line" x1="<?= $chartPaddingX ?>" y1="<?= $chartHeight - $chartPaddingY ?>" x2="<?= $chartWidth - $chartPaddingX ?>" y2="<?= $chartHeight - $chartPaddingY ?>" />
          <?php if ($chartPoints): ?>
            <polyline class="performance-line" points="<?= htmlspecialchars(implode(' ', $chartPoints)) ?>" />
            <?php foreach ($chartPoints as $index => $point): ?>
              <?php [$pointX, $pointY] = explode(',', $point); ?>
              <circle class="performance-point" cx="<?= $pointX ?>" cy="<?= $pointY ?>" r="5">
                <title><?= htmlspecialchars(($chartLabels[$index] ?? '') . ': ' . ($chartValues[$index] ?? 0) . ' resolvido(s)') ?></title>
              </circle>
            <?php endforeach; ?>
          <?php endif; ?>
        </svg>
        <div class="performance-chart-labels" aria-hidden="true">
          <?php foreach ($chartLabels as $index => $label): ?>
            <span><strong><?= (int)($chartValues[$index] ?? 0) ?></strong><?= htmlspecialchars($label) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="performance-chart-legend" aria-label="Legenda das tendências">
        <span><i class="legend-up"></i> Subindo: bom</span>
        <span><i class="legend-stable"></i> Estável: atenção</span>
        <span><i class="legend-down"></i> Descendo: requer acompanhamento</span>
      </div>
    </section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
