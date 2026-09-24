<?php
/**
 * Ethan Assistant - Footer Layout
 * Inclui modal global de confirmação de exclusão e scripts
 */
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['perfil' => 'admin']);
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
?>
      </div><!-- /.app-content -->
    </main><!-- /.app-main -->
  </div><!-- /.app-container -->

  <!-- Modal Global de Exclusão (RN08) -->
  <div class="modal-backdrop" id="modalExclusao">
    <div class="modal-dialog">
      <div class="modal-header">
        <h3 class="modal-title">Confirmar Exclusão</h3>
        <button type="button" class="btn btn-outline btn-sm" data-modal-close>✕</button>
      </div>
      <form id="formConfirmarExclusao" method="POST" action="">
        <div class="modal-body">
          <input type="hidden" name="id" value="">
          <p>Você tem certeza que deseja excluir <strong class="modal-record-name">este registro</strong>?</p>
          <?php if ($isAdmin): ?>
            <p style="font-size: var(--font-size-xs); color: var(--color-slate-500); margin-top: 8px;">
              Como administrador, a exclusão será processada diretamente conforme regras de integridade do sistema.
            </p>
          <?php else: ?>
            <p style="font-size: var(--font-size-xs); color: var(--color-slate-500); margin-top: 8px;">
              A exclusão ficará agendada por <strong>3 minutos</strong>, período no qual você poderá desfazer a ação.
            </p>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
          <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts Globais -->
  <script src="<?= $baseUrl ?>/assets/js/app.js"></script>
  <script src="<?= $baseUrl ?>/assets/js/undo-timer.js"></script>
  <script src="<?= $baseUrl ?>/assets/js/validation.js"></script>
</body>
</html>
