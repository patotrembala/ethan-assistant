<?php
/**
 * Ethan Assistant - Detalhes do Cliente (RF04)
 * @var array $cliente Dados do cliente
 * @var array $historicoOS Ordens de serviço vinculadas
 * @var array $historicoChamados Chamados online vinculados
 */
$pageTitle = htmlspecialchars($cliente['razao_social'] ?? 'Cliente') . ' - Ethan Assistant';
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
        <h1><?= htmlspecialchars($cliente['razao_social'] ?? 'Ficha do Cliente') ?></h1>
        <p class="page-subtitle">Informações cadastrais e histórico consolidado de atendimentos</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/clientes" class="btn btn-outline">
          ← Voltar
        </a>
        <?php if ($isAdmin): ?>
          <a href="<?= $baseUrl ?>/clientes/<?= (int)($cliente['id'] ?? 0) ?>/editar" class="btn btn-secondary">
            Editar Dados
          </a>
        <?php endif; ?>
        <a href="<?= $baseUrl ?>/ordens/nova?cliente_id=<?= (int)($cliente['id'] ?? 0) ?>" class="btn btn-primary">
          + Abrir OS para este cliente
        </a>
      </div>
    </div>

    <!-- Informações Cadastrais -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Dados Cadastrais</h2>
        <div>
          <?php if (!empty($cliente['ativo'])): ?>
            <span class="badge" style="background-color: var(--color-success-bg); color: var(--color-success); border: 1px solid var(--color-success-border);">
              Ativo
            </span>
          <?php else: ?>
            <span class="badge" style="background-color: var(--color-slate-200); color: var(--color-slate-600); border: 1px solid var(--color-slate-300);">
              Inativo
            </span>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <div class="form-grid-3">
          <div>
            <div class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">CNPJ</div>
            <div style="font-family: var(--font-mono); font-weight: 600;"><?= htmlspecialchars($cliente['cnpj_formatado'] ?? $cliente['cnpj'] ?? '-') ?></div>
          </div>
          <div>
            <div class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Telefone</div>
            <div style="font-weight: 600;"><?= htmlspecialchars($cliente['telefone'] ?? '-') ?></div>
          </div>
          <div>
            <div class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">E-mail</div>
            <div><?= htmlspecialchars($cliente['email'] ?? '-') ?></div>
          </div>
        </div>

        <div style="margin-top: var(--spacing-4);">
          <div class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Endereço</div>
          <div><?= htmlspecialchars($cliente['endereco'] ?? '-') ?></div>
        </div>
      </div>
    </div>

    <!-- Histórico de Ordens de Serviço -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Ordens de Serviço Relacionadas</h2>
      </div>
      <div class="table-responsive">
        <table class="table-corporate">
          <thead>
            <tr>
              <th>OS</th>
              <th>Equipamento</th>
              <th>Técnico</th>
              <th>Status</th>
              <th>Abertura</th>
              <th>Prazo</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($historicoOS)): ?>
              <tr class="empty-row">
                <td colspan="7" class="empty-state">Nenhuma ordem de serviço registrada para este cliente.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($historicoOS as $os): ?>
                <tr>
                  <td><strong>#<?= (int)$os['id'] ?></strong></td>
                  <td><?= htmlspecialchars("{$os['equipamento_tipo']} {$os['equipamento_marca']} {$os['equipamento_modelo']}") ?></td>
                  <td><?= htmlspecialchars($os['tecnico_nome'] ?? 'Não atribuído') ?></td>
                  <td>
                    <span class="badge badge-status-<?= strtolower(str_replace(' ', '', $os['status'] ?? 'aberta')) ?>">
                      <?= htmlspecialchars($os['status'] ?? 'Aberta') ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($os['abertura_em_formatada'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($os['prazo_formatado'] ?? '-') ?></td>
                  <td>
                    <a href="<?= $baseUrl ?>/ordens/<?= (int)$os['id'] ?>" class="btn btn-outline btn-sm">Ver OS</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
