document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  document.querySelectorAll('[data-project-type]').forEach(radio => {
    radio.addEventListener('change', () => {
      const type = document.querySelector('[data-project-type]:checked')?.value;
      document.querySelectorAll('[data-general-fields]').forEach(x => x.classList.toggle('d-none', type !== 'general'));
      document.querySelectorAll('[data-creative-fields]').forEach(x => x.classList.toggle('d-none', type !== 'creative'));
    });
  });

  document.querySelectorAll('[data-current-checkbox]').forEach(cb => {
    const target = document.querySelector(cb.dataset.currentCheckbox);
    const sync = () => {
      if (target) {
        target.disabled = cb.checked;
        if (cb.checked) target.value = '';
      }
    };
    cb.addEventListener('change', sync);
    sync();
  });

  document.querySelectorAll('input[type=file][data-preview]').forEach(input => {
    input.addEventListener('change', () => {
      const img = document.querySelector(input.dataset.preview);
      if (img && input.files[0]) img.src = URL.createObjectURL(input.files[0]);
    });
  });

  const sidebar = document.getElementById('sidebar');
  document.querySelectorAll('[data-toggle="offcanvas"]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      sidebar?.classList.toggle('active');
    });
  });

  document.addEventListener('click', e => {
    if (!sidebar?.classList.contains('active')) return;
    if (e.target.closest('#sidebar') || e.target.closest('[data-toggle="offcanvas"]')) return;
    sidebar.classList.remove('active');
  });
});
