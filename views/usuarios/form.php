<?php
/**
 * Ethan Assistant - Formulário de Usuário / Técnico (RF19)
 * @var array|null $usuario Dados do usuário (se edição)
 * @var array $errors Erros de validação
 */
$isEdit = !empty($usuario['id']);
$pageTitle = ($isEdit ? 'Editar Usuário' : 'Novo Usuário') . ' - Ethan Assistant';
$currentRoute = 'usuarios';
$baseUrl = $baseUrl ?? '';
$actionUrl = $isEdit ? "{$baseUrl}/usuarios/{$usuario['id']}/atualizar" : "{$baseUrl}/usuarios/salvar";

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1><?= $isEdit ? 'Editar Usuário / Redefinir Senha' : 'Cadastrar Novo Usuário' ?></h1>
        <p class="page-subtitle">Configuração de credenciais e perfil de acesso ao Ethan Assistant</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/usuarios" class="btn btn-outline">
          ← Voltar para listagem
        </a>
      </div>
    </div>

    <div class="card" style="max-width: 800px;">
      <form action="<?= $actionUrl ?>" method="POST" data-validate>
        <div class="card-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label for="nome" class="form-label required">Nome Completo</label>
              <input
                type="text"
                name="nome"
                id="nome"
                class="form-control"
                value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>"
                placeholder="Ex.: Carlos Mendes"
                required
              >
            </div>

            <div class="form-group">
              <label for="email" class="form-label required">E-mail Corporativo</label>
              <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                placeholder="carlos@empresa.com"
                required
              >
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="perfil" class="form-label required">Perfil de Permissão</label>
              <select name="perfil" id="perfil" class="form-select" required>
                <option value="tecnico" <?= (($usuario['perfil'] ?? 'tecnico') === 'tecnico') ? 'selected' : '' ?>>
                  Técnico (Operacional)
                </option>
                <option value="admin" <?= (($usuario['perfil'] ?? '') === 'admin') ? 'selected' : '' ?>>
                  Administrador (Acesso Total)
                </option>
              </select>
              <div class="form-text">
                Técnicos não gerenciam clientes ou prioridades de OS (RN01, RN04).
              </div>
            </div>

            <div class="form-group">
              <label for="senha" class="form-label <?= $isEdit ? '' : 'required' ?>">
                <?= $isEdit ? 'Nova Senha (opcional)' : 'Senha de Acesso' ?>
              </label>
              <input
                type="password"
                name="senha"
                id="senha"
                class="form-control"
                minlength="12"
                placeholder="<?= $isEdit ? 'Deixe em branco para manter a atual' : 'Mínimo de 12 caracteres' ?>"
                <?= $isEdit ? '' : 'required' ?>
              >
              <div class="form-text"><?= $isEdit ? 'Preencha apenas para redefinir. ' : '' ?>Use maiúscula, minúscula, número e símbolo.</div>
            </div>
          </div>

          <div class="form-group" style="margin-top: var(--spacing-2);">
            <label class="form-label">Situação da Conta</label>
            <div class="form-check">
              <input
                type="checkbox"
                name="ativo"
                id="ativo"
                value="1"
                class="form-check-input"
                <?= (!isset($usuario['ativo']) || $usuario['ativo'] == 1) ? 'checked' : '' ?>
              >
              <label for="ativo" class="form-check-label">
                Conta ativa para autenticação no sistema
              </label>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <a href="<?= $baseUrl ?>/usuarios" class="btn btn-outline">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Salvar Dados' : 'Cadastrar Usuário' ?>
          </button>
        </div>
      </form>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
