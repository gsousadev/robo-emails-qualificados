<?php
require 'vendor/autoload.php';
require 'src/getPlacesByTextSearch.php';

try {
    $options = getCommandOptions();

    $textSearch = $options['q'];
    $category = $options['c'];

    initSearch($textSearch, $category);

} catch (\Throwable $e) {
    echo "Ocorreu um erro: " . $e->getMessage() . PHP_EOL;
}

