<?php
class LavanderiaDataProvider implements DataProviderInterface {
    private mysqli $db;
    public function __construct(mysqli $db) { $this->db = $db; }

    public function getKpis(): array {
        $tot  = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_lavanderia")->fetch_assoc()['t'] ?? 0;
        $pend = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_lavanderia WHERE estatus='Pendiente'")->fetch_assoc()['t'] ?? 0;
        $proc = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_lavanderia WHERE estatus='En proceso'")->fetch_assoc()['t'] ?? 0;
        $term = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_lavanderia WHERE estatus='Terminado'")->fetch_assoc()['t'] ?? 0;
        return ['Total'=>$tot,'Pendiente'=>$pend,'En proceso'=>$proc,'Terminado'=>$term];
    }

    public function getTableData(TableOptions $opt): array {
        $allowed = ['folio','fecha','cliente','servicio','estatus'];
        $order   = in_array($opt->getOrderBy(), $allowed) ? $opt->getOrderBy() : 'folio';
        $dir     = $opt->getDirection();
        $limit   = $opt->getPerPage();
        $offset  = ($opt->getPage()-1)*$limit;
        $sql = "SELECT folio,fecha,cliente,servicio,estatus FROM ordenes_lavanderia ORDER BY $order $dir LIMIT $limit OFFSET $offset";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTotalCount(): int {
        $res = $this->db->query("SELECT COUNT(*) AS t FROM ordenes_lavanderia");
        return $res->fetch_assoc()['t'] ?? 0;
    }
}
?>
