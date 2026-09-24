<?php
/**
 * Ethan Assistant - Formulário de Tipo de Serviço (RF11)
 * @var array|null $tipoServico Dados do tipo de serviço
 */
$isEdit = !empty($tipoServico['id']);
$pageTitle = ($isEdit ? 'Editar Tipo de Serviço' : 'Novo Tipo de Serviço') . ' - Ethan Assistant';
$currentRoute = 'tipos-servico';
$baseUrl = $baseUrl ?? '';
$actionUrl = $isEdit ? "{$baseUrl}/tipos-servico/{$tipoServico['id']}/atualizar" : "{$baseUrl}/tipos-servico/salvar";

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1><?= $isEdit ? 'Editar Tipo de Serviço' : 'Novo Tipo de Serviço' ?></h1>
        <p class="page-subtitle">Configure as opções disponíveis para seleção na abertura de Ordens de Serviço</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/tipos-servico" class="btn btn-outline">
          ← Voltar para listagem
        </a>
      </div>
    </div>

    <div class="card" style="max-width: 700px;">
      <form action="<?= $actionUrl ?>" method="POST" data-validate>
        <div class="card-body">
          <div class="form-group">
            <label for="nome" class="form-label required">Nome do Tipo de Serviço</label>
            <input 
              type="text" 
              name="nome" 
              id="nome" 
              class="form-control" 
              value="<?= htmlspecialchars($tipoServico['nome'] ?? '') ?>" 
              placeholder="Ex.: Formatação e Instalação de SO, Troca de Tela, Reparo em Placa-Mãe" 
              required
            >
          </div>

          <div class="form-group">
            <label for="descricao" class="form-label">Descrição do Escopo Padrão</label>
            <textarea 
              name="descricao" 
              id="descricao" 
              class="form-control" 
              placeholder="Descreva resumidamente o que está incluso neste procedimento técnico..."
            ><?= htmlspecialchars($tipoServico['descricao'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Disponibilidade</label>
            <div class="form-check">
              <input 
                type="checkbox" 
                name="ativo" 
                id="ativo" 
                value="1" 
                class="form-check-input" 
                <?= (!isset($tipoServico['ativo']) || $tipoServico['ativo'] == 1) ? 'checked' : '' ?>
              >
              <label for="ativo" class="form-check-label">
                Serviço ativo para seleção em novas ordens de serviço
              </label>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <a href="<?= $baseUrl ?>/tipos-servico" class="btn btn-outline">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Serviço' ?>
          </button>
        </div>
      </form>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
