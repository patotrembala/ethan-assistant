<?php
/**
 * Ethan Assistant - Sidebar Navigation
 * @var string $currentRoute Rota ativa para marcar o item selecionado
 * @var array $currentUser Dados do usuário logado na sessão
 */
$currentRoute = $currentRoute ?? 'dashboard';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['nome' => 'Usuário', 'perfil' => 'admin']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
$baseUrl = $baseUrl ?? '';
?>
<aside class="app-sidebar">
  <div class="sidebar-header">
    <div class="brand-title">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
        <line x1="8" y1="21" x2="16" y2="21"></line>
        <line x1="12" y1="17" x2="12" y2="21"></line>
      </svg>
      Ethan Assistant
      <span class="brand-badge"><?= $isAdmin ? 'Admin' : 'Técnico' ?></span>
    </div>
    <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Fechar menu">✕</button>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-title">Operação</div>
    <ul class="nav-list">
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/dashboard" class="nav-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
          <span class="icon">📊</span>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/ordens" class="nav-link <?= str_starts_with($currentRoute, 'ordens') ? 'active' : '' ?>">
          <span class="icon">🛠️</span>
          <span>Ordens de Serviço</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/chamados" class="nav-link <?= str_starts_with($currentRoute, 'chamados') ? 'active' : '' ?>">
          <span class="icon">💬</span>
          <span>Chamados Online</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/pendencias" class="nav-link <?= str_starts_with($currentRoute, 'pendencias') ? 'active' : '' ?>">
          <span class="icon">📋</span>
          <span>Pendências Internas</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/clientes" class="nav-link <?= str_starts_with($currentRoute, 'clientes') ? 'active' : '' ?>">
          <span class="icon">🏢</span>
          <span>Clientes</span>
        </a>
      </li>
    </ul>

    <?php if ($isAdmin): ?>
    <div class="nav-section-title">Administração</div>
    <ul class="nav-list">
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/tipos-servico" class="nav-link <?= str_starts_with($currentRoute, 'tipos-servico') ? 'active' : '' ?>">
          <span class="icon">🏷️</span>
          <span>Tipos de Serviço</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/usuarios" class="nav-link <?= str_starts_with($currentRoute, 'usuarios') ? 'active' : '' ?>">
          <span class="icon">👥</span>
          <span>Técnicos & Usuários</span>
        </a>
      </li>
    </ul>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <div>Ethan Assistant v1.0</div>
    <div style="font-size: 11px; margin-top: 4px; color: var(--color-slate-500);">Uso interno corporativo</div>
  </div>
</aside>
