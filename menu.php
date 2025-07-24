<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: login_form.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Menú</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <h3 class="mb-3">Módulos</h3>
    <div class="list-group mb-4" id="moduleList">
        <a href="#" class="list-group-item list-group-item-action module-link" data-module="gastos">Gastos</a>
    </div>

    <div class="modal fade" id="mainModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content"></div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadModule(name){
    const modal=document.getElementById('mainModal');
    fetch('index.php?module='+name+'&ajax=1')
        .then(r=>r.text())
        .then(html=>{
            modal.dataset.module=name;
            modal.querySelector('.modal-content').innerHTML=html;
            new bootstrap.Modal(modal).show();
        });
}
function refreshModule(){
    const modal=document.getElementById('mainModal');
    if(modal.dataset.module){
        loadModule(modal.dataset.module);
    }
}

document.querySelectorAll('.module-link').forEach(el=>{
    el.addEventListener('click',e=>{e.preventDefault();loadModule(el.dataset.module);});
});
window.refreshModule=refreshModule;
</script>
</body>
</html>
