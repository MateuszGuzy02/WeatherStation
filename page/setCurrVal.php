<?php
include_once 'Charts.php';

$chart = new Charts;

$chart->setObecnaTemperatura();
$chart->setObecnaWilgotnosc();

$data = array();

$data[] = $chart->getObecnaTemperatura();
$data[] = $chart->getObecnaWilgotnosc();

$semdData = json_encode($data);

echo $semdData;