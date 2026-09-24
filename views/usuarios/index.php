<?php
/**
 * Ethan Assistant - Gestão de Técnicos e Usuários (RF19)
 * @var array $usuarios Lista de usuários cadastrados
 */
$pageTitle = 'Técnicos & Usuários - Ethan Assistant';
$currentRoute = 'usuarios';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['perfil' => 'admin']);

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1>Gestão de Técnicos & Usuários</h1>
        <p class="page-subtitle">Controle de acessos, credenciais e perfis de permissão do sistema</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/usuarios/novo" class="btn btn-primary">
          <span>+ Novo Usuário</span>
        </a>
      </div>
    </div>

    <!-- Tabela Corporativa de Usuários -->
    <div class="table-responsive">
      <table class="table-corporate">
        <thead>
          <tr>
            <th>Nome</th>
            <th>E-mail Corporativo</th>
            <th>Perfil de Acesso</th>
            <th>Status</th>
            <th>Última Atualização</th>
            <th style="text-align: right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($usuarios)): ?>
            <tr class="empty-row">
              <td colspan="6" class="empty-state">Nenhum usuário encontrado.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($usuarios as $u): ?>
              <tr>
                <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                  <?php if ($u['perfil'] === 'admin'): ?>
                    <span class="badge" style="background-color: var(--color-slate-900); color: var(--color-white);">
                      Administrador
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); border: 1px solid var(--color-primary-border);">
                      Técnico
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($u['ativo'])): ?>
                    <span class="badge" style="background-color: var(--color-success-bg); color: var(--color-success); border: 1px solid var(--color-success-border);">
                      Ativo
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background-color: var(--color-slate-200); color: var(--color-slate-600); border: 1px solid var(--color-slate-300);">
                      Inativo
                    </span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($u['atualizado_em_formatado'] ?? '-') ?></td>
                <td>
                  <div class="table-actions" style="justify-content: flex-end;">
                    <a href="<?= $baseUrl ?>/usuarios/<?= (int)$u['id'] ?>/editar" class="btn btn-secondary btn-sm">
                      Editar / Senha
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
