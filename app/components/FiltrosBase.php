<?php
class FiltrosBase {
    public function apply(array $params): array {
        return $params; // Adjust filters in subclasses
    }
}
