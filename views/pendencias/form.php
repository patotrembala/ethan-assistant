<?php
/**
 * Ethan Assistant - Formulário de Pendência Interna (RF13 / RN12)
 * @var array|null $pendencia Dados da pendência
 * @var array $usuarios Lista de responsáveis (técnicos e administradores)
 * @var array $errors Erros de validação
 */
$isEdit = !empty($pendencia['id']);
$pageTitle = ($isEdit ? 'Editar Pendência' : 'Nova Pendência Interna') . ' - Ethan Assistant';
$currentRoute = 'pendencias';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['id' => 1, 'nome' => 'Técnico', 'perfil' => 'tecnico']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
$actionUrl = $isEdit ? "{$baseUrl}/pendencias/{$pendencia['id']}/atualizar" : "{$baseUrl}/pendencias/salvar";
$prazoDefault = date('Y-m-d\TH:i', strtotime('+1 day'));

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1><?= $isEdit ? 'Editar Pendência Interna' : 'Cadastrar Pendência Interna' ?></h1>
        <p class="page-subtitle">Organize tarefas internas e rotinas operacionais da equipe</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/pendencias" class="btn btn-outline">
          ← Voltar para listagem
        </a>
      </div>
    </div>

    <div class="card" style="max-width: 900px;">
      <form action="<?= $actionUrl ?>" method="POST" data-validate>
        <div class="card-body">
          <div class="form-group">
            <label for="titulo" class="form-label required">Título da Pendência</label>
            <input 
              type="text" 
              name="titulo" 
              id="titulo" 
              class="form-control" 
              value="<?= htmlspecialchars($pendencia['titulo'] ?? '') ?>" 
              placeholder="Ex.: Realizar backup semanal do servidor local" 
              required
            >
          </div>

          <div class="form-group">
            <label for="descricao" class="form-label">Descrição Detalhada</label>
            <textarea 
              name="descricao" 
              id="descricao" 
              class="form-control" 
              placeholder="Orientações e procedimentos necessários para conclusão desta pendência..."
            ><?= htmlspecialchars($pendencia['descricao'] ?? '') ?></textarea>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="responsavel_id" class="form-label required">Responsável</label>
              <?php if ($isAdmin): ?>
                <select name="responsavel_id" id="responsavel_id" class="form-select" required>
                  <option value="">Selecione o responsável...</option>
                  <?php foreach (($usuarios ?? []) as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ((string)($pendencia['responsavel_id'] ?? '') === (string)$u['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($u['nome']) ?> (<?= $u['perfil'] === 'admin' ? 'Admin' : 'Técnico' ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              <?php else: ?>
                <input type="hidden" name="responsavel_id" value="<?= htmlspecialchars($currentUser['id']) ?>">
                <input type="text" class="form-control" value="<?= htmlspecialchars($currentUser['nome']) ?>" disabled readonly>
                <div class="form-text">Pendência atribuída ao técnico logado.</div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="prioridade" class="form-label required">Prioridade</label>
              <select name="prioridade" id="prioridade" class="form-select" required>
                <option value="baixa" <?= (($pendencia['prioridade'] ?? 'media') === 'baixa') ? 'selected' : '' ?>>Baixa</option>
                <option value="media" <?= (($pendencia['prioridade'] ?? 'media') === 'media') ? 'selected' : '' ?>>Média</option>
                <option value="alta" <?= (($pendencia['prioridade'] ?? 'media') === 'alta') ? 'selected' : '' ?>>Alta</option>
              </select>
            </div>
          </div>

          <div class="form-grid-3">
            <div class="form-group">
              <label for="prazo" class="form-label required">Prazo Limite</label>
              <input 
                type="datetime-local" 
                name="prazo" 
                id="prazo" 
                class="form-control" 
                value="<?= htmlspecialchars(!empty($pendencia['prazo']) ? date('Y-m-d\TH:i', strtotime($pendencia['prazo'])) : $prazoDefault) ?>" 
                required
              >
            </div>

            <div class="form-group">
              <label for="status" class="form-label required">Status</label>
              <select name="status" id="status" class="form-select" required>
                <option value="Pendente" <?= (($pendencia['status'] ?? 'Pendente') === 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                <option value="Em andamento" <?= (($pendencia['status'] ?? '') === 'Em andamento') ? 'selected' : '' ?>>Em andamento</option>
                <option value="Concluída" <?= (($pendencia['status'] ?? '') === 'Concluída') ? 'selected' : '' ?>>Concluída</option>
                <option value="Cancelada" <?= (($pendencia['status'] ?? '') === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
              </select>
            </div>

            <div class="form-group">
              <label for="concluido_em" class="form-label">Data de Conclusão</label>
              <input 
                type="datetime-local" 
                name="concluido_em" 
                id="concluido_em" 
                class="form-control" 
                value="<?= htmlspecialchars(!empty($pendencia['concluido_em']) ? date('Y-m-d\TH:i', strtotime($pendencia['concluido_em'])) : '') ?>"
              >
            </div>
          </div>
        </div>

        <div class="card-footer">
          <a href="<?= $baseUrl ?>/pendencias" class="btn btn-outline">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Salvar Modificações' : 'Criar Pendência' ?>
          </button>
        </div>
      </form>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
