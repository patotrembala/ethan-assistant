<?php
/**
 * Ethan Assistant - Dashboard Operacional (RF15 / RF16 / Seção 12)
 * @var array $metricas Dados consolidados de indicadores
 * @var array $atividadesRecentes Lista de OS, chamados ou pendências que demandam atenção
 * @var array $tecnicos Lista de técnicos para filtro do administrador
 * @var string $tecnicoFiltroId ID do técnico selecionado no filtro
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

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
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
                    <span class="badge badge-status-<?= strtolower(str_replace(' ', '', $item['status'] ?? 'aberta')) ?>">
                      <?= htmlspecialchars($item['status'] ?? 'Aberta') ?>
                    </span>
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

<?php require __DIR__ . '/../layouts/footer.php'; ?>
