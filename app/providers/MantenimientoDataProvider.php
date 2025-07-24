<?php
class MantenimientoDataProvider implements DataProviderInterface {
    private mysqli $db;
    public function __construct(mysqli $db) { $this->db = $db; }

    public function getKpis(): array {
        $tot  = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_mantenimiento")->fetch_assoc()['t'] ?? 0;
        $pag  = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_mantenimiento WHERE estatus='Pagado'")->fetch_assoc()['t'] ?? 0;
        $pend = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_mantenimiento WHERE estatus='Por pagar'")->fetch_assoc()['t'] ?? 0;
        $venc = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_mantenimiento WHERE estatus='Vencido'")->fetch_assoc()['t'] ?? 0;
        return ['Total'=>$tot,'Pagadas'=>$pag,'Por pagar'=>$pend,'Vencidas'=>$venc];
    }

    public function getTableData(TableOptions $opt): array {
        $allowed = ['folio','fecha_reporte','estatus'];
        $order   = in_array($opt->getOrderBy(), $allowed) ? $opt->getOrderBy() : 'folio';
        $dir     = $opt->getDirection();
        $limit   = $opt->getPerPage();
        $offset  = ($opt->getPage()-1)*$limit;
        $sql = "SELECT folio,fecha_reporte,estatus FROM ordenes_mantenimiento ORDER BY $order $dir LIMIT $limit OFFSET $offset";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTotalCount(): int {
        $res = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_mantenimiento");
        return $res->fetch_assoc()['t'] ?? 0;
    }
}
?>
