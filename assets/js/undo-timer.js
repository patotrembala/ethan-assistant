/**
 * Ethan Assistant - Undo Timer Component (RF14 / RN08)
 * Gerencia a espera de 3 minutos para exclusões solicitadas por técnicos.
 */
document.addEventListener('DOMContentLoaded', () => {
  initUndoTimer();
});

function initUndoTimer() {
  const banner = document.getElementById('undoBanner');
  if (!banner) return;

  const secondsAttr = banner.getAttribute('data-seconds-left');
  let secondsLeft = secondsAttr ? parseInt(secondsAttr, 10) : 180;
  const countdownEl = banner.querySelector('.undo-countdown');

  if (secondsLeft <= 0) {
    banner.style.display = 'none';
    return;
  }

  function formatTime(totalSeconds) {
    const mins = Math.floor(totalSeconds / 60);
    const secs = totalSeconds % 60;
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
  }

  countdownEl.textContent = formatTime(secondsLeft);

  const timer = setInterval(() => {
    secondsLeft--;
    if (secondsLeft <= 0) {
      clearInterval(timer);
      banner.style.transition = 'opacity 0.5s ease';
      banner.style.opacity = '0';
      setTimeout(() => banner.remove(), 500);
    } else {
      countdownEl.textContent = formatTime(secondsLeft);
    }
  }, 1000);
}
