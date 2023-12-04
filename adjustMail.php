<?php


$types = ['json'];
$pathIn = './output_files';
$dir = new DirectoryIterator($pathIn);
foreach ($dir as $fileInfo) {





    if ($fileInfo->isFile()) {
        $originalPath = $fileInfo->getPathname();
        $category = str_replace('.json', '', $fileInfo->getFilename());
        $content = json_decode(file_get_contents($originalPath), true);
        $mailsFile = './mails/' . $category . '.csv';
        $mailsPath = './mails/';
        if (!is_dir($mailsPath)) {
            mkdir($mailsPath, 0777, true);
        }

        $mails = array_map(function($line){
            return $line['email'];
        }, $content);

        $mails = array_unique($mails);
        $mails = array_filter($mails, function($mail){
            return 
            !str_contains($mail, 'example')
            && !str_contains($mail, 'email')
            && !str_contains($mail, 'exemplo')
            && !str_contains($mail, 'test')
            && !str_contains($mail, 'teste')
            && !str_contains($mail, 'wixpress');
        });

        $fp = fopen($mailsFile, 'w');
        fputcsv($fp, ['mail', 'category']);

        foreach ($mails as $mail) {
            fputcsv($fp, [$mail, $category]);
        }



        fclose($fp);

        echo $mailsFile . " = OK" . PHP_EOL;
    }
}
