<?php
/**
 * Assistance - Header Layout
 * @var string $pageTitle Título da página atual
 */
require_once __DIR__ . '/../../config/helpers.php';

$pageTitle = $pageTitle ?? 'Assistance - Gestão Técnica';
$baseUrl = $baseUrl ?? '';
$assetVersion = '20261029-userfix1';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <script>
    (() => {
      try {
        if (localStorage.getItem('ethan-theme') === 'dark') {
          document.documentElement.dataset.theme = 'dark';
        }
      } catch (error) {}
    })();
  </script>

  <!-- Estilos Corporativos Assistance -->
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/base.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/layout.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/forms.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/dark.css?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/print.css?v=<?= $assetVersion ?>" media="print">
<style>
.app-content:not(.dashboard-view):not(.ai-workspace-content){font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:var(--color-slate-50,#f5f7fb)}
.app-content:not(.dashboard-view):not(.ai-workspace-content) h1{font-size:26px;letter-spacing:-.7px;line-height:1.3}
.app-content:not(.dashboard-view):not(.ai-workspace-content) .page-subtitle{font-size:12px;line-height:1.8}
.app-content .card{border-radius:13px;overflow:hidden}.app-content .card-header{padding:20px}.app-content .card-body{padding:22px}.app-content .card-title{font-size:15px;font-weight:650}
.app-content .form-control,.app-content .form-select{border-radius:8px;min-height:40px;font-size:12px}.app-content textarea.form-control{min-height:110px;line-height:1.7}
.app-content .form-label{font-size:12px;font-weight:600}.app-content .form-text{font-size:11px;line-height:1.7}.app-content .btn{border-radius:8px}.app-content .filter-bar{border-radius:12px;flex-wrap:wrap;gap:12px}.app-content .filter-group{flex-wrap:wrap;gap:10px}
.app-content .table-responsive{border-radius:12px;border:1px solid var(--color-slate-200);overflow-x:auto}.app-content .table-corporate th{font-size:10px;letter-spacing:.6px;padding:14px}.app-content .table-corporate td{font-size:12px;line-height:1.7;padding:16px 14px}
.operation-timeline{padding-left:20px;margin:0}.operation-timeline li{padding:9px 0;border-bottom:1px solid var(--color-slate-200);font-size:12px}.operation-timeline small{display:block;color:var(--color-slate-500);margin:4px 0}.operation-timeline p{margin:3px 0}
.app-content .metric-label[href]{text-decoration:none}.app-content .metric-label[href]:hover{text-decoration:underline}
@media(max-width:760px){.app-content .form-grid-2,.app-content .form-grid-3,.app-content .form-grid-4{grid-template-columns:minmax(0,1fr)}.app-content .page-header{align-items:flex-start;flex-direction:column;gap:16px}.app-content .page-actions{flex-wrap:wrap;gap:8px}.app-content .filter-group{width:100%}.app-content .card-body{padding:16px}.app-content .filter-bar select{max-width:100%}.app-content .form-control,.app-content .form-select{font-size:16px}.app-content .table-corporate{min-width:650px}}
</style>
<style>@media(max-width:600px){.app-topbar{padding:10px 14px;gap:12px;min-height:60px}.app-topbar .topbar-left{flex:0 0 auto;min-width:0}.app-topbar .topbar-left>span{display:none}.app-topbar .topbar-right{flex:1;min-width:0;justify-content:flex-end;gap:10px}.app-topbar .user-info-text{display:none}.app-topbar .theme-toggle-label{display:none}.app-topbar .user-profile-menu{padding:0;gap:0}.app-topbar .topbar-right form{margin-left:0!important}.app-main{min-width:0}.app-content{min-width:0}.app-content .filter-bar form{max-width:100%}}</style>
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/mobile.css?v=<?= $assetVersion ?>">
</head>
<body>
<div class="app-container">
