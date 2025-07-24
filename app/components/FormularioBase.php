<?php
class FormularioBase {
    /**
     * Renderiza campos de formulario Bootstrap 5.
     *
     * Cada elemento del array debe incluir al menos:
     *   - type: tipo de campo (text, number, select, date, textarea, etc.)
     *   - name: nombre del campo
     *   - label: etiqueta a mostrar
     * Opcionalmente puede contener:
     *   - options: array de opciones [valor => texto] (para selects)
     *   - default: valor por defecto
     *   - required: bool para marcar como requerido
     *   - validation: mensaje de validación a mostrar
     */
    public static function render(array $config): string {
        $html = '';

        foreach ($config as $field) {
            $type       = $field['type'] ?? 'text';
            $name       = $field['name'] ?? '';
            $label      = $field['label'] ?? '';
            $options    = $field['options'] ?? [];
            $value      = $field['default'] ?? '';
            $required   = !empty($field['required']) ? 'required' : '';
            $validation = $field['validation'] ?? '';
            $id         = $field['id'] ?? $name;

            $html .= "<div class=\"mb-3\">";
            if ($label) {
                $html .= "<label for=\"{$id}\" class=\"form-label\">{$label}</label>";
            }

            switch ($type) {
                case 'select':
                    $html .= "<select name=\"{$name}\" id=\"{$id}\" class=\"form-select\" {$required}>";
                    $html .= '<option value="">Seleccionar</option>';
                    foreach ($options as $optValue => $optLabel) {
                        $selected = ((string)$optValue === (string)$value) ? 'selected' : '';
                        $optValueEsc = htmlspecialchars($optValue, ENT_QUOTES, 'UTF-8');
                        $optLabelEsc = htmlspecialchars($optLabel, ENT_QUOTES, 'UTF-8');
                        $html .= "<option value=\"{$optValueEsc}\" {$selected}>{$optLabelEsc}</option>";
                    }
                    $html .= '</select>';
                    break;

                case 'textarea':
                    $valEsc = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    $html .= "<textarea name=\"{$name}\" id=\"{$id}\" class=\"form-control\" {$required}>{$valEsc}</textarea>";
                    break;

                default:
                    $valEsc = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    $html .= "<input type=\"{$type}\" name=\"{$name}\" id=\"{$id}\" class=\"form-control\" value=\"{$valEsc}\" {$required}>";
                    break;
            }

            if ($validation) {
                $html .= "<div class=\"form-text text-danger\">{$validation}</div>";
            }

            $html .= '</div>';
        }

        return $html;
    }
}
