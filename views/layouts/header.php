<?php
/**
 * Ethan Assistant - Header Layout
 * @var string $pageTitle Título da página atual
 */
require_once __DIR__ . '/../../config/helpers.php';

$pageTitle = $pageTitle ?? 'Ethan Assistant - Gestão Técnica';
$baseUrl = $baseUrl ?? '';
$assetVersion = '20261016-chat2';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Estilos Corporativos Ethan Assistant -->
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/base.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/layout.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/forms.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/print.css?v=<?= $assetVersion ?>" media="print">
</head>
<body>
<div class="app-container">
