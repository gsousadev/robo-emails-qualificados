<?php


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

    die();
} catch (\Throwable $e) {

    echo "Ocorreu um erro: " . $e->getMessage() . PHP_EOL;
    die();
}


$dom = new DomDocument();
$dom->loadHtml($html);
$dom->preserveWhiteSpace = false;
$finder = new DomXPath($dom);
$classname = "my-class";
$nodes = $finder->query("//*[contains(@class, '$classname')]");


$outputLinks = [];

var_dump($outputLinks);
die();

echo "# Fazendo leitura dos links do site"  . PHP_EOL;

foreach ($lisks as $link) {
    if (!empty($link->getAttribute('class') == "lista-completa-santos__group-list-link")) {
        $outputLinks[] = $baseUrl . $link->getAttribute('href');
    }
}
echo "# Quantidade de link encontrados para páginas de santos ..." . count($outputLinks)  . PHP_EOL;
echo "# Acessando links de paginas para capturar locais de imagens de santos ..."  . PHP_EOL;
$count = 1;

foreach ($outputLinks as $link) {
    try {


        $htmlLinks = file_get_contents($link);
        $domLinks = new domDocument;
        @$domLinks->loadHTML($htmlLinks);
        $domLinks->preserveWhiteSpace = true;
        $images = $domLinks->getElementsByTagName('img');

        foreach ($images as $image) {
            if (!empty($image->getAttribute('class') == "saint-summary__picture")) {
                $linkToSave = $baseUrl . $image->getAttribute('src');
                $outputImages[] = $linkToSave;
                echo "# Gravando local de imagem " . $count . ": " . $linkToSave  . PHP_EOL;
                $count++;
            }
        }
    } catch (Exception $e) {
        sleep(10);
        continue;
    }
}

echo "# Quantidade de locais de imagens encontradas ..." . count($outputImages)  . PHP_EOL;
echo "# Acessando locais de imagens para fazer download ..." . PHP_EOL;

$count = 1;

$dir = "temp";

if (!file_exists($dir)) {
    mkdir('temp', 0777, true);
}

foreach ($outputImages as $imageUrl) {
    try {
        $url = explode("/", $imageUrl);
        $name = $url[count($url) - 1];
        $fp = fopen($dir  . "/" . $name, 'wb');

        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US)");
        $raw_data = curl_exec($ch);
        curl_close($ch);
        fwrite($fp, $raw_data);
        fclose($fp);

        echo "# Download de imagem " . $count . " concluído: " . $name  . PHP_EOL;

        $count++;
    } catch (Exception $e) {
        sleep(10);
        continue;
    }
}


function dd(...$args)
{
    var_dump($args);
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

function getCommandOptions(): array
{
    $options = getopt("q:e::c::");

    if (!isset($options['q']) || empty($options['q'])) {
        throw new Exception("Parametro '-q' é obrigatório");
    }

    return $options;
}
