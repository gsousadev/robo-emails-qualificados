<?php
require 'vendor/autoload.php';
require 'src/getPlacesByTextSearch.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$categorias = [
    "restaurante",
    "loja de varejo",
    "concessionária de automóveis",
    "academia",
    "salão de beleza",
    "spa",
    "supermercado",
    "clínica médica",
    "loja de móveis",
    "mercearia",
    "cafeteria",
    "petshop",
    "escritorio de advocacia"
];

$regioes = [
    "São Paulo" => [
        "zona sul de São Paulo",
        "zona norte de São Paulo",
        "zona oeste de São Paulo",
        "zona leste de São Paulo",
        "centro de São Paulo"
    ],
    "Rio de Janeiro" => [
        "zona sul do Rio de Janeiro",
        "zona norte do Rio de Janeiro",
        "zona oeste do Rio de Janeiro",
        "zona leste do Rio de Janeiro",
        "centro do Rio de Janeiro"
    ],
    "Brasília" => [
        "região sul de Brasília",
        "região norte de Brasília",
        "região oeste de Brasília",
        "região leste de Brasília",
        "região central de Brasília"
    ],
    "Salvador" => [
        "região sul de Salvador",
        "região norte de Salvador",
        "região oeste de Salvador",
        "região leste de Salvador",
        "centro de Salvador"
    ],
    "Fortaleza" => [
        "região sul de Fortaleza",
        "região norte de Fortaleza",
        "região oeste de Fortaleza",
        "região leste de Fortaleza",
        "centro de Fortaleza"
    ]
];

$regioesSimples = [];

foreach ($regioes as $capital) {
    $regioesSimples = array_merge($regioesSimples, $capital);
}
$count = 1;

foreach($categorias as $categoria){
   foreach($regioesSimples as $regiao){
        echo "- Iniciando Busca número: $count" . PHP_EOL; 
        echo "-- Termo de pesquisas: $categoria $regiao" . PHP_EOL; 
        initSearch($regiao, $categoria);
        $count++;
        echo PHP_EOL;
    }
}