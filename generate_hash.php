<?php
$password = 'Corazon.01*';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "El hash para la contrase«Ða 'Corazon.01*' es: $hash";
?>
