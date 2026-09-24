<?php
/**
 * Ethan Assistant - Listagem de Pendências Internas (RF13 / RN11 / RN12)
 * @var array $pendencias Lista de tarefas internas
 */
$pageTitle = 'Pendências Internas - Ethan Assistant';
$currentRoute = 'pendencias';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['perfil' => 'tecnico']);

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1>Pendências Internas</h1>
        <p class="page-subtitle">Organização de rotinas, manutenções de bancada e tarefas da equipe</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/pendencias/nova" class="btn btn-primary">
          <span>+ Nova Pendência</span>
        </a>
      </div>
    </div>

    <!-- Filtros de Busca -->
    <div class="filter-bar">
      <div class="filter-group">
        <div class="search-input-wrap">
          <input
            type="text"
            class="form-control"
            placeholder="Buscar por título ou responsável..."
            data-table-filter="tabelaPendencias"
          >
        </div>
      </div>
      <div style="font-size: var(--font-size-xs); color: var(--color-slate-500);">
        <?= count($pendencias ?? []) ?> pendência(s) listada(s)
      </div>
    </div>

    <!-- Tabela Corporativa de Pendências -->
    <div class="table-responsive">
      <table class="table-corporate" id="tabelaPendencias">
        <thead>
          <tr>
            <th>Título da Pendência</th>
            <th>Responsável</th>
            <th>Prioridade</th>
            <th>Status</th>
            <th>Prazo Limite</th>
            <th style="text-align: right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pendencias)): ?>
            <tr class="empty-row">
              <td colspan="6" class="empty-state">
                <div class="empty-state-title">Nenhuma pendência pendente</div>
                <p>Todas as tarefas internas foram concluídas.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pendencias as $p): ?>
              <?php
                $isAtrasada = !empty($p['prazo_violado']);
              ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($p['titulo']) ?></strong>
                  <?php if (!empty($p['descricao'])): ?>
                    <div style="font-size: var(--font-size-xs); color: var(--color-slate-500); max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                      <?= htmlspecialchars($p['descricao']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['responsavel_nome'] ?? 'Não atribuído') ?></td>
                <td>
                  <span class="badge badge-prioridade-<?= strtolower($p['prioridade'] ?? 'media') ?>">
                    <?= ucfirst(htmlspecialchars($p['prioridade'] ?? 'Média')) ?>
                  </span>
                </td>
                <td>
                  <?= renderStatusBadge($p['status'] ?? 'Pendente') ?>
                </td>
                <td>
                  <span><?= htmlspecialchars($p['prazo_formatado'] ?? '-') ?></span>
                  <?php if ($isAtrasada): ?>
                    <span class="badge-prazo-violado">Prazo violado</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="table-actions" style="justify-content: flex-end;">
                    <a href="<?= $baseUrl ?>/pendencias/<?= (int)$p['id'] ?>" class="btn btn-outline btn-sm">
                      Detalhes
                    </a>
                    <a href="<?= $baseUrl ?>/pendencias/<?= (int)$p['id'] ?>/editar" class="btn btn-secondary btn-sm">
                      Editar
                    </a>
                    <button
                      type="button"
                      class="btn btn-outline btn-sm"
                      style="color: var(--color-danger);"
                      data-modal-target="modalExclusao"
                      data-record-id="<?= (int)$p['id'] ?>"
                      data-record-name="Pendência: <?= htmlspecialchars($p['titulo']) ?>"
                      onclick="document.getElementById('formConfirmarExclusao').action='<?= $baseUrl ?>/pendencias/<?= (int)$p['id'] ?>/excluir'"
                    >
                      ✕
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
