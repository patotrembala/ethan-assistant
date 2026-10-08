<?php $pageTitle = 'Privacidade - Ethan Assistant'; $baseUrl = $baseUrl ?? ''; ?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= htmlspecialchars($pageTitle) ?></title><link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/base.css"><link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css"><link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/forms.css"></head>
<body style="background: var(--color-slate-50);"><main style="max-width: 900px; margin: 0 auto; padding: 40px 20px;">
  <div class="card"><div class="card-body">
    <p class="badge badge-status-em-andamento">AVISO DE PRIVACIDADE</p><h1 style="margin: 16px 0 8px;">Privacidade no Ethan Assistant</h1><p>Última atualização: 8 de outubro de 2026.</p>
    <h2>Responsável pelo tratamento</h2><p><strong><?= htmlspecialchars($privacyController) ?></strong><?php if ($privacyEmail): ?> — contato: <a href="mailto:<?= htmlspecialchars($privacyEmail) ?>"><?= htmlspecialchars($privacyEmail) ?></a><?php endif; ?>.</p>
    <h2>Dados e finalidades</h2><p>Tratamos dados de identificação e contato de clientes, usuários e técnicos; informações de equipamentos; ordens de serviço; chamados; pendências e registros de segurança. As finalidades são executar e organizar o atendimento técnico, controlar acessos, prevenir fraude, cumprir obrigações e proteger direitos.</p>
    <h2>Compartilhamentos</h2><p>Os dados podem ser tratados por fornecedores de hospedagem, banco de dados e e-mail estritamente para operar o sistema. Não vendemos dados pessoais.</p>
    <h2>Retenção e segurança</h2><p>Os dados são mantidos pelo período necessário às finalidades e obrigações aplicáveis. Utilizamos controle de acesso, hash de senhas, HTTPS, registros de auditoria, autenticação em duas etapas para administradores e tokens temporários de recuperação.</p>
    <h2>Seus direitos</h2><p>Você pode solicitar confirmação, acesso, correção, informações, bloqueio, eliminação e portabilidade quando aplicáveis. A identidade poderá ser confirmada antes da resposta para proteger os próprios dados.</p>
    <h2>Cookies</h2><p>O sistema utiliza apenas cookie essencial de sessão para autenticação e segurança. Não há publicidade comportamental.</p>
    <p style="margin-top: 24px;"><a class="btn btn-primary" href="<?= $baseUrl ?>/solicitacao-privacidade">Fazer solicitação de privacidade</a> <a class="btn btn-outline" href="<?= $baseUrl ?>/login">Voltar ao login</a></p>
  </div></div>
</main></body></html>
