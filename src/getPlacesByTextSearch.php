<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

function initSearch(string $textSearch, $category){

    $links = getGoogleLinksByMapsTextSearch($textSearch, $category);

    $contacts = [];

    echo "# " . count($links) . " Links encontrados" . PHP_EOL;
    echo "# Acessando sites para buscar emails " . PHP_EOL;

    foreach ($links as $link) {
        $findedInfos = getContactEmailByWebSite($link);

        if (!is_null($findedInfos['email'])) {
            $contacts[] = getContactEmailByWebSite($link);
            echo "*";
        } else {
            echo "x";
        }
    }

    echo PHP_EOL . "# Gerando arquivo ...";
    $fileNameGenerated = generateFile($contacts, $category);
    echo PHP_EOL . "# Arquivo gerado/alterado com sucesso: '$fileNameGenerated'";
}

function generateFile(array $contacts, string $category): string
{

    $fileName = clearFileName($category);
    $path = './output_files';
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }

    $completePath = './output_files/' . $fileName . '.json';

    $actualContent = json_decode(file_get_contents($completePath), true);

    if (is_file($completePath) && !empty($actualContent)) {
        $contacts = array_merge($actualContent, $contacts);
    }

    file_put_contents($completePath, json_encode($contacts));

    return $completePath;
}

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

function getGoogleLinksByMapsTextSearch(string $search, string $category): array
{
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
        CURLOPT_POSTFIELDS => '{
      "textQuery" : "' . $category . '+' . $search . '",
    }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . getenv('API_KEY_GOOGLE_MAPS'),
            'X-Goog-FieldMask: places.websiteUri'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $baseLinks = json_decode($response, true);

    $linksList = [];
    foreach ($baseLinks['places'] as $link) {

        if (isset($link['websiteUri'])) {
            $linksList[] = $link['websiteUri'];
        }
    }

    return array_filter($linksList, function ($value) {
        return $value !== null;
    });
}

function getCommandOptions(): array
{
    $options = getopt("q:c:");

    if (!isset($options['q']) || empty($options['q'])) {
        throw new \Exception("Parametro '-q' de 'query' é obrigatório");
    }

    if (!isset($options['c']) || empty($options['c'])) {
        throw new \Exception("Parametro '-c' de 'categoria' é obrigatório");
    }

    return $options;
}


function getContactEmailByWebSite(string $url)
{
    $html = fetchUrl($url);

    if (is_null($html)) {
        return ['url' => $url, 'email' => null];
    }

    $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(org|com|com\.br)/';

    $matches = getRegexInHtml($html, $emailPattern);

    if (!$matches) {
        $html = fetchUrlContact($url);
        if (is_null($html)) {
            return ['url' => $url, 'email' => null];
        }
        $matches = getRegexInHtml($html, $emailPattern);
        return  ['url' => addContactPageInEndUrl($url), 'email' => $matches];
    }

    if (empty($matches)) {
        return ['url' => $url, 'email' => null];
    } else {
        return  ['url' => $url, 'email' => $matches];
    }
}


function fetchUrl(string $url): ?string
{
    try {
        return (new Client())->get($url)->getBody()->getContents();
    } catch (TransferException $e) {
        return null;
    }
}
function fetchUrlContact(string $url): ?string
{
    return fetchUrl(addContactPageInEndUrl($url));
}


function addContactPageInEndUrl(string $url)
{
    $url .= substr($url, -1) !== '/' ? '/contato' : 'contato';
    return $url;
}

function getRegexInHtml(string $html, string $regex): ?string
{
    preg_match($regex, $html, $matches);
    return empty($matches) ? null : $matches[0];
}
