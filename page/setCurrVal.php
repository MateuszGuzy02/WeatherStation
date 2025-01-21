<?php
include_once 'Charts.php';

$servername = "localhost";
$dbname = "projektArm";
$username = "root";
$password = "root";


$charts = new Charts($servername, $username, $password, $dbname);

if (!$charts) {
    http_response_code(500);
    echo json_encode(["error" => "Błąd połączenia z bazą danych."]);
    exit;
}

$charts->setObecneDane();

$data = array(
    'temperaturaZew' => $charts->getObecnaTemperaturaZewnetrzna(),
    'temperaturaWew' => $charts->getObecnaTemperaturaWewnetrzna(),
    'wilgotnoscZew' => $charts->getObecnaWilgotnoscZewnetrzna(),
    'wilgotnoscWew' => $charts->getObecnaWilgotnoscWewnetrzna(),
    'cisnienie' => $charts->getObecneCisnienieZewnetrzne()
);

header('Content-Type: application/json');

echo json_encode($data);
