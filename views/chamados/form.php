<?php
/**
 * Ethan Assistant - Formulário de Chamado Online (RF12 / RN12)
 * @var array|null $chamado Dados do chamado (para edição)
 * @var array $clientes Lista de clientes cadastrados
 * @var array $tecnicos Lista de técnicos do sistema
 * @var array $errors Erros de validação
 */
$isEdit = !empty($chamado['id']);
$pageTitle = ($isEdit ? 'Atender Chamado #' . (int)$chamado['id'] : 'Novo Chamado Online') . ' - Ethan Assistant';
$currentRoute = 'chamados';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['id' => 1, 'nome' => 'Técnico', 'perfil' => 'tecnico']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
$actionUrl = $isEdit ? "{$baseUrl}/chamados/{$chamado['id']}/atualizar" : "{$baseUrl}/chamados/salvar";
$agora = date('Y-m-d\TH:i');

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1><?= $isEdit ? 'Atendimento de Chamado Online #' . (int)$chamado['id'] : 'Novo Chamado Online' ?></h1>
        <p class="page-subtitle">Suporte remoto direto ao cliente</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/chamados" class="btn btn-outline">
          ← Voltar para listagem
        </a>
      </div>
    </div>

    <div class="card" style="max-width: 900px;">
      <form action="<?= $actionUrl ?>" method="POST" data-validate>
        <div class="card-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label for="cliente_id" class="form-label required">Cliente / Empresa Solicitante</label>
              <select name="cliente_id" id="cliente_id" class="form-select" required>
                <option value="">Selecione o cliente...</option>
                <?php foreach (($clientes ?? []) as $cli): ?>
                  <option value="<?= $cli['id'] ?>" <?= ((string)($chamado['cliente_id'] ?? '') === (string)$cli['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cli['razao_social']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="tecnico_id" class="form-label required">Técnico Responsável</label>
              <?php if ($isAdmin): ?>
                <select name="tecnico_id" id="tecnico_id" class="form-select" required>
                  <option value="">Selecione o técnico atendente...</option>
                  <?php foreach (($tecnicos ?? []) as $tec): ?>
                    <option value="<?= $tec['id'] ?>" <?= ((string)($chamado['tecnico_id'] ?? '') === (string)$tec['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($tec['nome']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              <?php else: ?>
                <input type="hidden" name="tecnico_id" value="<?= htmlspecialchars($currentUser['id']) ?>">
                <input type="text" class="form-control" value="<?= htmlspecialchars($currentUser['nome']) ?>" disabled readonly>
                <div class="form-text">Atendimento realizado pelo técnico logado.</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="descricao" class="form-label required">Descrição da Solicitação / Problema</label>
            <textarea 
              name="descricao" 
              id="descricao" 
              class="form-control" 
              placeholder="Descreva a demanda relatada pelo cliente para este atendimento remoto..." 
              required
            ><?= htmlspecialchars($chamado['descricao'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label for="solucao" class="form-label">Solução Aplicada / Procedimentos Realizados</label>
            <textarea 
              name="solucao" 
              id="solucao" 
              class="form-control" 
              placeholder="Descreva as ações realizadas para solucionar o chamado (obrigatório para finalizar)..."
            ><?= htmlspecialchars($chamado['solucao'] ?? '') ?></textarea>
          </div>

          <div class="form-grid-3">
            <div class="form-group">
              <label for="status" class="form-label required">Status do Chamado</label>
              <select name="status" id="status" class="form-select" required>
                <option value="Aberto" <?= (($chamado['status'] ?? 'Aberto') === 'Aberto') ? 'selected' : '' ?>>Aberto</option>
                <option value="Em atendimento" <?= (($chamado['status'] ?? '') === 'Em atendimento') ? 'selected' : '' ?>>Em atendimento</option>
                <option value="Concluído" <?= (($chamado['status'] ?? '') === 'Concluído') ? 'selected' : '' ?>>Concluído</option>
                <option value="Cancelado" <?= (($chamado['status'] ?? '') === 'Cancelado') ? 'selected' : '' ?>>Cancelado</option>
              </select>
            </div>

            <div class="form-group">
              <label for="aberto_em" class="form-label required">Data de Abertura</label>
              <input 
                type="datetime-local" 
                name="aberto_em" 
                id="aberto_em" 
                class="form-control" 
                value="<?= htmlspecialchars(!empty($chamado['aberto_em']) ? date('Y-m-d\TH:i', strtotime($chamado['aberto_em'])) : $agora) ?>" 
                required
              >
            </div>

            <div class="form-group">
              <label for="concluido_em" class="form-label">Data de Conclusão</label>
              <input 
                type="datetime-local" 
                name="concluido_em" 
                id="concluido_em" 
                class="form-control" 
                value="<?= htmlspecialchars(!empty($chamado['concluido_em']) ? date('Y-m-d\TH:i', strtotime($chamado['concluido_em'])) : '') ?>"
              >
            </div>
          </div>
        </div>

        <div class="card-footer">
          <a href="<?= $baseUrl ?>/chamados" class="btn btn-outline">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Salvar Alterações' : 'Registrar Chamado' ?>
          </button>
        </div>
      </form>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
