// Generic filter handler
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-endpoint]').forEach(form => {
    const endpoint = form.dataset.endpoint;
    const target = document.querySelector(form.dataset.target || '#resultado');
    const render = form.dataset.render || 'json';

    async function cargar() {
      const datos = new URLSearchParams(new FormData(form));
      const url = endpoint + '?' + datos.toString();
      const res = await fetch(url);
      if (render === 'html') {
        const html = await res.text();
        if (target) target.innerHTML = html;
      } else {
        const json = await res.json();
        form.dispatchEvent(new CustomEvent('filtros:data', { detail: json }));
      }
    }

    form.addEventListener('submit', e => { e.preventDefault(); cargar(); });
    form.addEventListener('change', e => { if(e.target.name) cargar(); });

    // Carga inicial
    cargar();
  });
});
