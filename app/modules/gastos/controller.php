<?php
class GastosController {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function getKpis(): array {
        $mes  = $this->db->query("SELECT SUM(monto) AS t FROM gastos WHERE MONTH(fecha_pago)=MONTH(CURDATE()) AND YEAR(fecha_pago)=YEAR(CURDATE())")->fetch_assoc()['t'] ?? 0;
        $anio = $this->db->query("SELECT SUM(monto) AS t FROM gastos WHERE YEAR(fecha_pago)=YEAR(CURDATE())")->fetch_assoc()['t'] ?? 0;
        return ['mes' => (float)$mes, 'anio' => (float)$anio];
    }

    public function listar(array $filtros = [], string $orden = 'fecha_pago', string $dir = 'DESC'): array {
        $cond = [];
        if (!empty($filtros['proveedor'])) $cond[] = 'g.proveedor_id='.(int)$filtros['proveedor'];
        if (!empty($filtros['unidad'])) $cond[] = 'g.unidad_negocio_id='.(int)$filtros['unidad'];
        if (!empty($filtros['fecha_inicio'])) $cond[] = "g.fecha_pago >= '".$this->db->real_escape_string($filtros['fecha_inicio'])."'";
        if (!empty($filtros['fecha_fin'])) $cond[] = "g.fecha_pago <= '".$this->db->real_escape_string($filtros['fecha_fin'])."'";
        if (!empty($filtros['estatus'])) $cond[] = "g.estatus='".$this->db->real_escape_string($filtros['estatus'])."'";
        if (!empty($filtros['origen'])) $cond[] = "g.origen='".$this->db->real_escape_string($filtros['origen'])."'";
        $where = $cond ? 'WHERE '.implode(' AND ',$cond) : '';
        $map = ['folio'=>'g.folio','proveedor'=>'p.nombre','monto'=>'g.monto','fecha_pago'=>'g.fecha_pago'];
        $col  = $map[$orden] ?? 'g.fecha_pago';
        $dir  = strtoupper($dir)==='ASC'?'ASC':'DESC';
        $sql = "SELECT
                g.id,
                g.folio,
                p.nombre AS proveedor,
                g.monto,
                g.fecha_pago,
                un.nombre AS unidad,
                g.tipo_gasto,
                g.tipo_compra,
                g.medio_pago,
                g.cuenta_bancaria,
                g.concepto,
                g.estatus,
                g.origen,
                (SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id) AS abonado_total,
                (g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id),0)) AS saldo,
                (SELECT GROUP_CONCAT(a.archivo_comprobante SEPARATOR ';') FROM abonos_gastos a WHERE a.gasto_id = g.id AND a.archivo_comprobante IS NOT NULL) AS archivo_comprobante
            FROM gastos g
            LEFT JOIN proveedores p ON g.proveedor_id=p.id
            LEFT JOIN unidades_negocio un ON g.unidad_negocio_id=un.id
            $where
            ORDER BY $col $dir";
        $res = $this->db->query($sql);
        return $res? $res->fetch_all(MYSQLI_ASSOC):[];
    }

    public function guardar(array $data, array $files = []): string {
        $proveedor_id = $data['proveedor_id'] ?? null;
        $monto        = $data['monto'] ?? null;
        $fecha_pago   = $data['fecha_pago'] ?? null;
        $unidad_id    = $data['unidad_negocio_id'] ?? null;
        $tipo_gasto   = $data['tipo_gasto'] ?? 'Unico';
        $tipo_compra  = $data['tipo_compra'] ?? null;
        $medio_pago   = $data['medio_pago'] ?? 'Transferencia';
        $cuenta       = $data['cuenta_bancaria'] ?? null;
        $concepto     = $data['concepto'] ?? null;
        $origen       = $data['origen'] ?? 'Directo';
        $orden_folio  = $data['orden_folio'] ?? null;

        if (!$proveedor_id || !$monto || !$fecha_pago || !$unidad_id) {
            return 'Faltan datos obligatorios';
        }

        $nuevo_id = $this->db->query("SELECT IFNULL(MAX(id),0)+1 AS nuevo_id FROM gastos")->fetch_assoc()['nuevo_id'] ?? 1;
        $prefijo  = ($origen === 'Orden') ? 'OC-' : 'G-';
        $folio    = $prefijo . str_pad($nuevo_id, 3, '0', STR_PAD_LEFT);
        $hoy      = date('Y-m-d');
        $estatus  = ($origen === 'Orden') ? (($fecha_pago < $hoy) ? 'Vencido' : 'Por pagar') : 'Pagado';

        $archivo_comprobante = null;
        if (isset($files['comprobante']['tmp_name'][0]) && is_uploaded_file($files['comprobante']['tmp_name'][0])) {
            $ext = strtolower(pathinfo($files['comprobante']['name'][0], PATHINFO_EXTENSION));
            if (!is_dir(COMPROBANTES_DIR)) mkdir(COMPROBANTES_DIR, 0777, true);
            $nombre = uniqid('comp_').'.'.$ext;
            $destino = COMPROBANTES_DIR.'/'.$nombre;
            if (move_uploaded_file($files['comprobante']['tmp_name'][0], $destino)) {
                $archivo_comprobante = $destino;
            }
        }

        $sql = "INSERT INTO gastos (folio,proveedor_id,monto,fecha_pago,unidad_negocio_id,tipo_gasto,tipo_compra,medio_pago,cuenta_bancaria,estatus,concepto,orden_folio,origen,archivo_comprobante) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $this->db->prepare($sql);
        if(!$stmt) return 'Error: '.$this->db->error;
        $stmt->bind_param('sidsisssssssss', $folio, $proveedor_id, $monto, $fecha_pago, $unidad_id, $tipo_gasto, $tipo_compra, $medio_pago, $cuenta, $estatus, $concepto, $orden_folio, $origen, $archivo_comprobante);
        if($stmt->execute()) return 'ok';
        return 'Error: '.$stmt->error;
    }

    public function actualizar(array $data): string {
        $id = (int)($data['id'] ?? 0);
        if(!$id) return 'ID inválido';
        $campos = ['proveedor_id','monto','fecha_pago','unidad_negocio_id','tipo_gasto','medio_pago','cuenta_bancaria','concepto','tipo_compra','estatus'];
        $vals = [];
        $sets = [];
        $types = '';
        foreach($campos as $c){
            if(!isset($data[$c])) continue;
            $vals[] = $data[$c];
            $sets[] = "$c=?";
            $types .= is_numeric($data[$c])? 'i':'s';
        }
        if(!$sets) return 'Sin datos';
        $vals[] = $id; $types.='i';
        $sql = "UPDATE gastos SET ".implode(',', $sets)." WHERE id=?";
        $stmt = $this->db->prepare($sql);
        if(!$stmt) return 'Error: '.$this->db->error;
        $stmt->bind_param($types, ...$vals);
        if($stmt->execute()) return 'ok';
        return 'Error: '.$stmt->error;
    }
}
