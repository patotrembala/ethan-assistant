document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-auth-form]');
  const passwordInput = document.querySelector('#senha');
  const passwordToggle = document.querySelector('[data-password-toggle]');

  if (passwordInput && passwordToggle) {
    passwordToggle.addEventListener('click', () => {
      const isVisible = passwordInput.type === 'text';
      passwordInput.type = isVisible ? 'password' : 'text';
      passwordToggle.setAttribute('aria-pressed', String(!isVisible));
      passwordToggle.setAttribute('aria-label', isVisible ? 'Mostrar senha' : 'Ocultar senha');
      passwordInput.focus();
    });
  }

  if (!form) return;

  form.querySelectorAll('input[required]').forEach((input) => {
    input.addEventListener('input', () => input.closest('.auth-field')?.classList.remove('is-invalid'));
  });

  form.addEventListener('submit', (event) => {
    let valid = true;
    form.querySelectorAll('input[required]').forEach((input) => {
      const fieldValid = input.value.trim() !== '' && (input.type !== 'email' || input.validity.valid);
      input.closest('.auth-field')?.classList.toggle('is-invalid', !fieldValid);
      if (!fieldValid) valid = false;
    });

    if (!valid) {
      event.preventDefault();
      form.querySelector('.is-invalid input')?.focus();
      return;
    }

    const button = form.querySelector('.auth-submit');
    button?.classList.add('is-loading');
    if (button) button.disabled = true;
  });
});
