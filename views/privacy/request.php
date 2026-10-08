<?php $pageTitle = 'Solicitação de privacidade - Ethan Assistant'; $baseUrl = $baseUrl ?? ''; ?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= htmlspecialchars($pageTitle) ?></title><link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/base.css"><link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css"><link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/forms.css"></head>
<body style="background: var(--color-slate-50);"><main style="max-width: 720px; margin: 0 auto; padding: 40px 20px;"><div class="card"><div class="card-body">
  <h1>Solicitação de privacidade</h1><p>Use este canal para exercer direitos relacionados aos seus dados pessoais. Não informe senhas, códigos ou dados de terceiros.</p>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><strong><?= htmlspecialchars($success) ?></strong></div><?php else: ?>
  <form method="POST" action="<?= $baseUrl ?>/solicitacao-privacidade">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <div class="form-group"><label class="form-label required" for="nome">Nome completo</label><input class="form-control" id="nome" name="nome" maxlength="120" required value="<?= htmlspecialchars($requestData['nome'] ?? '') ?>"></div>
    <div class="form-group"><label class="form-label required" for="email">E-mail</label><input class="form-control" type="email" id="email" name="email" maxlength="190" required value="<?= htmlspecialchars($requestData['email'] ?? '') ?>"></div>
    <div class="form-group"><label class="form-label required" for="tipo">Tipo de solicitação</label><select class="form-select" id="tipo" name="tipo" required><option value="">Selecione...</option><option value="acesso">Acesso aos dados</option><option value="correcao">Correção</option><option value="eliminacao">Eliminação</option><option value="bloqueio">Bloqueio ou anonimização</option><option value="portabilidade">Portabilidade</option><option value="informacao">Informações sobre o tratamento</option><option value="outro">Outro</option></select></div>
    <div class="form-group"><label class="form-label required" for="descricao">Descrição</label><textarea class="form-control" id="descricao" name="descricao" minlength="10" maxlength="2000" required><?= htmlspecialchars($requestData['descricao'] ?? '') ?></textarea></div>
    <button class="btn btn-primary" type="submit">Registrar solicitação</button> <a class="btn btn-outline" href="<?= $baseUrl ?>/privacidade">Cancelar</a>
  </form><?php endif; ?>
</div></div></main></body></html>
