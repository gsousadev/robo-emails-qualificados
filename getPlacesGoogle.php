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
    $links = getGoogleLinksBySearch($options['q'], $options['e'], $options['c']);
   

    die();
} catch (\Throwable $e) {

    echo "Ocorreu um erro: " . $e->getMessage() . PHP_EOL;
    die();
}

dd($links);

foreach ($links as $link) {
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


function dd(...$args)
{
    var_dump($args);
    die();
}

function getGoogleLinksBySearch(string $search, ?string $excludeTerms = '', ?string $country = 'br'): array
{
    $properties = [
        'cx' => "17e10092d249747cf",
        'key' => "AIzaSyDd-sbd5zCh3uOYt3JAniDz91sHsnbllUc",
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




    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://places.googleapis.com/v1/places:searchText',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
      "textQuery" : "clinica veterinaria zona sul são paulo"
    }',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'X-Goog-Api-Key: AIzaSyB6chQfK0Ffcvg9tsgCoI0HzC_TmO-lyCM',
        'X-Goog-FieldMask: places.websiteUri'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo $response;

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


function buscarElementosHtml()
{
    $qp = html5qp("https://www.infomoney.com.br/mercados/cambio");
    /* Captura os atributos "tr" da tag "tbody" da primeira tabela coma  classe "table-general" */
    $values = $qp->find("table.table-general:first tbody tr");
    /* Percorre os valores capturados */
    foreach ($values as $value) {
        /* Armazenamos em um array para posteriormente exibir aos usuários. */
        $currencies[] = [
            "img"           => trim($value->find('td:eq(2) img')->attr("src")),
            "name"          => trim($value->find('td:eq(1)')->text()),
            "purchasePrice" => trim($value->find('td:eq(3)')->text()),
            "salePrice"     => trim($value->find('td:eq(0)')->text()),
        ];
    }
}
