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
    <div class="sidebar-brand">
      <div class="brand-symbol" aria-hidden="true">EA</div>
      <div class="brand-copy">
        <strong>Ethan Assistant</strong>
        <span>Gestão técnica</span>
      </div>
    </div>
    <span class="brand-badge"><?= $isAdmin ? 'Admin' : 'Técnico' ?></span>
    <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Fechar menu">✕</button>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-title">Operação</div>
    <ul class="nav-list">
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/dashboard" class="nav-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="4" rx="2"/><rect x="14" y="11" width="7" height="10" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg></span>
          <span class="nav-label">Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/ordens" class="nav-link <?= str_starts_with($currentRoute, 'ordens') ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 0-5-5l2.1 2.1-2.4 2.4-2.1-2.1a4 4 0 0 0 5 5l6.7 6.7a2 2 0 0 1-2.8 2.8l-6.7-6.7"/><path d="m5 19 3.5-3.5"/></svg></span>
          <span class="nav-label">Ordens de Serviço</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/chamados" class="nav-link <?= str_starts_with($currentRoute, 'chamados') ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8M8 13h5"/></svg></span>
          <span class="nav-label">Chamados Online</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/pendencias" class="nav-link <?= str_starts_with($currentRoute, 'pendencias') ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h4"/></svg></span>
          <span class="nav-label">Pendências Internas</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/clientes" class="nav-link <?= str_starts_with($currentRoute, 'clientes') ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 9h2a2 2 0 0 1 2 2v10M8 7h4M8 11h4M8 15h4M9 21v-2h2v2M2 21h20"/></svg></span>
          <span class="nav-label">Clientes</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/ethan-ia" class="nav-link <?= $currentRoute === 'ethan-ia' ? 'active' : '' ?>">
          <span class="icon icon-ai"><svg viewBox="0 0 24 24"><path d="m12 3 1.3 4.2L17.5 8.5l-4.2 1.3L12 14l-1.3-4.2-4.2-1.3 4.2-1.3z"/><path d="m18 14 .8 2.2L21 17l-2.2.8L18 20l-.8-2.2L15 17l2.2-.8z"/></svg></span>
          <span class="nav-label">Ethan IA</span>
          <span class="nav-beta">IA</span>
        </a>
      </li>
    </ul>

    <?php if ($isAdmin): ?>
    <div class="nav-section-title">Administração</div>
    <ul class="nav-list">
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/tipos-servico" class="nav-link <?= str_starts_with($currentRoute, 'tipos-servico') ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><path d="M20.6 13.6 11 23l-9-9V3h11z"/><circle cx="8" cy="8" r="1.5"/></svg></span>
          <span class="nav-label">Tipos de Serviço</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/usuarios" class="nav-link <?= str_starts_with($currentRoute, 'usuarios') ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg></span>
          <span class="nav-label">Técnicos & Usuários</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/recuperacoes-senha" class="nav-link <?= str_starts_with($currentRoute, 'recuperacoes-senha') ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><circle cx="8" cy="15" r="4"/><path d="m11 12 8-8M16 4l4 4M15 8l2 2"/></svg></span>
          <span class="nav-label">Recuperações de Senha</span>
          <?php if (!empty($pendingPasswordResetCount)): ?><span class="nav-count"><?= (int)$pendingPasswordResetCount ?></span><?php endif; ?>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/auditoria" class="nav-link <?= $currentRoute === 'auditoria' ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h7"/><path d="M14 2v6h6M8 13h4M8 17h3"/><circle cx="17" cy="17" r="3"/><path d="m19.2 19.2 2 2"/></svg></span>
          <span class="nav-label">Auditoria</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= $baseUrl ?>/solicitacoes-privacidade" class="nav-link <?= $currentRoute === 'solicitacoes-privacidade' ? 'active' : '' ?>">
          <span class="icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg></span>
          <span class="nav-label">Solicitações LGPD</span>
        </a>
      </li>
    </ul>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-status"><span class="status-indicator" aria-hidden="true"></span><span>Sistema operacional</span></div>
    <div class="sidebar-version"><span>Ethan Assistant</span><span>v1.0</span></div>
  </div>
</aside>
