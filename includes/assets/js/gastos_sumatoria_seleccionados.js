document.addEventListener('DOMContentLoaded', function () {
  const resumen = document.getElementById('resumen-seleccionados');
  const selMonto = document.getElementById('sel-monto');
  const selAbono = document.getElementById('sel-abono');
  const selSaldo = document.getElementById('sel-saldo');

  function actualizarTotalesSeleccionados() {
    let totalMonto = 0, totalAbono = 0, totalSaldo = 0;
    let algunoSeleccionado = false;

    document.querySelectorAll('.seleccionar-gasto:checked').forEach(cb => {
      const fila = cb.closest('tr');
      totalMonto += parseFloat(fila.querySelector('.col-monto')?.textContent.replace(/[$,]/g, '') || 0);
      totalAbono += parseFloat(fila.querySelector('.col-abonado')?.textContent.replace(/[$,]/g, '') || 0);
      totalSaldo += parseFloat(fila.querySelector('.col-saldo')?.textContent.replace(/[$,]/g, '') || 0);
      algunoSeleccionado = true;
    });

if (algunoSeleccionado) {
  resumen.classList.remove('fade-out-bottom', 'd-none');
  resumen.classList.add('fade-in-bottom');
} else if (!resumen.classList.contains('d-none')) {
  resumen.classList.remove('fade-in-bottom');
  resumen.classList.add('fade-out-bottom');
  // Espera que termine la animación antes de ocultarlo
  setTimeout(() => resumen.classList.add('d-none'), 300);
}


    selMonto.textContent = totalMonto.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
    selAbono.textContent = totalAbono.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
    selSaldo.textContent = totalSaldo.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
  }

  document.querySelectorAll('.seleccionar-gasto').forEach(cb => {
    cb.addEventListener('change', actualizarTotalesSeleccionados);
  });

  document.getElementById('seleccionar-todos')?.addEventListener('change', actualizarTotalesSeleccionados);
})

document.getElementById('btn-exportar-csv')?.addEventListener('click', function () {
    const ids = Array.from(document.querySelectorAll('.seleccionar-gasto'))
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    if (!ids.length) {
        alert("❌ No hay elementos seleccionados.");
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'includes/controllers/exportar_gastos_seleccionados.php';
    form.style.display = 'none';

    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
});

document.getElementById('btn-exportar-pdf')?.addEventListener('click', function () {
    const ids = Array.from(document.querySelectorAll('.seleccionar-gasto'))
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    if (!ids.length) {
        alert("❌ No hay elementos seleccionados.");
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'includes/controllers/exportar_gastos_seleccionados_pdf.php';
    form.style.display = 'none';

    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
});
