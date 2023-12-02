<?php
require 'vendor/autoload.php';

try {
    
    $types = array( 'json');
    $path = './output_files';
    $dir = new DirectoryIterator($path);
    foreach ($dir as $fileInfo) {

        dd($fileInfo);
    }

} catch (\Throwable $e) {
    echo "Ocorreu um erro: " . $e->getMessage() . PHP_EOL;
}

