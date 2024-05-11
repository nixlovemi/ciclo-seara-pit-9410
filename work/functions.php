<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once('php-mailer/Exception.php');
require_once('php-mailer/PHPMailer.php');
require_once('php-mailer/SMTP.php');

function dd()
{
    ob_clean();
    echo '<pre>';
    foreach (func_get_args() as $x) {
        var_dump($x);
    }
    die;
}

function readConfig()
{
    $file = file_get_contents('config.json');
    return json_decode($file, true);
}

function sendEmail($to, $title, $body, $arrFiles=[])
{
    include_once('session.php');
    $config = $_SESSION['config'];
    $configEmail = $config['email'];

    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->CharSet = "UTF-8";
    $mail->Mailer = $configEmail['Mailer'];
    $mail->SMTPDebug = 0;  
    $mail->SMTPAuth = $configEmail['SMTPAuth'];
    $mail->SMTPSecure = $configEmail['SMTPSecure'];
    $mail->Port = $configEmail['Port'];
    $mail->Host = $configEmail['Host'];
    $mail->Username = $configEmail['Username'];
    $mail->Password = $configEmail['Password'];
    $mail->IsHTML(true);
    $mail->SetFrom($configEmail['FromEmail'], $configEmail['FromName']);

    // Add Attachment
    foreach ($arrFiles as $filePath) {
        $mail->addAttachment($filePath);
    }

    $mail->Subject = $title;
    $mail->AddAddress($to);
    $mail->MsgHTML($body);

    if(!$mail->Send()) {
        // echo "Error while sending Email.";
        // var_dump($mail);

        $log = fopen("logs/" . date('YmdHisu') . ".txt", "w") or die("Unable to open file!");
        $txt = var_export($mail, true);
        fwrite($log, $txt);
        fclose($log);

        return 0;
    }
    
    return 1;
}