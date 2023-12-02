<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

const EXCLUDE_SITES = [
    'facebook.com',
    'linkedin.com',
    'google.com',
    'instagram.com',
    'www.glassdoor.com.br',
    '*.org'
];

try {

    $options = getCommandOptions();
    $links = getGoogleLinksBySearch($options['q'], $options['e'], $options['c'] );
    var_dump($links);
} catch (\Throwable $e) {
    echo "Ocorreu um erro: " . $e->getMessage() . PHP_EOL;
    die();
}


function getGoogleLinksBySearch(string $search, ?string $excludeTerms = '', ?string $country = 'br'): array
{
    $properties = [
        'cx' => getenv('SEARCH_ENGINE_ID'),
        'key' => getenv('API_CUSTOM_SEARCH_KEY'),
        'q' => urlencode($search),
        'lr' => "lang_pt",
        'excludeTerms' => $excludeTerms,
        'siteSearch' => implode(" ", EXCLUDE_SITES),
        'siteSearchFilter' => "e",
        'gl' => $country
    ];

    $baseUrl = "https://www.googleapis.com/customsearch/v1?";
    $completeUrl = $baseUrl . http_build_query($properties);

    echo "# Fazendo Busca em $completeUrl" . PHP_EOL;

    $results = file_get_contents($completeUrl);

    $decodedResults = json_decode($results, true);
    $links = [];

    foreach ($decodedResults['items'] as $item) {
        $links[] = $item['link'];
    }

    return $links;
}


