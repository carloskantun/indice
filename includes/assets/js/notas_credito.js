document.addEventListener('DOMContentLoaded', function () {
  // Activar select2
  $('.select2').select2({ width: '100%' });

  // Abrir modal de nueva nota
  const btnNueva = document.getElementById('btnNuevaNota');
  if (btnNueva) {
    btnNueva.addEventListener('click', function () {
      const modal = new bootstrap.Modal(document.getElementById('modalNota'));
      $('#contenidoNota').load('includes/modals/modal_nota_credito.php', function () {
        modal.show();
      });
    });
  }

  // Editar nota (misma modal, con ?id=)
  document.querySelectorAll('.editar-nota-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const modal = new bootstrap.Modal(document.getElementById('modalNota'));
      $('#contenidoNota').load(`includes/modals/modal_nota_credito.php?id=${id}`, function () {
        modal.show();
      });
    });
  });

  // Ver abonos
  document.querySelectorAll('.ver-abonos-nota').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const modal = new bootstrap.Modal(document.getElementById('modalVerAbonos'));
      $('#contenidoVerAbonos').load(`includes/modals/modal_ver_abonos_nota.php?id=${id}`, function () {
        modal.show();
      });
    });
  });
});
  // Modal para abonar
  document.querySelectorAll('.abonar-nota-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const modal = new bootstrap.Modal(document.getElementById('modalAbonoNota'));
      $('#contenidoAbonoNota').load(`includes/modals/modal_abono_nota.php?id=${id}`, function () {
        modal.show();
      });
    });
  });
