(function(){
  function attachForm(modal, container){
    const form = container.querySelector('form');
    if(!form || form.dataset.ajaxHandled) return;
    form.dataset.ajaxHandled = '1';
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const data = new FormData(form);
      const action = form.getAttribute('action') || modal.getAttribute('data-form-action');
      const url = action + (action.includes('?') ? '&' : '?') + 'ajax=1';
      fetch(url, {method: form.method || 'POST', body: data})
        .then(r => r.text())
        .then(t => {
          if(t.trim() === 'ok'){
            alert('✅ Operación realizada correctamente');
            bootstrap.Modal.getInstance(modal)?.hide();
            if(window.refreshModule) window.refreshModule();
          }else{
            alert('❌ Error: ' + t);
          }
        })
        .catch(() => alert('❌ Error de conexión'));
    });
  }

  function loadContent(modal){
    const url = modal.getAttribute('data-modal-url');
    if(!url) return;
    const selector = modal.getAttribute('data-modal-content') || '.modal-content';
    const cont = selector.startsWith('#') ? document.querySelector(selector) : modal.querySelector(selector);
    if(!cont) return;
    const done = html => { cont.innerHTML = html; attachForm(modal, cont); };
    if(window.jQuery){
      $(cont).load(url, done);
    } else {
      cont.innerHTML = 'Cargando...';
      fetch(url).then(r => r.text()).then(done)
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

    // Nuevo gasto
    document.querySelectorAll('[data-bs-target="#modalGasto"]').forEach(btn => {
      btn.addEventListener('click', () => {
        const modal = document.getElementById('modalGasto');
        if(modal) bootstrap.Modal.getOrCreateInstance(modal).show();
      });
    });

    // Editar gasto
    document.body.addEventListener('click', e => {
      const btn = e.target.closest('.edit-btn');
      if(!btn) return;
      const id = btn.dataset.id;
      const modal = document.getElementById('modalEditarGasto');
      if(modal){
        modal.setAttribute('data-modal-url', 'app/modules/gastos/modal_editar_gasto.php?id='+id+'&ajax=1');
        bootstrap.Modal.getOrCreateInstance(modal).show();
      }
    });

    // Nueva Orden de Compra
    document.querySelectorAll('[data-bs-target="#modalOrden"]').forEach(btn => {
      btn.addEventListener('click', () => {
        const modal = document.getElementById('modalOrden');
        if(modal) bootstrap.Modal.getOrCreateInstance(modal).show();
      });
    });
  });

  window.refreshModule = window.refreshModule || function(){};
})();
