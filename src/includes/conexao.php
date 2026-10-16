<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "ipsa_estoque";

$conn = new mysqli($host, $usuario, $senha, $banco);
$conn->set_charset("utf8mb4");

?>