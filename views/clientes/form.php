<?php
/**
 * Ethan Assistant - Formulário de Cliente (RF03 / RN01)
 * @var array|null $cliente Dados do cliente para edição (se houver)
 * @var array $errors Lista de erros de validação retornados pelo backend
 */
$isEdit = !empty($cliente['id']);
$pageTitle = ($isEdit ? 'Editar Cliente' : 'Novo Cliente') . ' - Ethan Assistant';
$currentRoute = 'clientes';
$baseUrl = $baseUrl ?? '';
$actionUrl = $isEdit ? "{$baseUrl}/clientes/{$cliente['id']}/atualizar" : "{$baseUrl}/clientes/salvar";

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1><?= $isEdit ? 'Editar Cadastro de Cliente' : 'Cadastrar Novo Cliente' ?></h1>
        <p class="page-subtitle">Preencha os dados empresariais e de contato para atendimento</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/clientes" class="btn btn-outline">
          ← Voltar para listagem
        </a>
      </div>
    </div>

    <div class="card" style="max-width: 900px;">
      <form action="<?= $actionUrl ?>" method="POST" data-validate>
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="card-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label for="razao_social" class="form-label required">Razão Social / Nome Fantasia</label>
              <input
                type="text"
                id="razao_social"
                name="razao_social"
                class="form-control <?= isset($errors['razao_social']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($cliente['razao_social'] ?? '') ?>"
                placeholder="Ex.: Alfa Tecnologia Ltda."
                required
              >
              <?php if (isset($errors['razao_social'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['razao_social']) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="cnpj" class="form-label required">CNPJ (14 dígitos)</label>
              <input
                type="text"
                id="cnpj"
                name="cnpj"
                class="form-control <?= isset($errors['cnpj']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($cliente['cnpj'] ?? '') ?>"
                placeholder="00.000.000/0000-00"
                data-mask="cnpj"
                maxlength="18"
                required
              >
              <?php if (isset($errors['cnpj'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['cnpj']) ?></div>
              <?php else: ?>
                <div class="form-text">Apenas números são armazenados no sistema.</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="endereco" class="form-label required">Endereço Completo</label>
            <input
              type="text"
              id="endereco"
              name="endereco"
              class="form-control <?= isset($errors['endereco']) ? 'is-invalid' : '' ?>"
              value="<?= htmlspecialchars($cliente['endereco'] ?? '') ?>"
              placeholder="Rua, número, complemento, bairro, cidade - UF"
              required
            >
            <?php if (isset($errors['endereco'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['endereco']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="telefone" class="form-label required">Telefone de Contato</label>
              <input
                type="tel"
                id="telefone"
                name="telefone"
                class="form-control <?= isset($errors['telefone']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>"
                placeholder="(00) 00000-0000"
                data-mask="telefone"
                required
              >
              <?php if (isset($errors['telefone'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['telefone']) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="email" class="form-label required">E-mail Comercial</label>
              <input
                type="email"
                id="email"
                name="email"
                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($cliente['email'] ?? '') ?>"
                placeholder="contato@empresa.com.br"
                required
              >
              <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group" style="margin-top: var(--spacing-2);">
            <label class="form-label">Status do Cliente</label>
            <div class="form-check">
              <input
                type="checkbox"
                id="ativo"
                name="ativo"
                value="1"
                class="form-check-input"
                <?= (!isset($cliente['ativo']) || $cliente['ativo'] == 1) ? 'checked' : '' ?>
              >
              <label for="ativo" class="form-check-label">
                Cliente ativo para abertura de novas ordens e chamados
              </label>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <a href="<?= $baseUrl ?>/clientes" class="btn btn-outline">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Cliente' ?>
          </button>
        </div>
      </form>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
