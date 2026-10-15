<?php
$pageTitle = 'Ethan IA - Ethan Assistant';
$currentRoute = 'ethan-ia';
$baseUrl = $baseUrl ?? '';
$aiConfigured = $aiConfigured ?? false;
$analysis = $analysis ?? null;
$report = $report ?? null;
$aiError = $aiError ?? null;
$activeTool = $activeTool ?? 'analise';
$chatHistory = $chatHistory ?? [];
$formData = $formData ?? [];

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1>✨ Ethan IA</h1>
        <p class="page-subtitle">Apoio inteligente para diagnóstico, documentação e dúvidas técnicas.</p>
      </div>
      <span class="badge <?= $aiConfigured ? 'badge-status-concluida' : 'badge-status-aguardando' ?>">
        <?= $aiConfigured ? 'IA configurada' : 'Configuração pendente' ?>
      </span>
    </div>

    <?php if (!$aiConfigured): ?>
      <div class="alert alert-info">
        <span>A interface está pronta, mas o administrador ainda precisa configurar a chave privada da IA neste ambiente.</span>
      </div>
    <?php endif; ?>

    <div class="ai-notice">
      <strong>Uso responsável:</strong> não informe senhas, códigos, chaves de acesso ou dados pessoais desnecessários. As respostas são sugestões e devem ser validadas por um técnico.
    </div>

    <?php if ($aiError): ?>
      <div class="alert alert-danger"><span><?= htmlspecialchars($aiError) ?></span></div>
    <?php endif; ?>

    <div class="ai-tool-grid">
      <section class="card ai-tool-card <?= $activeTool === 'analise' ? 'ai-tool-active' : '' ?>">
        <div class="card-header">
          <div><div class="ai-tool-icon">🔎</div><h2 class="card-title">Analisar chamado</h2></div>
          <span class="badge badge-status-diagnostico">Diagnóstico</span>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/ethan-ia/analisar">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <div class="card-body">
            <div class="form-group">
              <label for="descricao_chamado" class="form-label required">Descrição do chamado</label>
              <textarea id="descricao_chamado" name="descricao" class="form-control ai-input-large" maxlength="5000" required placeholder="Ex.: Após uma queda de energia, três computadores ligam, mas não acessam a rede..."><?= htmlspecialchars((string)($formData['descricao'] ?? '')) ?></textarea>
              <div class="form-text">O Ethan sugere categoria, prioridade, causa, diagnóstico e próximos passos.</div>
            </div>
          </div>
          <div class="card-footer"><button class="btn btn-primary" type="submit" <?= !$aiConfigured ? 'disabled' : '' ?>>Analisar com Ethan</button></div>
        </form>

        <?php if (is_array($analysis)): ?>
          <div class="ai-result">
            <div class="ai-result-header"><strong>Análise sugerida</strong><span class="badge badge-prioridade-<?= htmlspecialchars($analysis['prioridade'] ?? 'media') ?>"><?= ucfirst(htmlspecialchars($analysis['prioridade'] ?? 'média')) ?></span></div>
            <dl class="ai-analysis-grid">
              <div><dt>Categoria</dt><dd><?= ucfirst(htmlspecialchars($analysis['categoria'] ?? '-')) ?></dd></div>
              <div><dt>Possível causa</dt><dd><?= htmlspecialchars($analysis['possivel_causa'] ?? '-') ?></dd></div>
            </dl>
            <h3>Diagnóstico sugerido</h3><p><?= nl2br(htmlspecialchars($analysis['diagnostico_sugerido'] ?? '-')) ?></p>
            <?php foreach (['proximos_passos' => 'Próximos passos', 'perguntas_ao_usuario' => 'Perguntas ao usuário', 'alertas' => 'Alertas'] as $key => $title): ?>
              <?php if (!empty($analysis[$key])): ?>
                <h3><?= $title ?></h3><ol><?php foreach ($analysis[$key] as $item): ?><li><?= htmlspecialchars((string)$item) ?></li><?php endforeach; ?></ol>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section class="card ai-tool-card <?= $activeTool === 'relatorio' ? 'ai-tool-active' : '' ?>">
        <div class="card-header">
          <div><div class="ai-tool-icon">📝</div><h2 class="card-title">Relatório de encerramento</h2></div>
          <span class="badge badge-status-concluida">Documentação</span>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/ethan-ia/relatorio">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <div class="card-body">
            <div class="form-group"><label for="problema" class="form-label required">Problema relatado</label><textarea id="problema" name="problema" class="form-control" maxlength="4000" required><?= htmlspecialchars((string)($formData['problema'] ?? '')) ?></textarea></div>
            <div class="form-group"><label for="acoes" class="form-label required">Procedimentos realizados</label><textarea id="acoes" name="acoes" class="form-control ai-input-large" maxlength="5000" required><?= htmlspecialchars((string)($formData['acoes'] ?? '')) ?></textarea></div>
            <div class="form-group"><label for="resultado" class="form-label">Resultado dos testes</label><textarea id="resultado" name="resultado" class="form-control" maxlength="2000"><?= htmlspecialchars((string)($formData['resultado'] ?? '')) ?></textarea></div>
          </div>
          <div class="card-footer"><button class="btn btn-primary" type="submit" <?= !$aiConfigured ? 'disabled' : '' ?>>Gerar relatório</button></div>
        </form>
        <?php if (is_string($report) && $report !== ''): ?>
          <div class="ai-result"><div class="ai-result-header"><strong>Relatório gerado</strong><button type="button" class="btn btn-outline btn-sm" data-copy-target="relatorioGerado">Copiar</button></div><div id="relatorioGerado" class="ai-report-text"><?= nl2br(htmlspecialchars($report)) ?></div></div>
        <?php endif; ?>
      </section>
    </div>

    <section class="card ai-chat-card <?= $activeTool === 'chat' ? 'ai-tool-active' : '' ?>">
      <div class="card-header">
        <div><div class="ai-tool-icon">💬</div><h2 class="card-title">Pergunte ao Ethan</h2></div>
        <?php if ($chatHistory): ?><form method="POST" action="<?= $baseUrl ?>/ethan-ia/chat/limpar"><input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>"><button type="submit" class="btn btn-outline btn-sm">Limpar conversa</button></form><?php endif; ?>
      </div>
      <div class="card-body">
        <div class="ai-chat-history" aria-live="polite">
          <?php if (!$chatHistory): ?>
            <div class="ai-chat-empty"><strong>Como posso ajudar?</strong><span>Pergunte sobre Windows, redes, hardware, impressoras, backups ou procedimentos de suporte.</span></div>
          <?php else: ?>
            <?php foreach ($chatHistory as $message): ?>
              <div class="ai-message ai-message-<?= ($message['role'] ?? '') === 'assistant' ? 'assistant' : 'user' ?>">
                <strong><?= ($message['role'] ?? '') === 'assistant' ? 'Ethan' : 'Você' ?></strong>
                <div><?= nl2br(htmlspecialchars((string)($message['content'] ?? ''))) ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/ethan-ia/perguntar" class="ai-chat-form">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <label for="pergunta" class="form-label required">Sua dúvida técnica</label>
          <div class="ai-chat-input-row"><textarea id="pergunta" name="pergunta" class="form-control" maxlength="3000" required placeholder="Ex.: Quais testes devo fazer quando o computador liga sem exibir vídeo?"></textarea><button class="btn btn-primary" type="submit" <?= !$aiConfigured ? 'disabled' : '' ?>>Enviar</button></div>
        </form>
      </div>
    </section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
