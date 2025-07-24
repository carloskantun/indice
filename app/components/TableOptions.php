<?php
class TableOptions {
    private int $page;
    private int $perPage;
    private string $orderBy;
    private string $direction;
    private int $total;

    public function __construct(array $opts = []) {
        $this->page      = max(1, (int)($opts['page'] ?? 1));
        $this->perPage   = max(1, (int)($opts['per_page'] ?? 20));
        $this->orderBy   = $opts['order_by'] ?? 'id';
        $dir             = strtoupper($opts['direction'] ?? 'ASC');
        $this->direction = $dir === 'DESC' ? 'DESC' : 'ASC';
        $this->total     = (int)($opts['total'] ?? 0);
    }

    public function getPage(): int { return $this->page; }
    public function getPerPage(): int { return $this->perPage; }
    public function getOrderBy(): string { return $this->orderBy; }
    public function getDirection(): string { return $this->direction; }
    public function getTotal(): int { return $this->total; }
    public function setTotal(int $total): void { $this->total = $total; }
}
?>
