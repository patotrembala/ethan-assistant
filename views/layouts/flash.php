<?php
/**
 * Ethan Assistant - Flash Messages & Undo Notification (RF14 / RF20)
 * @var array $flash Mensagens flash da sessão: ['tipo' => 'success'|'danger'|'info', 'mensagem' => '...']
 * @var array $pendingDeletion Dados de exclusão pendente de técnico com janela de 3 minutos (RN08)
 */
$flash = $flash ?? ($_SESSION['flash'] ?? null);
$pendingDeletion = $pendingDeletion ?? ($_SESSION['pending_deletion'] ?? null);
$baseUrl = $baseUrl ?? '';
?>

<?php if ($flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['tipo'] ?? 'info') ?>" data-auto-dismiss>
    <div style="display: flex; align-items: center; gap: var(--spacing-2);">
      <?php if (($flash['tipo'] ?? '') === 'success'): ?>
        <span>✓</span>
      <?php elseif (($flash['tipo'] ?? '') === 'danger'): ?>
        <span>⚠</span>
      <?php else: ?>
        <span>ℹ</span>
      <?php endif; ?>
      <span><?= htmlspecialchars($flash['mensagem'] ?? '') ?></span>
    </div>
    <button type="button" class="btn btn-link btn-sm" onclick="this.closest('.alert').remove()" style="color: inherit; text-decoration: none; padding: 0;">✕</button>
  </div>
<?php endif; ?>

<?php if ($pendingDeletion): ?>
  <!-- Banner Flutuante de Desfazer Exclusão (3 Minutos - RF14 / RN08) -->
  <div class="undo-banner" id="undoBanner" data-seconds-left="<?= (int)($pendingDeletion['seconds_left'] ?? 180) ?>">
    <div style="display: flex; flex-direction: column;">
      <span style="font-weight: 600; font-size: var(--font-size-sm);">
        Exclusão agendada: <?= htmlspecialchars($pendingDeletion['descricao'] ?? 'Registro') ?>
      </span>
      <span style="font-size: var(--font-size-xs); color: var(--color-slate-400);">
        Você pode desfazer esta ação dentro de <span class="undo-countdown">3:00</span>
      </span>
    </div>

    <form action="<?= $baseUrl ?>/exclusoes/<?= urlencode($pendingDeletion['id'] ?? 0) ?>/desfazer" method="POST" style="margin: 0;">
      <button type="submit" class="btn btn-undo btn-sm">
        Desfazer Exclusão
      </button>
    </form>
  </div>
<?php endif; ?>
