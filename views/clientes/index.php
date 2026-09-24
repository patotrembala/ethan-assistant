<?php
/**
 * Ethan Assistant - Listagem de Clientes (RF03 / RF04 / RN01 / RN02)
 * @var array $clientes Lista de empresas cadastradas
 */
$pageTitle = 'Clientes - Ethan Assistant';
$currentRoute = 'clientes';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['perfil' => 'admin']);
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
        <h1>Gestão de Clientes</h1>
        <p class="page-subtitle">Empresas e contas atendidas pela equipe técnica</p>
      </div>
      <div class="page-actions">
        <?php if ($isAdmin): ?>
          <a href="<?= $baseUrl ?>/clientes/novo" class="btn btn-primary">
            <span>+ Novo Cliente</span>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Barra de Filtros e Busca -->
    <div class="filter-bar">
      <div class="filter-group">
        <div class="search-input-wrap">
          <input
            type="text"
            class="form-control"
            placeholder="Buscar por razão social ou CNPJ..."
            data-table-filter="tabelaClientes"
          >
        </div>
      </div>
      <div style="font-size: var(--font-size-xs); color: var(--color-slate-500);">
        <?= count($clientes ?? []) ?> cliente(s) cadastrado(s)
      </div>
    </div>

    <!-- Tabela Corporativa de Clientes -->
    <div class="table-responsive">
      <table class="table-corporate" id="tabelaClientes">
        <thead>
          <tr>
            <th>Razão Social</th>
            <th>CNPJ</th>
            <th>Telefone</th>
            <th>E-mail</th>
            <th>Status</th>
            <th style="text-align: right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($clientes)): ?>
            <tr class="empty-row">
              <td colspan="6" class="empty-state">
                <div class="empty-state-title">Nenhum cliente cadastrado</div>
                <p>Nenhum registro encontrado no sistema.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($clientes as $c): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($c['razao_social']) ?></strong>
                </td>
                <td style="font-family: var(--font-mono); font-size: var(--font-size-xs);">
                  <?= htmlspecialchars($c['cnpj_formatado'] ?? $c['cnpj']) ?>
                </td>
                <td><?= htmlspecialchars($c['telefone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                <td>
                  <?php if (!empty($c['ativo'])): ?>
                    <span class="badge" style="background-color: var(--color-success-bg); color: var(--color-success); border: 1px solid var(--color-success-border);">
                      Ativo
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background-color: var(--color-slate-200); color: var(--color-slate-600); border: 1px solid var(--color-slate-300);">
                      Inativo
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="table-actions" style="justify-content: flex-end;">
                    <a href="<?= $baseUrl ?>/clientes/<?= (int)$c['id'] ?>" class="btn btn-outline btn-sm">
                      Ver Ficha
                    </a>

                    <?php if ($isAdmin): ?>
                      <a href="<?= $baseUrl ?>/clientes/<?= (int)$c['id'] ?>/editar" class="btn btn-secondary btn-sm">
                        Editar
                      </a>
                      <button
                        type="button"
                        class="btn btn-outline btn-sm"
                        style="color: var(--color-danger);"
                        data-modal-target="modalExclusao"
                        data-record-id="<?= (int)$c['id'] ?>"
                        data-record-name="<?= htmlspecialchars($c['razao_social']) ?>"
                        onclick="document.getElementById('formConfirmarExclusao').action='<?= $baseUrl ?>/clientes/<?= (int)$c['id'] ?>/excluir'"
                      >
                        Excluir
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
