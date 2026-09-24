/**
 * Ethan Assistant - Main Application Scripts
 */
document.addEventListener('DOMContentLoaded', () => {
  initMasks();
  initModals();
  initFlashDismiss();
  initTableSearch();
});

/**
 * Máscaras para campos corporativos (CNPJ e Telefone)
 */
function initMasks() {
  const cnpjInputs = document.querySelectorAll('input[data-mask="cnpj"]');
  cnpjInputs.forEach(input => {
    input.addEventListener('input', (e) => {
      let v = e.target.value.replace(/\D/g, '').substring(0, 14);
      if (v.length > 12) {
        v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})$/, '$1.$2.$3/$4-$5');
      } else if (v.length > 8) {
        v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{1,4})$/, '$1.$2.$3/$4');
      } else if (v.length > 5) {
        v = v.replace(/^(\d{2})(\d{3})(\d{1,3})$/, '$1.$2.$3');
      } else if (v.length > 2) {
        v = v.replace(/^(\d{2})(\d{1,3})$/, '$1.$2');
      }
      e.target.value = v;
    });
  });

  const telInputs = document.querySelectorAll('input[data-mask="telefone"]');
  telInputs.forEach(input => {
    input.addEventListener('input', (e) => {
      let v = e.target.value.replace(/\D/g, '').substring(0, 11);
      if (v.length > 10) {
        v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
      } else if (v.length > 6) {
        v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
      } else if (v.length > 2) {
        v = v.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
      }
      e.target.value = v;
    });
  });
}

/**
 * Controle de Modais corporativos
 */
function initModals() {
  document.querySelectorAll('[data-modal-target]').forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = trigger.getAttribute('data-modal-target');
      const modal = document.getElementById(targetId);
      if (modal) {
        modal.classList.add('active');
        // Se houver form de ação dinâmica (ex: exclusão)
        const recordId = trigger.getAttribute('data-record-id');
        const recordName = trigger.getAttribute('data-record-name');
        const form = modal.querySelector('form');
        if (form && recordId) {
          const idInput = form.querySelector('input[name="id"]');
          if (idInput) idInput.value = recordId;
        }
        if (recordName) {
          const nameSpan = modal.querySelector('.modal-record-name');
          if (nameSpan) nameSpan.textContent = recordName;
        }
      }
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-backdrop');
      if (modal) modal.classList.remove('active');
    });
  });

  // Fechar ao clicar fora da caixa do modal
  document.querySelectorAll('.modal-backdrop').forEach(modal => {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.remove('active');
      }
    });
  });
}

/**
 * Auto-dismiss suave para alertas flash
 */
function initFlashDismiss() {
  const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.4s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 6000);
  });
}

/**
 * Busca rápida instantânea na tabela
 */
function initTableSearch() {
  const searchInput = document.querySelector('input[data-table-filter]');
  if (!searchInput) return;

  const tableId = searchInput.getAttribute('data-table-filter');
  const table = document.getElementById(tableId);
  if (!table) return;

  searchInput.addEventListener('keyup', (e) => {
    const term = e.target.value.toLowerCase().trim();
    const rows = table.querySelectorAll('tbody tr:not(.empty-row)');

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(term) ? '' : 'none';
    });
  });
}
