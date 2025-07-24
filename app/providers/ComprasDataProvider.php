<?php
class ComprasDataProvider implements DataProviderInterface {
    private mysqli $db;
    public function __construct(mysqli $db) { $this->db = $db; }

    public function getKpis(): array {
        $tot   = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_compra")->fetch_assoc()['t'] ?? 0;
        $pag   = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_compra WHERE estatus_pago='Pagado'")->fetch_assoc()['t'] ?? 0;
        $pend  = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_compra WHERE estatus_pago='Por pagar'")->fetch_assoc()['t'] ?? 0;
        $venc  = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_compra WHERE estatus_pago='Vencido'")->fetch_assoc()['t'] ?? 0;
        return ['Total'=>$tot,'Pagadas'=>$pag,'Por pagar'=>$pend,'Vencidas'=>$venc];
    }

    public function getTableData(TableOptions $opt): array {
        $allowed = ['folio','monto','vencimiento_pago','concepto_pago','tipo_pago','estatus_pago'];
        $order   = in_array($opt->getOrderBy(), $allowed) ? $opt->getOrderBy() : 'folio';
        $dir     = $opt->getDirection();
        $limit   = $opt->getPerPage();
        $offset  = ($opt->getPage()-1)*$limit;
        $sql = "SELECT folio,monto,vencimiento_pago,concepto_pago,tipo_pago,estatus_pago FROM ordenes_compra ORDER BY $order $dir LIMIT $limit OFFSET $offset";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTotalCount(): int {
        $res = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_compra");
        return $res->fetch_assoc()['t'] ?? 0;
    }
}
?>
