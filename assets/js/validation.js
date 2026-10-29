/**
 * Ethan Assistant - Form Validations (RNF05 / Seção 13)
 * Validações no navegador com mensagens em conformidade com a especificação do projeto.
 */
document.addEventListener('DOMContentLoaded', () => {
  initFormValidation();
});

function initFormValidation() {
  const forms = document.querySelectorAll('form[data-validate]');

  forms.forEach(form => {
    form.addEventListener('submit', (e) => {
      let isValid = true;
      clearErrors(form);

      // 1. Validação de Datas da OS (Prazo não deve anteceder a abertura)
      const aberturaInput = form.querySelector('input[name="abertura_em"]');
      const prazoInput = form.querySelector('input[name="prazo_previsto"]');
      if (aberturaInput && prazoInput && aberturaInput.value && prazoInput.value) {
        const dataAbertura = new Date(aberturaInput.value);
        const dataPrazo = new Date(prazoInput.value);

        if (dataPrazo < dataAbertura) {
          showFieldError(prazoInput, 'O prazo deve ser posterior à abertura.');
          isValid = false;
        }
      }

      // 2. Validação de CNPJ (14 dígitos numéricos)
      const cnpjInput = form.querySelector('input[name="cnpj"]');
      if (cnpjInput && cnpjInput.hasAttribute('required')) {
        const rawCnpj = cnpjInput.value.replace(/\D/g, '');
        if (rawCnpj.length !== 14) {
          showFieldError(cnpjInput, 'CNPJ deve conter 14 dígitos válidos.');
          isValid = false;
        }
      }

      // 3. Validação de campos obrigatórios genéricos
      const requiredInputs = form.querySelectorAll('[required]');
      requiredInputs.forEach(input => {
        if (!input.value.trim()) {
          showFieldError(input, 'Preencha este campo obrigatório.');
          isValid = false;
        }
      });

      if (!isValid) {
        e.preventDefault();
        // Focar no primeiro campo inválido
        const firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      // Impede cliques duplos e requisições repetidas enquanto o cadastro é processado.
      if (form.dataset.submitting === 'true') {
        e.preventDefault();
        return;
      }

      form.dataset.submitting = 'true';
      form.setAttribute('aria-busy', 'true');
      const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.dataset.originalLabel = submitButton.value || submitButton.textContent || '';
        if (submitButton.tagName === 'INPUT') submitButton.value = 'Salvando...';
        else submitButton.textContent = 'Salvando...';
      }
    });
  });

  // Restaura o formulário ao voltar pelo histórico do navegador.
  window.addEventListener('pageshow', () => {
    forms.forEach(form => {
      delete form.dataset.submitting;
      form.removeAttribute('aria-busy');
      const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
      if (!submitButton) return;
      submitButton.disabled = false;
      const originalLabel = submitButton.dataset.originalLabel;
      if (!originalLabel) return;
      if (submitButton.tagName === 'INPUT') submitButton.value = originalLabel;
      else submitButton.textContent = originalLabel;
    });
  });
}

function showFieldError(input, message) {
  input.classList.add('is-invalid');
  let feedback = input.nextElementSibling;
  if (!feedback || !feedback.classList.contains('invalid-feedback')) {
    feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    input.parentNode.appendChild(feedback);
  }
  feedback.textContent = message;
}

function clearErrors(form) {
  form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
}
