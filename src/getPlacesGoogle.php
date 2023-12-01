<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

try {
    $options = getCommandOptions();

    $textSearch = $options['q'];

    $links = getGoogleLinksByMapsTextSearch($textSearch);

    $contacts = [];

    echo "# " . count($links) . " Links encontrados" . PHP_EOL;
    echo "# Acessando sites para buscar emails " . PHP_EOL;

    foreach ($links as $link) {
        $contacts[] = getContactEmailByWebSite($link);
        echo "*";
    }

    echo PHP_EOL . "# Gerando arquivo ...";
    generateFile($contacts,$textSearch);
    echo PHP_EOL . "# Arquivo gerado com sucesso: 'output_files/";
} catch (\Throwable $e) {
    echo "Ocorreu um erro: " . $e->getMessage() . PHP_EOL;
}

function generateFile(array $contacts, string $textSearch): string
{

    $fileName = clearFileName($textSearch) . "_" . (new DateTime())->format('Y-m-d_H:i:s');
    $path = './output_files';
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }

    $completePath = './output_files/' . $fileName . '.json';

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

function getGoogleLinksByMapsTextSearch(string $search): array
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
      "textQuery" : "' . $search . '"
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
    $options = getopt("q:");

    if (!isset($options['q']) || empty($options['q'])) {
        throw new \Exception("Parametro '-q' é obrigatório");
    }

    return $options;
}


function getContactEmailByWebSite(string $url)
{
    $html = fetchUrl($url);

    if (is_null($html)) {
        return ['url' => $url, 'email' => null];
    }

    $emailPattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';

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
