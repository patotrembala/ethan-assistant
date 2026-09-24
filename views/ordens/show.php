<?php
/**
 * Ethan Assistant - Visualização de Detalhes da OS (RF05 / RF07 / RF08 / RF09 / RF18 / RN05 / RN11)
 * @var array $ordem Dados completos da OS
 * @var bool $isAtrasada Flag indicando se o prazo foi violado
 */
$pageTitle = 'OS #' . (int)($ordem['id'] ?? 0) . ' - Ethan Assistant';
$currentRoute = 'ordens';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['id' => 1, 'nome' => 'Técnico', 'perfil' => 'tecnico']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
$isConcluida = in_array($ordem['status'] ?? '', ['Concluída', 'Cancelada']);
$semTecnico = empty($ordem['tecnico_id']);
$isAtrasada = !empty($ordem['prazo_violado']);

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
          <h1>Ordem de Serviço #<?= (int)($ordem['id'] ?? 0) ?></h1>
          <span class="badge badge-status-<?= strtolower(str_replace(' ', '', $ordem['status'] ?? 'aberta')) ?>">
            <?= htmlspecialchars($ordem['status'] ?? 'Aberta') ?>
          </span>
          <?php if (!empty($ordem['prioridade'])): ?>
            <span class="badge badge-prioridade-<?= strtolower($ordem['prioridade']) ?>">
              Prioridade <?= ucfirst(htmlspecialchars($ordem['prioridade'])) ?>
            </span>
          <?php endif; ?>
          <?php if ($isAtrasada): ?>
            <span class="badge-prazo-violado">Prazo violado</span>
          <?php endif; ?>
        </div>
        <p class="page-subtitle">Abertura: <?= htmlspecialchars($ordem['abertura_em_formatada'] ?? '-') ?> | Prazo: <?= htmlspecialchars($ordem['prazo_formatado'] ?? '-') ?></p>
      </div>

      <div class="page-actions">
        <a href="<?= $baseUrl ?>/ordens" class="btn btn-outline">
          ← Voltar
        </a>

        <a href="<?= $baseUrl ?>/ordens/<?= (int)$ordem['id'] ?>/imprimir" class="btn btn-outline" target="_blank">
          🖨️ Imprimir OS
        </a>

        <?php if ($semTecnico && !$isAdmin): ?>
          <form action="<?= $baseUrl ?>/ordens/<?= (int)$ordem['id'] ?>/assumir" method="POST" style="margin: 0;">
            <button type="submit" class="btn btn-primary">
              Assumir esta OS
            </button>
          </form>
        <?php endif; ?>

        <?php if ($isConcluida): ?>
          <!-- Reabertura de OS (RF09 / RN05: permitido para admin e técnico) -->
          <form action="<?= $baseUrl ?>/ordens/<?= (int)$ordem['id'] ?>/reabrir" method="POST" style="margin: 0;">
            <button type="submit" class="btn btn-secondary" onclick="return confirm('Deseja reabrir esta Ordem de Serviço?')">
              🔄 Reabrir OS
            </button>
          </form>
        <?php endif; ?>

        <a href="<?= $baseUrl ?>/ordens/<?= (int)$ordem['id'] ?>/editar" class="btn btn-secondary">
          Editar Dados
        </a>

        <button 
          type="button" 
          class="btn btn-outline" 
          style="color: var(--color-danger);"
          data-modal-target="modalExclusao" 
          data-record-id="<?= (int)$ordem['id'] ?>"
          data-record-name="OS #<?= (int)$ordem['id'] ?>"
          onclick="document.getElementById('formConfirmarExclusao').action='<?= $baseUrl ?>/ordens/<?= (int)$ordem['id'] ?>/excluir'"
        >
          Excluir
        </button>
      </div>
    </div>

    <div class="grid-2-1">
      <!-- Coluna Principal: Equipamento e Histórico de Ocorrência -->
      <div>
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Equipamento em Manutenção</h2>
          </div>
          <div class="card-body">
            <div class="form-grid-3">
              <div>
                <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Tipo</span>
                <span style="font-size: var(--font-size-md); font-weight: 600;"><?= htmlspecialchars($ordem['equipamento_tipo'] ?? '-') ?></span>
              </div>
              <div>
                <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Marca</span>
                <span style="font-size: var(--font-size-md); font-weight: 600;"><?= htmlspecialchars($ordem['equipamento_marca'] ?? '-') ?></span>
              </div>
              <div>
                <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Modelo</span>
                <span style="font-size: var(--font-size-md); font-weight: 600;"><?= htmlspecialchars($ordem['equipamento_modelo'] ?? '-') ?></span>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Defeito Informado pelo Cliente</h2>
          </div>
          <div class="card-body">
            <p style="white-space: pre-line;"><?= htmlspecialchars($ordem['defeito'] ?? 'Sem relato cadastrado.') ?></p>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Diagnóstico Técnico</h2>
          </div>
          <div class="card-body">
            <p style="white-space: pre-line;"><?= htmlspecialchars($ordem['diagnostico'] ?? 'Nenhum diagnóstico registrado até o momento.') ?></p>
          </div>
        </div>

        <?php if (!empty($ordem['observacoes'])): ?>
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">Observações Complementares</h2>
            </div>
            <div class="card-body">
              <p style="white-space: pre-line;"><?= htmlspecialchars($ordem['observacoes']) ?></p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Coluna Lateral: Resumo, Cliente e Responsável -->
      <div>
        <!-- Transição Rápida de Status (RF08) -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Transição de Status</h3>
          </div>
          <div class="card-body">
            <form action="<?= $baseUrl ?>/ordens/<?= (int)$ordem['id'] ?>/atualizar-status" method="POST">
              <div class="form-group">
                <label for="novo_status" class="form-label">Atualizar Situação</label>
                <select name="status" id="novo_status" class="form-select">
                  <?php 
                  $statusList = ['Aberta', 'Em diagnóstico', 'Aguardando aprovação', 'Em andamento', 'Concluída', 'Cancelada'];
                  foreach ($statusList as $st): 
                  ?>
                    <option value="<?= $st ?>" <?= (($ordem['status'] ?? '') === $st) ? 'selected' : '' ?>>
                      <?= $st ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">
                Gravar Status
              </button>
            </form>
          </div>
        </div>

        <!-- Dados do Cliente -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Dados do Cliente</h3>
          </div>
          <div class="card-body">
            <div style="font-weight: 600; font-size: var(--font-size-md); margin-bottom: 4px;">
              <?= htmlspecialchars($ordem['cliente_nome'] ?? 'Cliente') ?>
            </div>
            <div style="font-size: var(--font-size-xs); color: var(--color-slate-500); margin-bottom: var(--spacing-3);">
              CNPJ: <?= htmlspecialchars($ordem['cliente_cnpj'] ?? '-') ?>
            </div>
            <div style="font-size: var(--font-size-sm); margin-bottom: var(--spacing-2);">
              📞 <?= htmlspecialchars($ordem['cliente_telefone'] ?? '-') ?>
            </div>
            <div style="font-size: var(--font-size-sm); margin-bottom: var(--spacing-2);">
              ✉️ <?= htmlspecialchars($ordem['cliente_email'] ?? '-') ?>
            </div>
            <div style="font-size: var(--font-size-sm);">
              📍 <?= htmlspecialchars($ordem['cliente_endereco'] ?? '-') ?>
            </div>
            <div style="margin-top: var(--spacing-4);">
              <a href="<?= $baseUrl ?>/clientes/<?= (int)($ordem['cliente_id'] ?? 0) ?>" class="btn btn-outline btn-sm" style="width: 100%;">
                Abrir Ficha do Cliente
              </a>
            </div>
          </div>
        </div>

        <!-- Responsável Técnico e Prazos -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Atribuição e Cronograma</h3>
          </div>
          <div class="card-body">
            <div style="margin-bottom: var(--spacing-3);">
              <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Técnico Designado</span>
              <div style="font-weight: 600;"><?= htmlspecialchars($ordem['tecnico_nome'] ?? 'Aguardando atribuição') ?></div>
            </div>

            <div style="margin-bottom: var(--spacing-3);">
              <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Tipo de Serviço</span>
              <div><?= htmlspecialchars($ordem['servico_nome'] ?? '-') ?></div>
            </div>

            <div style="margin-bottom: var(--spacing-3);">
              <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Data de Abertura</span>
              <div><?= htmlspecialchars($ordem['abertura_em_formatada'] ?? '-') ?></div>
            </div>

            <div>
              <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Prazo Limite</span>
              <div>
                <?= htmlspecialchars($ordem['prazo_formatado'] ?? '-') ?>
                <?php if ($isAtrasada): ?>
                  <div style="margin-top: 4px;">
                    <span class="badge-prazo-violado">Prazo violado</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($ordem['conclusao_em'])): ?>
              <div style="margin-top: var(--spacing-3); padding-top: var(--spacing-2); border-top: 1px solid var(--color-slate-200);">
                <span class="form-label" style="color: var(--color-slate-500); font-size: var(--font-size-xs);">Concluída em</span>
                <div style="color: var(--color-success); font-weight: 600;">
                  <?= htmlspecialchars($ordem['conclusao_em_formatada'] ?? $ordem['conclusao_em']) ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
