<?php
/**
 * Ethan Assistant - Listagem de Chamados Online (RF12 / RN12)
 * @var array $chamados Lista de atendimentos online
 * @var array $tecnicos Lista de técnicos para filtros
 */
$pageTitle = 'Chamados Online - Ethan Assistant';
$currentRoute = 'chamados';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['perfil' => 'tecnico']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1>Chamados Online</h1>
        <p class="page-subtitle">Suportes remotos e atendimentos virtuais a clientes (sem fila de prioridade)</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/chamados/novo" class="btn btn-primary">
          <span>+ Novo Chamado Online</span>
        </a>
      </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="filter-bar">
      <div class="filter-group">
        <div class="search-input-wrap">
          <input
            type="text"
            class="form-control"
            placeholder="Buscar chamado por cliente ou descrição..."
            data-table-filter="tabelaChamados"
          >
        </div>
      </div>
      <div style="font-size: var(--font-size-xs); color: var(--color-slate-500);">
        <?= count($chamados ?? []) ?> chamado(s) registrado(s)
      </div>
    </div>

    <!-- Tabela Corporativa de Chamados Online -->
    <div class="table-responsive">
      <table class="table-corporate" id="tabelaChamados">
        <thead>
          <tr>
            <th>Nº</th>
            <th>Cliente</th>
            <th>Técnico</th>
            <th>Descrição do Atendimento</th>
            <th>Status</th>
            <th>Abertura</th>
            <th>Conclusão</th>
            <th style="text-align: right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($chamados)): ?>
            <tr class="empty-row">
              <td colspan="8" class="empty-state">
                <div class="empty-state-title">Nenhum chamado online registrado</div>
                <p>Abra um novo chamado utilizando o botão acima.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($chamados as $ch): ?>
              <tr>
                <td><strong>#<?= (int)$ch['id'] ?></strong></td>
                <td><strong><?= htmlspecialchars($ch['cliente_nome'] ?? 'Cliente') ?></strong></td>
                <td><?= htmlspecialchars($ch['tecnico_nome'] ?? 'Não atribuído') ?></td>
                <td style="max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  <?= htmlspecialchars($ch['descricao']) ?>
                </td>
                <td>
                  <?= renderStatusBadge($ch['status'] ?? 'Aberto') ?>
                </td>
                <td><?= htmlspecialchars($ch['aberto_em_formatada'] ?? '-') ?></td>
                <td><?= htmlspecialchars($ch['concluido_em_formatada'] ?? '-') ?></td>
                <td>
                  <div class="table-actions" style="justify-content: flex-end;">
                    <a href="<?= $baseUrl ?>/chamados/<?= (int)$ch['id'] ?>/editar" class="btn btn-outline btn-sm">
                      Atender / Editar
                    </a>
                    <button
                      type="button"
                      class="btn btn-outline btn-sm"
                      style="color: var(--color-danger);"
                      data-modal-target="modalExclusao"
                      data-record-id="<?= (int)$ch['id'] ?>"
                      data-record-name="Chamado #<?= (int)$ch['id'] ?>"
                      onclick="document.getElementById('formConfirmarExclusao').action='<?= $baseUrl ?>/chamados/<?= (int)$ch['id'] ?>/excluir'"
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
