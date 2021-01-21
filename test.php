<?php require 'phpmailer/PHPMailer.php'; require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';


function send_mail()
{
    $name = "Бот";
    $email = "mailrobot@uni-dubna.ru";
    $text = "";

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    try {
        $msg = "Отчёт отправлен";
        $mail->isSMTP();
        $mail->CharSet = "UTF-8";
        $mail->SMTPAuth   = true;

        // Настройки вашей почты
        $mail->Host       = 'smtp.mailtrap.io'; // SMTP сервера GMAIL
        $mail->Username   = '204fc694f7ca8b'; // Логин на почте
        $mail->Password   = '57b84e05c848b9'; // Пароль на почте
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 2525;
        $mail->setFrom('e60031e187-4c9ff5@inbox.mailtrap.io', 'Mailrobot'); // Адрес самой почты и имя отправителя

$conf=[
'mails' => [
'andrews.mr@yandex.ru'
]
];
 foreach ($conf['mails'] as $item)
        {
            $mail->addAddress($item);
        }



 $now = date('d-m-Y');

        $mail->addAttachment('../public/storage/archive/05-03-2020.xls');

  $mail->isHTML(true);

    $mail->Subject = 'Тестовый отчёт о публикациях';
        $mail->Body = "<b>Имя:</b> $name <br>
        <b>Почта:</b> $email<br><br>
        <b>Сообщение:</b><br>$text"; // Проверяем отравленность сообщения
        if ($mail->send()) {
            return "$msg";
        } else {
            return ['error' => "Сообщение не было отправлено. Неверно указаны настройки вашей почты"];
        }

    } catch (Exception $e) {
        return ['error' => "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}"];
    }
}


send_mail();
