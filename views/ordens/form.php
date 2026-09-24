<?php
/**
 * Ethan Assistant - Formulário de Ordem de Serviço (RF05 / RF06 / RF07 / RF08 / RF10 / RN04 / RN06 / RN07)
 * @var array|null $ordem Dados da OS (para edição)
 * @var array $clientes Lista de clientes cadastrados
 * @var array $tiposServico Lista de tipos de serviço configurados
 * @var array $tecnicos Lista de técnicos do sistema
 * @var array $errors Lista de erros de validação
 */
$isEdit = !empty($ordem['id']);
$pageTitle = ($isEdit ? 'Editar OS #' . (int)$ordem['id'] : 'Nova Ordem de Serviço') . ' - Ethan Assistant';
$currentRoute = 'ordens';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['id' => 1, 'nome' => 'Técnico', 'perfil' => 'tecnico']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
$actionUrl = $isEdit ? "{$baseUrl}/ordens/{$ordem['id']}/atualizar" : "{$baseUrl}/ordens/salvar";

// Horários default para nova OS
$agora = date('Y-m-d\TH:i');
$prazoDefault = date('Y-m-d\TH:i', strtotime('+2 days'));

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1><?= $isEdit ? 'Editar Ordem de Serviço #' . (int)$ordem['id'] : 'Abertura de Ordem de Serviço' ?></h1>
        <p class="page-subtitle">Registro detalhado de atendimento técnico e prazos operacionais</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/ordens" class="btn btn-outline">
          ← Voltar para listagem
        </a>
      </div>
    </div>

    <form action="<?= $actionUrl ?>" method="POST" data-validate style="max-width: 1000px;">
      <!-- Seção 1: Cliente e Serviço -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">1. Dados do Cliente e Tipo de Atendimento</h2>
        </div>
        <div class="card-body">
          <div class="form-grid-3">
            <div class="form-group">
              <label for="cliente_id" class="form-label required">Cliente / Empresa</label>
              <select name="cliente_id" id="cliente_id" class="form-select <?= isset($errors['cliente_id']) ? 'is-invalid' : '' ?>" required>
                <option value="">Selecione um cliente...</option>
                <?php foreach (($clientes ?? []) as $cli): ?>
                  <option value="<?= $cli['id'] ?>" <?= ((string)($ordem['cliente_id'] ?? ($_GET['cliente_id'] ?? '')) === (string)$cli['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cli['razao_social']) ?> (CNPJ: <?= htmlspecialchars($cli['cnpj']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['cliente_id'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['cliente_id']) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="tipo_servico_id" class="form-label required">Tipo de Serviço</label>
              <select name="tipo_servico_id" id="tipo_servico_id" class="form-select <?= isset($errors['tipo_servico_id']) ? 'is-invalid' : '' ?>" required>
                <option value="">Selecione o serviço...</option>
                <?php foreach (($tiposServico ?? []) as $ts): ?>
                  <option value="<?= $ts['id'] ?>" <?= ((string)($ordem['tipo_servico_id'] ?? '') === (string)$ts['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ts['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['tipo_servico_id'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['tipo_servico_id']) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="tecnico_id" class="form-label">Técnico Responsável</label>
              <?php if ($isAdmin): ?>
                <!-- Administrador pode atribuir livremente a qualquer técnico (RF06) -->
                <select name="tecnico_id" id="tecnico_id" class="form-select">
                  <option value="">Aguardando atribuição (Disponível)</option>
                  <?php foreach (($tecnicos ?? []) as $tec): ?>
                    <option value="<?= $tec['id'] ?>" <?= ((string)($ordem['tecnico_id'] ?? '') === (string)$tec['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($tec['nome']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="form-text">Administrador pode distribuir e reatribuir livremente entre técnicos.</div>
              <?php else: ?>
                <!-- Técnico: Não pode atribuir a outro técnico -->
                <?php
                  $ordemTecnicoId = $ordem['tecnico_id'] ?? null;
                  $pertenceAOutro = !empty($ordemTecnicoId) && (string)$ordemTecnicoId !== (string)$currentUser['id'];
                ?>
                <?php if ($pertenceAOutro): ?>
                  <!-- OS atribuída a outro técnico: bloqueado para alteração por técnicos -->
                  <input type="hidden" name="tecnico_id" value="<?= htmlspecialchars($ordemTecnicoId) ?>">
                  <input 
                    type="text" 
                    class="form-control" 
                    value="<?= htmlspecialchars($ordem['tecnico_nome'] ?? 'Outro Técnico') ?>" 
                    disabled 
                    readonly
                  >
                  <div class="form-text" style="color: var(--color-danger);">
                    Esta OS já pertence a outro técnico. Apenas administradores podem transferi-la.
                  </div>
                <?php else: ?>
                  <!-- Nova OS ou OS própria/disponível: técnico só pode atribuir a si mesmo ou deixar disponível -->
                  <select name="tecnico_id" id="tecnico_id" class="form-select">
                    <option value="<?= $currentUser['id'] ?>" <?= (!empty($ordemTecnicoId) && (string)$ordemTecnicoId === (string)$currentUser['id']) || empty($ordem) ? 'selected' : '' ?>>
                      Atribuído a mim (<?= htmlspecialchars($currentUser['nome']) ?>)
                    </option>
                    <option value="" <?= (isset($ordem) && empty($ordemTecnicoId)) ? 'selected' : '' ?>>
                      Aguardando atribuição (Disponível)
                    </option>
                  </select>
                  <div class="form-text">Técnicos só podem assumir OS para si mesmos ou deixá-la disponível.</div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Seção 2: Identificação do Equipamento (RN07: apenas tipo, marca e modelo) -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">2. Identificação do Equipamento</h2>
        </div>
        <div class="card-body">
          <div class="form-grid-3">
            <div class="form-group">
              <label for="equipamento_tipo" class="form-label required">Tipo de Equipamento</label>
              <input 
                type="text" 
                id="equipamento_tipo" 
                name="equipamento_tipo" 
                class="form-control <?= isset($errors['equipamento_tipo']) ? 'is-invalid' : '' ?>" 
                value="<?= htmlspecialchars($ordem['equipamento_tipo'] ?? '') ?>" 
                placeholder="Ex.: Notebook, Servidor, Desktop" 
                required
              >
              <?php if (isset($errors['equipamento_tipo'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['equipamento_tipo']) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="equipamento_marca" class="form-label required">Marca</label>
              <input 
                type="text" 
                id="equipamento_marca" 
                name="equipamento_marca" 
                class="form-control <?= isset($errors['equipamento_marca']) ? 'is-invalid' : '' ?>" 
                value="<?= htmlspecialchars($ordem['equipamento_marca'] ?? '') ?>" 
                placeholder="Ex.: Dell, Lenovo, HP" 
                required
              >
              <?php if (isset($errors['equipamento_marca'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['equipamento_marca']) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="equipamento_modelo" class="form-label required">Modelo</label>
              <input 
                type="text" 
                id="equipamento_modelo" 
                name="equipamento_modelo" 
                class="form-control <?= isset($errors['equipamento_modelo']) ? 'is-invalid' : '' ?>" 
                value="<?= htmlspecialchars($ordem['equipamento_modelo'] ?? '') ?>" 
                placeholder="Ex.: Vostro 3520, ThinkPad E14" 
                required
              >
              <?php if (isset($errors['equipamento_modelo'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['equipamento_modelo']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Seção 3: Detalhamento Técnico -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">3. Detalhamento e Ocorrência</h2>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="defeito" class="form-label required">Defeito Relatado (Problema do Cliente)</label>
            <textarea 
              name="defeito" 
              id="defeito" 
              class="form-control <?= isset($errors['defeito']) ? 'is-invalid' : '' ?>" 
              placeholder="Descreva detalhadamente o sintoma ou falha informada pelo cliente..." 
              required
            ><?= htmlspecialchars($ordem['defeito'] ?? '') ?></textarea>
            <?php if (isset($errors['defeito'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['defeito']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="diagnostico" class="form-label">Diagnóstico Técnico</label>
            <textarea 
              name="diagnostico" 
              id="diagnostico" 
              class="form-control" 
              placeholder="Constatações técnicas, testes realizados ou peças identificadas..."
            ><?= htmlspecialchars($ordem['diagnostico'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label for="observacoes" class="form-label">Observações Complementares</label>
            <textarea 
              name="observacoes" 
              id="observacoes" 
              class="form-control" 
              placeholder="Acessórios entregues, senhas de acesso, autorizações específicas..."
            ><?= htmlspecialchars($ordem['observacoes'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Seção 4: Status, Prioridade e Prazos (Regras Críticas RN04 e RN06) -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">4. Controle Operacional, Prioridade e Prazos</h2>
        </div>
        <div class="card-body">
          <div class="form-grid-4">
            <!-- Prioridade (RN04: Somente administrador define ou altera) -->
            <div class="form-group">
              <label for="prioridade" class="form-label required">Prioridade</label>
              <?php if ($isAdmin): ?>
                <select name="prioridade" id="prioridade" class="form-select" required>
                  <option value="baixa" <?= (($ordem['prioridade'] ?? 'media') === 'baixa') ? 'selected' : '' ?>>Baixa</option>
                  <option value="media" <?= (($ordem['prioridade'] ?? 'media') === 'media') ? 'selected' : '' ?>>Média</option>
                  <option value="alta" <?= (($ordem['prioridade'] ?? 'media') === 'alta') ? 'selected' : '' ?>>Alta</option>
                </select>
                <div class="form-text">Definida pelo Administrador.</div>
              <?php else: ?>
                <!-- Campo desabilitado para Técnico com campo oculto para preservar valor -->
                <input type="hidden" name="prioridade" value="<?= htmlspecialchars($ordem['prioridade'] ?? 'media') ?>">
                <input 
                  type="text" 
                  class="form-control" 
                  value="<?= ucfirst(htmlspecialchars($ordem['prioridade'] ?? 'Média')) ?>" 
                  disabled 
                  readonly
                >
                <div class="form-text" style="color: var(--color-slate-500);">Alterável somente pelo administrador (RN04).</div>
              <?php endif; ?>
            </div>

            <!-- Status da OS (RF08) -->
            <div class="form-group">
              <label for="status" class="form-label required">Status da OS</label>
              <select name="status" id="status" class="form-select" required>
                <?php 
                $statusList = ['Aberta', 'Em diagnóstico', 'Aguardando aprovação', 'Em andamento', 'Concluída', 'Cancelada'];
                foreach ($statusList as $st): 
                ?>
                  <option value="<?= $st ?>" <?= (($ordem['status'] ?? 'Aberta') === $st) ? 'selected' : '' ?>>
                    <?= $st ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Data de Abertura -->
            <div class="form-group">
              <label for="abertura_em" class="form-label required">Data de Abertura</label>
              <input 
                type="datetime-local" 
                name="abertura_em" 
                id="abertura_em" 
                class="form-control" 
                value="<?= htmlspecialchars(!empty($ordem['abertura_em']) ? date('Y-m-d\TH:i', strtotime($ordem['abertura_em'])) : $agora) ?>" 
                required
              >
            </div>

            <!-- Prazo Previsto (Seção 13: O prazo deve ser posterior à abertura) -->
            <div class="form-group">
              <label for="prazo_previsto" class="form-label required">Prazo Previsto</label>
              <input 
                type="datetime-local" 
                name="prazo_previsto" 
                id="prazo_previsto" 
                class="form-control <?= isset($errors['prazo_previsto']) ? 'is-invalid' : '' ?>" 
                value="<?= htmlspecialchars(!empty($ordem['prazo_previsto']) ? date('Y-m-d\TH:i', strtotime($ordem['prazo_previsto'])) : $prazoDefault) ?>" 
                required
              >
              <?php if (isset($errors['prazo_previsto'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['prazo_previsto']) ?></div>
              <?php else: ?>
                <div class="form-text">Deve ser posterior à data de abertura.</div>
              <?php endif; ?>
            </div>
          </div>

          <?php if ($isEdit && in_array($ordem['status'] ?? '', ['Concluída', 'Cancelada'])): ?>
            <div class="form-group" style="max-width: 300px; margin-top: var(--spacing-2);">
              <label for="conclusao_em" class="form-label">Data de Conclusão / Encerramento</label>
              <input 
                type="datetime-local" 
                name="conclusao_em" 
                id="conclusao_em" 
                class="form-control" 
                value="<?= htmlspecialchars(!empty($ordem['conclusao_em']) ? date('Y-m-d\TH:i', strtotime($ordem['conclusao_em'])) : $agora) ?>"
              >
            </div>
          <?php endif; ?>
        </div>

        <div class="card-footer">
          <a href="<?= $baseUrl ?>/ordens" class="btn btn-outline">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Salvar Modificações na OS' : 'Criar Ordem de Serviço' ?>
          </button>
        </div>
      </div>
    </form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
