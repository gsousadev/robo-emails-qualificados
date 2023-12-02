<?php 

function clearFileName(string $fileName): string
{
    $fileName = preg_replace('/[^\w\d]+/', '_', $fileName);
    return preg_replace('/_+/', '_', $fileName);
}


function dd(...$args)
{
    var_dump($args);
    die();
}