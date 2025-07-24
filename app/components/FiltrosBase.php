<?php
class FiltrosBase {
    public static function render(array $filtros): string {
        $html = '';

        foreach ($filtros as $f) {
            $tipo        = $f['type'] ?? 'text';
            $nombre      = $f['name'] ?? '';
            $label       = $f['label'] ?? '';
            $options     = $f['options'] ?? [];
            $valor       = $f['value'] ?? '';
            $placeholder = $f['placeholder'] ?? '';
            $clase       = $f['class'] ?? ($tipo === 'select' ? 'form-select' : 'form-control');
            $col         = $f['col'] ?? 'col-md';
            $id          = $f['id'] ?? $nombre;

            $html .= "<div class=\"{$col}\">";
            if ($label) {
                $html .= "<label for=\"{$id}\" class=\"form-label\">{$label}</label>";
            }

            switch ($tipo) {
                case 'select':
                    $html .= "<select name=\"{$nombre}\" id=\"{$id}\" class=\"{$clase}\" data-placeholder=\"{$placeholder}\">";
                    if ($placeholder !== '') {
                        $html .= "<option value=\"\">{$placeholder}</option>";
                    }
                    foreach ($options as $val => $text) {
                        $sel  = ((string)$val === (string)$valor) ? 'selected' : '';
                        $val  = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
                        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
                        $html .= "<option value=\"{$val}\" {$sel}>{$text}</option>";
                    }
                    $html .= '</select>';
                    break;

                default:
                    $valEsc = htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
                    $html  .= "<input type=\"{$tipo}\" name=\"{$nombre}\" id=\"{$id}\" class=\"{$clase}\" value=\"{$valEsc}\" placeholder=\"{$placeholder}\">";
                    break;
            }

            $html .= '</div>';
        }

        return $html;
    }

    public function apply(array $params): array {
        return $params; // Adjust filters in subclasses
    }
}
