// Titan Hello Call Inbox helpers (lightweight, no dependencies)
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-titanhello-filters]');
  if (!form) return;

  // Auto-submit on select changes
  form.querySelectorAll('select[data-autosubmit]').forEach(sel => {
    sel.addEventListener('change', () => form.submit());
  });

  // Quick clear
  const clearBtn = document.querySelector('[data-titanhello-clear]');
  if (clearBtn) {
    clearBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.location = clearBtn.getAttribute('href');
    });
  }
});
