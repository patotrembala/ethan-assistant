<?php
/**
 * Ethan Assistant - Topbar
 * @var array $currentUser
 */
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['nome' => 'Usuário Sistema', 'email' => 'admin@ethan.local', 'perfil' => 'admin']);
$initials = strtoupper(substr($currentUser['nome'] ?? 'U', 0, 2));
$baseUrl = $baseUrl ?? '';
?>
<header class="app-topbar">
  <div class="topbar-left">
    <button type="button" class="btn-sidebar-toggle" id="btnSidebarToggle" aria-label="Abrir menu de navegação">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>
    <span style="font-size: var(--font-size-sm); color: var(--color-slate-500); font-weight: 500;">
      Assistência Técnica & Gestão Operacional
    </span>
  </div>

  <div class="topbar-right">
    <div class="user-profile-menu">
      <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
      <div class="user-info-text">
        <span class="user-name"><?= htmlspecialchars($currentUser['nome'] ?? 'Usuário') ?></span>
        <span class="user-role-tag"><?= ($currentUser['perfil'] ?? '') === 'admin' ? 'Administrador' : 'Técnico de Suporte' ?></span>
      </div>
    </div>

    <form method="POST" action="<?= $baseUrl ?>/logout" style="margin-left: var(--spacing-2);">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <button type="submit" class="btn btn-outline btn-sm" title="Sair do sistema">
        <span>Sair</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
      </button>
    </form>
  </div>
</header>
