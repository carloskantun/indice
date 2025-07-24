<?php
class PanelResumen {
    private string $title;
    private array $kpis;
    private array $data;
    private TableOptions $options;

    public function __construct(string $title, array $kpis, array $data, TableOptions $options) {
        $this->title   = $title;
        $this->kpis    = $kpis;
        $this->data    = $data;
        $this->options = $options;
    }

    public function render(): void {
        echo '<h2>'.htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8')."</h2>\n";
        echo '<div class="row mb-3">';
        foreach ($this->kpis as $label => $value) {
            echo '<div class="col"><strong>'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').':</strong> '.htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8').'</div>';
        }
        echo '</div>';

        echo '<div class="table-responsive"><table class="table table-striped">';
        if (!empty($this->data)) {
            echo '<thead><tr>'; 
            foreach (array_keys($this->data[0]) as $col) {
                echo '<th>'.htmlspecialchars($col, ENT_QUOTES, 'UTF-8').'</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ($this->data as $row) {
                echo '<tr>'; 
                foreach ($row as $cell) {
                    echo '<td>'.htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8').'</td>';
                }
                echo '</tr>';
            }
            echo '</tbody>';
        } else {
            echo '<tr><td>No data</td></tr>';
        }
        echo '</table></div>';

        $totalPages = $this->options->getTotal() > 0 ? (int)ceil($this->options->getTotal() / $this->options->getPerPage()) : 1;
        if ($totalPages > 1) {
            echo '<nav><ul class="pagination">';
            $page = $this->options->getPage();
            $prev = $page - 1;
            $next = $page + 1;
            if ($page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?pagina='.$prev.'">&laquo;</a></li>';
            }
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = $i === $page ? ' active' : '';
                echo '<li class="page-item'.$active.'"><a class="page-link" href="?pagina='.$i.'">'.$i.'</a></li>';
            }
            if ($page < $totalPages) {
                echo '<li class="page-item"><a class="page-link" href="?pagina='.$next.'">&raquo;</a></li>';
            }
            echo '</ul></nav>';
        }
    }
}
?>
