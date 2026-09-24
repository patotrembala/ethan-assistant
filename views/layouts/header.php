<?php
/**
 * Ethan Assistant - Header Layout
 * @var string $pageTitle Título da página atual
 */
require_once __DIR__ . '/../../config/helpers.php';

$pageTitle = $pageTitle ?? 'Ethan Assistant - Gestão Técnica';
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Estilos Corporativos Ethan Assistant -->
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/base.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/layout.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/forms.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/print.css" media="print">
</head>
<body>
<div class="app-container">
