<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "invicta_financas";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>