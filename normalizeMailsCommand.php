<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';
require 'commonFunctions.php';

try {
    $types = ['json'];
    $path = './output_files';
    $dir = new DirectoryIterator($path);
    /**
     * @var DirectoryIterator $fileInfo
     */
    foreach ($dir as $fileInfo) {
        $filename = $fileInfo->getPathname();
        $mailList = getMailList($filename);
        sendMailByList($mailList);
    }
} catch (\Throwable $e) {
    echo "Ocorreu um erro: " . $e->getMessage() . PHP_EOL;
}


function getMailList(string $filename): array
{
    $content = json_decode(file_get_contents($filename));
    return array_map(function ($line): string {
        return $line['email'];
    }, $content);
}

function sendMailByList(array $mailList)
{




    $mail = new PHPMailer(true);

    try {
        // Configurar o servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'seu_servidor_smtp';
        $mail->SMTPAuth = true;
        $mail->Username = 'seu_endereco_de_email';
        $mail->Password = 'sua_senha';
        $mail->SMTPSecure = 'tls'; // Pode mudar para 'ssl' se necessário
        $mail->Port = 587; // Ajuste a porta de acordo com sua configuração

        // Configurar a mensagem
        $mail->setFrom('', 'Nome do Remetente');
        $mail->addAddress('');
        $mail->Subject = 'Assunto do e-mail';
        $mail->Body = 'Corpo do e-mail.';

        // Enviar o e-mail
        $mail->send();
        echo "E-mail enviado com sucesso.";
    } catch (Exception $e) {
        echo "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
    }
}
