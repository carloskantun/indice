<?php
interface DataProviderInterface {
    public function getKpis(): array;
    public function getTableData(TableOptions $options): array;
    public function getTotalCount(): int;
}
?>
