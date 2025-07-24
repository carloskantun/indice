(function(){
  function loadContent(modal){
    const url = modal.getAttribute('data-modal-url');
    if(!url) return;
    const selector = modal.getAttribute('data-modal-content') || '.modal-content';
    const cont = selector.startsWith('#') ? document.querySelector(selector) : modal.querySelector(selector);
    if(!cont) return;
    if(window.jQuery){
      $(cont).load(url);
    } else {
      cont.innerHTML = 'Cargando...';
      fetch(url).then(r => r.text()).then(html => cont.innerHTML = html)
        .catch(() => cont.innerHTML = "<div class='p-3 text-danger'>Error al cargar contenido.</div>");
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[data-modal-url]').forEach(modal => {
      modal.addEventListener('show.bs.modal', function(){ loadContent(modal); });
      modal.addEventListener('hidden.bs.modal', function(){
        const selector = modal.getAttribute('data-modal-content') || '.modal-content';
        const cont = selector.startsWith('#') ? document.querySelector(selector) : modal.querySelector(selector);
        if(cont) cont.innerHTML = 'Cargando...';
      });
    });
  });
})();
