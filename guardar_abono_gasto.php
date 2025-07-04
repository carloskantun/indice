<?php
include 'conexion.php';

$gasto_id = intval($_POST['gasto_id'] ?? 0);
$monto = floatval($_POST['monto'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$comentario = $_POST['comentario'] ?? '';

if(!$gasto_id || !$monto || !$fecha){
    echo 'Datos incompletos';
    exit;
}

$conn->begin_transaction();
try{
    $archivo = null;
    if(!empty($_FILES['comprobante']['name'])){
        $ext = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','pdf'];
        if(!in_array($ext,$permitidos)) throw new Exception('Tipo de archivo no permitido');
        if(!is_dir('uploads/comprobantes')) mkdir('uploads/comprobantes',0777,true);
        $nombre = uniqid('comp_').'.'.$ext;
        $destino = 'uploads/comprobantes/'.$nombre;
        if($ext==='jpg' || $ext==='jpeg' || $ext==='png'){
            $img = ($ext==='png') ? imagecreatefrompng($_FILES['comprobante']['tmp_name']) : imagecreatefromjpeg($_FILES['comprobante']['tmp_name']);
            imagejpeg($img,$destino,60);
            imagedestroy($img);
        }else{
            if(!move_uploaded_file($_FILES['comprobante']['tmp_name'],$destino)) throw new Exception('Error subiendo archivo');
        }
        $archivo = $destino;
    }

    $stmt = $conn->prepare("INSERT INTO abonos_gastos (gasto_id,monto,fecha,comentario,archivo_comprobante) VALUES (?,?,?,?,?)");
    $stmt->bind_param('idsss',$gasto_id,$monto,$fecha,$comentario,$archivo);
    if(!$stmt->execute()) throw new Exception($stmt->error);

    $total = $conn->query("SELECT SUM(monto) AS s FROM abonos_gastos WHERE gasto_id=$gasto_id")->fetch_assoc()['s'];
    $gasto = $conn->query("SELECT monto FROM gastos WHERE id=$gasto_id")->fetch_assoc();
    $nuevo_status = ($total >= $gasto['monto']) ? 'Pagado' : 'Abonado';
    $conn->query("UPDATE gastos SET estatus='".$conn->real_escape_string($nuevo_status)."' WHERE id=$gasto_id");

    $conn->commit();
    echo 'ok';
}catch(Exception $e){
    $conn->rollback();
    echo 'Error: '.$e->getMessage();
}
?>
