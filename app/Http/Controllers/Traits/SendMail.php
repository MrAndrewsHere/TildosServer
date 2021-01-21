<?php


namespace App\Http\Controllers\Traits;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception ;
use PHPMailer\PHPMailer\SMTP;

trait SendMail
{

    static function mailer($debug = false)
    {
        $mail = new PHPMailer($debug);
        try {

            $mail->isSMTP();
            $debug ? $mail->SMTPDebug = SMTP::DEBUG_SERVER: null;
            $mail->CharSet = "UTF-8";
            $mail->SMTPAuth = true;

            $mail->Host = env('MAIL_HOST'); // SMTP сервера GMAIL
            $mail->Username = env('MAIL_USERNAME'); // Логин на почте
            $mail->Password = env('MAIL_PASSWORD'); // Пароль на почте
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_FROM'), env('MAIL_FROM_NAME')); // Адрес самой почты и имя отправителя

           return $mail;

        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error($mail->ErrorInfo);
            return false;
        }
    }
}
