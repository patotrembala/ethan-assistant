<?php
/**
 * Ethan Assistant - Detalhes da Pendência Interna (RF13 / RN11 / RN12)
 * @var array $pendencia Dados da pendência interna
 */
$pageTitle = htmlspecialchars($pendencia['titulo'] ?? 'Pendência') . ' - Ethan Assistant';
$currentRoute = 'pendencias';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['id' => 1, 'nome' => 'Técnico', 'perfil' => 'tecnico']);
$isAtrasada = !empty($pendencia['prazo_violado']);
$isConcluida = in_array($pendencia['status'] ?? '', ['Concluída', 'Cancelada']);

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <div style="display: flex; align-items: center; gap: var(--spacing-3);">
          <h1>Pendência #<?= (int)($pendencia['id'] ?? 0) ?></h1>
          <span class="badge badge-status-<?= strtolower(str_replace([' ', 'í'], ['', 'i'], $pendencia['status'] ?? 'pendente')) ?>">
            <?= htmlspecialchars($pendencia['status'] ?? 'Pendente') ?>
          </span>
          <?php if (!empty($pendencia['prioridade'])): ?>
            <span class="badge badge-prioridade-<?= strtolower($pendencia['prioridade']) ?>">
              Prioridade <?= ucfirst(htmlspecialchars($pendencia['prioridade'])) ?>
            </span>
          <?php endif; ?>
          <?php if ($isAtrasada): ?>
            <span class="badge-prazo-violado">Prazo violado</span>
          <?php endif; ?>
        </div>
        <p class="page-subtitle">Atividade interna da equipe técnica</p>
      </div>

      <div class="page-actions">
        <a href="<?= $baseUrl ?>/pendencias" class="btn btn-outline">
          ← Voltar para listagem
        </a>

        <?php if (!$isConcluida): ?>
          <form action="<?= $baseUrl ?>/pendencias/<?= (int)$pendencia['id'] ?>/concluir" method="POST" style="margin: 0;">
            <button type="submit" class="btn btn-primary">
              ✓ Marcar como Concluída
            </button>
          </form>
        <?php endif; ?>

        <a href="<?= $baseUrl ?>/pendencias/<?= (int)$pendencia['id'] ?>/editar" class="btn btn-secondary">
          Editar
        </a>

        <button 
          type="button" 
          class="btn btn-outline" 
          style="color: var(--color-danger);"
          data-modal-target="modalExclusao" 
          data-record-id="<?= (int)$pendencia['id'] ?>"
          data-record-name="Pendência: <?= htmlspecialchars($pendencia['titulo'] ?? '') ?>"
          onclick="document.getElementById('formConfirmarExclusao').action='<?= $baseUrl ?>/pendencias/<?= (int)$pendencia['id'] ?>/excluir'"
        >
          Excluir
        </button>
      </div>
    </div>

    <div class="grid-2-1">
      <!-- Coluna Principal: Detalhes e Descrição -->
      <div>
        <div class="card">
          <div class="card-header">
            <h2 class="card-title"><?= htmlspecialchars($pendencia['titulo'] ?? '') ?></h2>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Descrição da Tarefa</label>
              <div style="font-size: var(--font-size-base); color: var(--color-slate-800); white-space: pre-line; line-height: 1.6;">
                <?= !empty($pendencia['descricao']) ? htmlspecialchars($pendencia['descricao']) : '<em>Sem descrição detalhada registrada.</em>' ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Coluna Lateral: Responsável e Prazos -->
      <div>
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Informações de Execução</h3>
          </div>
          <div class="card-body">
            <div style="margin-bottom: var(--spacing-4);">
              <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Responsável</span>
              <div style="font-weight: 600; font-size: var(--font-size-md);">
                <?= htmlspecialchars($pendencia['responsavel_nome'] ?? 'Não atribuído') ?>
              </div>
            </div>

            <div style="margin-bottom: var(--spacing-4);">
              <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Prioridade</span>
              <div>
                <span class="badge badge-prioridade-<?= strtolower($pendencia['prioridade'] ?? 'media') ?>">
                  <?= ucfirst(htmlspecialchars($pendencia['prioridade'] ?? 'Média')) ?>
                </span>
              </div>
            </div>

            <div style="margin-bottom: var(--spacing-4);">
              <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Prazo Limite</span>
              <div>
                <?= htmlspecialchars($pendencia['prazo_formatado'] ?? $pendencia['prazo'] ?? '-') ?>
                <?php if ($isAtrasada): ?>
                  <div style="margin-top: 4px;">
                    <span class="badge-prazo-violado">Prazo violado</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($pendencia['concluido_em'])): ?>
              <div style="padding-top: var(--spacing-3); border-top: 1px solid var(--color-slate-200);">
                <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Concluída em</span>
                <div style="color: var(--color-success); font-weight: 600;">
                  <?= htmlspecialchars($pendencia['concluido_em_formatada'] ?? $pendencia['concluido_em']) ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
