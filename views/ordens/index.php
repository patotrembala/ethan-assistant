<?php
/**
 * Ethan Assistant - Listagem de Ordens de Serviço (RF05 / RF06 / RF07 / RF08 / RF17)
 * @var array $ordens Lista de ordens de serviço
 * @var array $tecnicos Lista de técnicos para filtro
 * @var array $filtros Filtros ativos (status, tecnico_id, prioridade)
 */
$pageTitle = 'Ordens de Serviço - Ethan Assistant';
$currentRoute = 'ordens';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['id' => 1, 'nome' => 'Técnico', 'perfil' => 'tecnico']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
$currentUserId = $currentUser['id'] ?? 0;

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1>Ordens de Serviço</h1>
        <p class="page-subtitle">Acompanhamento e atendimento dos equipamentos de clientes</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/ordens/nova" class="btn btn-primary">
          <span>+ Nova Ordem de Serviço</span>
        </a>
      </div>
    </div>

    <!-- Barra de Filtros Corporativos -->
    <form method="GET" action="<?= $baseUrl ?>/ordens" class="filter-bar">
      <div class="filter-group">
        <div class="search-input-wrap">
          <input
            type="text"
            name="busca"
            class="form-control"
            placeholder="Buscar por cliente, equipamento..."
            value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>"
            data-table-filter="tabelaOrdens"
          >
        </div>

        <select name="status" class="form-select" onchange="this.form.submit()" style="width: auto;">
          <option value="">Todos os Status</option>
          <?php
          $statusList = ['Aberta', 'Em diagnóstico', 'Aguardando aprovação', 'Em andamento', 'Concluída', 'Cancelada'];
          foreach ($statusList as $st):
          ?>
            <option value="<?= $st ?>" <?= (($_GET['status'] ?? '') === $st) ? 'selected' : '' ?>>
              <?= $st ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="prioridade" class="form-select" onchange="this.form.submit()" style="width: auto;">
          <option value="">Todas as Prioridades</option>
          <option value="alta" <?= (($_GET['prioridade'] ?? '') === 'alta') ? 'selected' : '' ?>>Alta</option>
          <option value="media" <?= (($_GET['prioridade'] ?? '') === 'media') ? 'selected' : '' ?>>Média</option>
          <option value="baixa" <?= (($_GET['prioridade'] ?? '') === 'baixa') ? 'selected' : '' ?>>Baixa</option>
        </select>

        <?php if ($isAdmin): ?>
          <select name="tecnico_id" class="form-select" onchange="this.form.submit()" style="width: auto;">
            <option value="">Todos os Técnicos</option>
            <?php foreach (($tecnicos ?? []) as $tec): ?>
              <option value="<?= $tec['id'] ?>" <?= (($_GET['tecnico_id'] ?? '') == $tec['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($tec['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>

      <?php if (!empty($_GET['busca']) || !empty($_GET['status']) || !empty($_GET['prioridade']) || !empty($_GET['tecnico_id'])): ?>
        <a href="<?= $baseUrl ?>/ordens" class="btn btn-outline btn-sm">Limpar Filtros</a>
      <?php endif; ?>
    </form>

    <!-- Tabela de Ordens de Serviço -->
    <div class="table-responsive">
      <table class="table-corporate" id="tabelaOrdens">
        <thead>
          <tr>
            <th>Nº OS</th>
            <th>Cliente</th>
            <th>Equipamento</th>
            <th>Técnico</th>
            <th>Prioridade</th>
            <th>Status</th>
            <th>Prazo Previsto</th>
            <th style="text-align: right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($ordens)): ?>
            <tr class="empty-row">
              <td colspan="8" class="empty-state">
                <div class="empty-state-title">Nenhuma ordem de serviço encontrada</div>
                <p>Crie uma nova OS utilizando o botão acima.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($ordens as $os): ?>
              <?php
                $isAtrasada = !empty($os['prazo_violado']);
                $semTecnico = empty($os['tecnico_id']);
              ?>
              <tr>
                <td>
                  <strong>#<?= (int)$os['id'] ?></strong>
                </td>
                <td>
                  <strong><?= htmlspecialchars($os['cliente_nome'] ?? 'Cliente') ?></strong>
                </td>
                <td>
                  <span style="font-weight: 500;"><?= htmlspecialchars($os['equipamento_tipo']) ?></span>
                  <div style="font-size: var(--font-size-xs); color: var(--color-slate-500);">
                    <?= htmlspecialchars("{$os['equipamento_marca']} {$os['equipamento_modelo']}") ?>
                  </div>
                </td>
                <td>
                  <?php if ($semTecnico): ?>
                    <span style="color: var(--color-slate-400); font-style: italic;">Disponível</span>
                    <?php if (!$isAdmin): ?>
                      <form action="<?= $baseUrl ?>/ordens/<?= (int)$os['id'] ?>/assumir" method="POST" style="display: inline; margin-left: 4px;">
                        <button type="submit" class="btn btn-outline btn-sm" title="Assumir esta OS para atendimento">
                          Assumir
                        </button>
                      </form>
                    <?php endif; ?>
                  <?php else: ?>
                    <?= htmlspecialchars($os['tecnico_nome'] ?? '-') ?>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge badge-prioridade-<?= strtolower($os['prioridade'] ?? 'media') ?>">
                    <?= ucfirst(htmlspecialchars($os['prioridade'] ?? 'Média')) ?>
                  </span>
                </td>
                <td>
                  <?= renderStatusBadge($os['status'] ?? 'Aberta') ?>
                </td>
                <td>
                  <div><?= htmlspecialchars($os['prazo_formatado'] ?? '-') ?></div>
                  <?php if ($isAtrasada): ?>
                    <span class="badge-prazo-violado">Prazo violado</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="table-actions" style="justify-content: flex-end;">
                    <a href="<?= $baseUrl ?>/ordens/<?= (int)$os['id'] ?>" class="btn btn-outline btn-sm" title="Ver detalhes">
                      Detalhes
                    </a>
                    <a href="<?= $baseUrl ?>/ordens/<?= (int)$os['id'] ?>/editar" class="btn btn-secondary btn-sm" title="Editar">
                      Editar
                    </a>
                    <a href="<?= $baseUrl ?>/ordens/<?= (int)$os['id'] ?>/imprimir" class="btn btn-outline btn-sm" title="Imprimir OS" target="_blank">
                      🖨️
                    </a>
                    <button
                      type="button"
                      class="btn btn-outline btn-sm"
                      style="color: var(--color-danger);"
                      data-modal-target="modalExclusao"
                      data-record-id="<?= (int)$os['id'] ?>"
                      data-record-name="OS #<?= (int)$os['id'] ?>"
                      onclick="document.getElementById('formConfirmarExclusao').action='<?= $baseUrl ?>/ordens/<?= (int)$os['id'] ?>/excluir'"
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
