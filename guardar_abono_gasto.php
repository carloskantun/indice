<?php
session_start();
include 'auth.php';
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
    $ext = null;
    if(isset($_FILES['comprobante']) && is_uploaded_file($_FILES['comprobante']['tmp_name'])){
        $ext = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','pdf'];
        if(!in_array($ext,$permitidos)) throw new Exception('Tipo de archivo no permitido');
        if(!is_dir('uploads/comprobantes')) mkdir('uploads/comprobantes',0777,true);
        $nombre = uniqid('comp_').'.'.$ext;
        $destino = 'uploads/comprobantes/'.$nombre;
        if($ext==='png'){
            $png = imagecreatefrompng($_FILES['comprobante']['tmp_name']);
            $bg = imagecreatetruecolor(imagesx($png), imagesy($png));
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefilledrectangle($bg, 0, 0, imagesx($png), imagesy($png), $white);
            imagecopy($bg, $png, 0, 0, 0, 0, imagesx($png), imagesy($png));
            imagejpeg($bg, $destino, 60);
            imagedestroy($png);
            imagedestroy($bg);
        }elseif($ext==='jpg' || $ext==='jpeg'){
            $img = imagecreatefromjpeg($_FILES['comprobante']['tmp_name']);
            imagejpeg($img,$destino,60);
            imagedestroy($img);
        }else{ // pdf
            if(!move_uploaded_file($_FILES['comprobante']['tmp_name'],$destino)) throw new Exception('Error subiendo archivo');
        }
        $archivo = $destino;
    }


    $stmt = $conn->prepare("INSERT INTO abonos_gastos (gasto_id,monto,fecha,comentario,archivo_comprobante) VALUES (?,?,?,?,?)");
    $stmt->bind_param('idsss',$gasto_id,$monto,$fecha,$comentario,$archivo);
    if(!$stmt->execute()) throw new Exception($stmt->error);

    $total = $conn->query("SELECT SUM(monto) AS s FROM abonos_gastos WHERE gasto_id=$gasto_id")->fetch_assoc()['s'];
    $gasto = $conn->query("SELECT monto, fecha_pago, origen FROM gastos WHERE id=$gasto_id")->fetch_assoc();
    $hoy = date('Y-m-d');
    if ($total >= $gasto['monto']) {
        $nuevo_status = 'Pagado';
    } elseif ($total > 0 && $gasto['fecha_pago'] < $hoy) {
        $nuevo_status = 'Vencido';
    } elseif ($total > 0) {
        $nuevo_status = 'Pago parcial';
    } elseif ($gasto['origen'] === 'Orden' && $gasto['fecha_pago'] < $hoy) {
        $nuevo_status = 'Vencido';
    } else {
        $nuevo_status = 'Por pagar';
    }

    $conn->query("UPDATE gastos SET estatus='".$conn->real_escape_string($nuevo_status)."' WHERE id=$gasto_id");

    $conn->commit();
    echo 'ok';
}catch(Exception $e){
    $conn->rollback();
    echo 'Error: '.$e->getMessage();
}
?>