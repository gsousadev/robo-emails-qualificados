<?php
require 'vendor/autoload.php';
require 'src/getPlacesByTextSearch.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$categorias = json_decode(file_get_contents('./input_files/categories.json'), true);

$regioes = json_decode(file_get_contents('./input_files/zones.json'), true);

$count = 1;

foreach ($categorias as $categoria) {
    foreach ($regioes as $regiao) {
        echo "- Iniciando Busca número: $count" . PHP_EOL;
        echo "-- Termo de pesquisas: $categoria $regiao" . PHP_EOL;
        initSearch($regiao, $categoria);
        $count++;
        echo PHP_EOL;
    }
}
