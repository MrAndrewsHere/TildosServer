<?php

namespace App\Http\Controllers;
//require 'phpmailer/PHPMailer.php';
//require 'phpmailer/SMTP.php';
//require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;



class FormController extends BaseController
{

    public function submit(Request $request){

        $data = [];
        $fields = [];
        $req = $request->all();
        foreach (json_decode($req['fields']) as $field)
        {
            $temp = json_decode($field);
            $fields[]= [
                'title' => $temp->title->val,
                'val' => $temp->val,
            ];
        }
        $data['fields'] = $fields;
        $files = [];
        $path = '../storage/app/tempstorage/';
        foreach ($request->allFiles() as $file)
        {
            $originalName = $file->getClientOriginalName();
            $file->move( $path,$originalName);
            $files[]=$path.$originalName;

        }

        $data['files'] = $files;
        $data['to'] = explode(',',$req['mailrecipirnt']);
        $data['formname'] = $req['formname'];
        $data['domain'] = $req['domain'];


        $response = $this->send_mail($data);


        return response()->json($response,200);
    }

    function send_mail($data)
    {
        $mail = new PHPMailer();
        try {

            $mail->isSMTP();
//            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->CharSet = "UTF-8";
            $mail->SMTPAuth   = true;

            // Настройки вашей почты
            $mail->Host       = env('MAIL_HOST'); // SMTP сервера GMAIL
            $mail->Username   = env('MAIL_USERNAME'); // Логин на почте
            $mail->Password   = env('MAIL_PASSWORD'); // Пароль на почте
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_FROM'), env('MAIL_FROM_NAME')); // Адрес самой почты и имя отправителя

            foreach ($data['to'] as $item)
            {
                $mail->addAddress($item);
            }



            foreach ($data['files'] as $file)
            {
                $attache[]= $mail->addAttachment($file);

            }



            $mail->isHTML(true);

            $mail->Subject = $data['domain'].' : '.$data['formname'];
            $mail->Body="";
            foreach ($data['fields'] as $field)
            {
                $gg = '<b>'.$field['title'].':</b> '.$field['val'].'<br>';
                $mail->Body.=$gg;
            }

            if ($mail->send()) {
                return ['success' => "Сообщение отправлено"];
            } else {
                return ['error' => "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}"];
            }

        } catch (Exception $e) {
            return ['error' => "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}"];
        }
    }

}
