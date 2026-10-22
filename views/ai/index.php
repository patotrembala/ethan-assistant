<?php
$pageTitle = 'Conversa com Ethan - Ethan Assistant';
$currentRoute = 'ethan-ia';
$baseUrl = $baseUrl ?? '';
$aiConfigured = $aiConfigured ?? false;
$aiUsesChatGptPlan = $aiUsesChatGptPlan ?? false;
$aiError = $aiError ?? null;
$chatHistory = $chatHistory ?? [];
$draftQuestion = $draftQuestion ?? '';
$hasConversation = count($chatHistory) > 0;
$isQuotaNotice = is_string($aiError) && str_contains($aiError, 'créditos de IA');

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content ai-workspace-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="ai-chat-shell">
      <header class="ai-chat-topbar">
        <div class="ai-chat-identity">
          <div class="ai-avatar" aria-hidden="true">EA</div>
          <div>
            <div class="ai-chat-title-row">
              <h1>Ethan</h1>
              <span class="ai-online-dot" aria-hidden="true"></span>
              <span class="ai-online-label"><?= $aiConfigured ? 'Disponível' : 'Indisponível' ?></span>
            </div>
            <p>Assistente de diagnóstico técnico</p>
            <?php if ($aiUsesChatGptPlan): ?>
              <span class="ai-plan-badge">Conectado ao seu ChatGPT</span>
            <?php endif; ?>
          </div>
        </div>

        <form method="POST" action="<?= $baseUrl ?>/ethan-ia/chat/limpar">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <button type="submit" class="btn btn-outline btn-sm ai-new-ticket">
            <span aria-hidden="true">＋</span> Novo chamado
          </button>
        </form>
      </header>

      <?php if (!$aiConfigured): ?>
        <div class="alert alert-info ai-chat-alert">
          <span>A chave privada da IA ainda não foi reconhecida neste ambiente.</span>
        </div>
      <?php endif; ?>

      <?php if ($aiError): ?>
        <div class="<?= $isQuotaNotice ? 'ai-service-note' : 'alert alert-danger ai-chat-alert' ?>">
          <?php if ($isQuotaNotice): ?><span class="ai-service-note-icon" aria-hidden="true">i</span><?php endif; ?>
          <span><?= htmlspecialchars($aiError) ?></span>
        </div>
      <?php endif; ?>

      <section class="ai-conversation" data-ai-conversation aria-live="polite" aria-label="Conversa com o Ethan">
        <?php if (!$hasConversation): ?>
          <div class="ai-chat-welcome">
            <div class="ai-welcome-mark" aria-hidden="true">EA</div>
            <span class="ai-welcome-eyebrow">NOVO CHAMADO ASSISTIDO</span>
            <h2>O que está acontecendo?</h2>
            <p>Descreva o problema como se estivesse conversando com outro técnico. Eu farei perguntas, sugerirei testes e ajudarei a registrar a solução.</p>

            <div class="ai-prompt-suggestions" aria-label="Exemplos de chamados">
              <button type="button" data-ai-prompt="O computador liga, mas não apresenta imagem no monitor.">Computador sem vídeo</button>
              <button type="button" data-ai-prompt="O usuário está conectado ao Wi-Fi, mas não consegue acessar a internet.">Sem acesso à internet</button>
              <button type="button" data-ai-prompt="A impressora aparece como offline e não imprime os documentos enviados.">Impressora offline</button>
            </div>
          </div>
        <?php else: ?>
          <div class="ai-conversation-start"><span>Chamado iniciado agora</span></div>
          <?php foreach ($chatHistory as $message): ?>
            <?php $isAssistant = ($message['role'] ?? '') === 'assistant'; ?>
            <article class="ai-chat-message <?= $isAssistant ? 'from-ethan' : 'from-technician' ?>">
              <?php if ($isAssistant): ?><div class="ai-message-avatar" aria-hidden="true">EA</div><?php endif; ?>
              <div class="ai-message-content">
                <div class="ai-message-meta"><?= $isAssistant ? 'Ethan' : 'Você' ?></div>
                <div class="ai-message-bubble"><?= nl2br(htmlspecialchars((string)($message['content'] ?? ''))) ?></div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <footer class="ai-composer-wrap">
        <form method="POST" action="<?= $baseUrl ?>/ethan-ia/perguntar" class="ai-composer" data-ai-chat-form>
          <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <label for="pergunta" class="sr-only">Mensagem para o Ethan</label>
          <textarea id="pergunta" name="pergunta" maxlength="3000" required <?= !$aiConfigured ? 'disabled' : '' ?> placeholder="Descreva o problema, os sintomas e o que você já tentou..." rows="1"><?= htmlspecialchars($draftQuestion) ?></textarea>
          <button type="submit" class="ai-send-button" <?= !$aiConfigured ? 'disabled' : '' ?> aria-label="Enviar mensagem">
            <span>Enviar</span><span aria-hidden="true">➜</span>
          </button>
        </form>
        <p class="ai-composer-hint">Enter para enviar · Shift + Enter para quebrar linha · Não informe senhas ou chaves de acesso.</p>
      </footer>
    </div>
  </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
