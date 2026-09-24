<?php
/**
 * Ethan Assistant - Impressão da Ordem de Serviço (RF18)
 * @var array $ordem Dados completos da OS
 */
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>OS #<?= (int)($ordem['id'] ?? 0) ?> - Impressão</title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/base.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/print.css">
  <style>
    body {
      background: #f1f5f9;
      padding: 20px;
    }
    .print-sheet {
      background: #ffffff;
      max-width: 800px;
      margin: 0 auto;
      padding: 30px;
      border: 1px solid #cbd5e1;
      border-radius: 4px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .print-bar {
      max-width: 800px;
      margin: 0 auto 15px auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  </style>
</head>
<body>

<div class="print-bar no-print">
  <a href="<?= $baseUrl ?>/ordens/<?= (int)($ordem['id'] ?? 0) ?>" class="btn btn-outline btn-sm">← Voltar para o sistema</a>
  <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">🖨️ Imprimir Ordem de Serviço</button>
</div>

<div class="print-sheet print-page">
  <div class="print-header">
    <div>
      <div class="print-title">Ethan Assistant — Assistência Técnica</div>
      <div style="font-size: 9pt; color: #555555; margin-top: 4px;">Comprovante de Atendimento e Ordem de Serviço</div>
    </div>
    <div style="text-align: right;">
      <div class="print-os-number">OS Nº <?= sprintf('%06d', (int)($ordem['id'] ?? 0)) ?></div>
      <div style="font-size: 9pt; color: #555555;">Status: <?= htmlspecialchars($ordem['status'] ?? 'Aberta') ?></div>
    </div>
  </div>

  <!-- Dados do Cliente -->
  <div class="print-section">
    <div class="print-section-title">Dados do Cliente</div>
    <div class="print-grid">
      <div><strong>Razão Social:</strong> <?= htmlspecialchars($ordem['cliente_nome'] ?? '-') ?></div>
      <div><strong>CNPJ:</strong> <?= htmlspecialchars($ordem['cliente_cnpj'] ?? '-') ?></div>
      <div><strong>Telefone:</strong> <?= htmlspecialchars($ordem['cliente_telefone'] ?? '-') ?></div>
      <div><strong>E-mail:</strong> <?= htmlspecialchars($ordem['cliente_email'] ?? '-') ?></div>
      <div style="grid-column: span 2;"><strong>Endereço:</strong> <?= htmlspecialchars($ordem['cliente_endereco'] ?? '-') ?></div>
    </div>
  </div>

  <!-- Equipamento e Serviço -->
  <div class="print-section">
    <div class="print-section-title">Identificação do Equipamento & Serviço</div>
    <div class="print-grid">
      <div><strong>Tipo de Equipamento:</strong> <?= htmlspecialchars($ordem['equipamento_tipo'] ?? '-') ?></div>
      <div><strong>Marca / Modelo:</strong> <?= htmlspecialchars("{$ordem['equipamento_marca']} / {$ordem['equipamento_modelo']}") ?></div>
      <div><strong>Tipo de Serviço:</strong> <?= htmlspecialchars($ordem['servico_nome'] ?? '-') ?></div>
      <div><strong>Técnico Responsável:</strong> <?= htmlspecialchars($ordem['tecnico_nome'] ?? 'Aguardando') ?></div>
      <div><strong>Data de Entrada:</strong> <?= htmlspecialchars($ordem['abertura_em_formatada'] ?? '-') ?></div>
      <div><strong>Previsão de Entrega:</strong> <?= htmlspecialchars($ordem['prazo_formatado'] ?? '-') ?></div>
    </div>
  </div>

  <!-- Defeito e Diagnóstico -->
  <div class="print-section">
    <div class="print-section-title">Defeito Reclamado pelo Cliente</div>
    <div style="font-size: 10pt; min-height: 40px; white-space: pre-line;">
      <?= htmlspecialchars($ordem['defeito'] ?? 'Nenhum defeito informado.') ?>
    </div>
  </div>

  <div class="print-section">
    <div class="print-section-title">Diagnóstico Técnico e Parecer</div>
    <div style="font-size: 10pt; min-height: 50px; white-space: pre-line;">
      <?= htmlspecialchars($ordem['diagnostico'] ?? 'Diagnóstico em elaboração.') ?>
    </div>
  </div>

  <?php if (!empty($ordem['observacoes'])): ?>
    <div class="print-section">
      <div class="print-section-title">Observações Complementares</div>
      <div style="font-size: 9pt; white-space: pre-line;">
        <?= htmlspecialchars($ordem['observacoes']) ?>
      </div>
    </div>
  <?php endif; ?>

  <div style="font-size: 8pt; color: #666666; margin-top: 15px; text-align: justify; line-height: 1.4;">
    Declaro que o equipamento acima descrito foi entregue com os acessórios e condições relatadas. O prazo para retirada após conclusão é de 30 dias corridos, sob pena de cobrança de taxa de guarda.
  </div>

  <div class="print-signatures">
    <div class="signature-box">
      <strong>Assinatura do Técnico</strong><br>
      <?= htmlspecialchars($ordem['tecnico_nome'] ?? 'Técnico Responsável') ?>
    </div>
    <div class="signature-box">
      <strong>Assinatura do Cliente</strong><br>
      <?= htmlspecialchars($ordem['cliente_nome'] ?? 'Representante Autorizado') ?>
    </div>
  </div>
</div>

</body>
</html>
