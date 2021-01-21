<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use App\Http\Controllers\Traits\SendMail;
use App\User;

class DCR extends Model
{
    use SendMail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "DCR";
    protected $fillable = [
        'project_id',
        'domain',
        'old_domain',
        'request_user',
        'response_user',

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function requestUser()
    {
        return $this->hasOne(User::class, 'id', 'request_user');
    }

    public function responseUser()
    {
        return $this->hasOne(User::class, 'id', 'response_user');
    }

    static function requestConnectionDomain($data)
    {
        try {
            $project = Project::find($data['project_id']);

            if ($dcr = DCR::where([
                ['project_id', '=', $project->id],
                ['closed', '=', 0]
            ])->first()) {
                return ['code' => 409, 'message' => 'Запрос уже открыт'];
            }
            $data = [
                'project_id' => $data['project_id'],
                'domain' => $data['domain'] . '.uni-dubna.ru',
                'old_domain' => $project->domain,
                'request_user' => auth()->user()->id
            ];
            $newDcr = DCR::create($data);
            if ($newDcr) {
                if (self::sendMailtoHD($newDcr)) {

                    return ['code' => 200, 'message' => $newDcr];
                }
                $newDcr->delete();
                return ['code' => 500, 'message' => ' Что-то пошло не так. Попробуйте позже'];
            }
            return ['code' => 500, 'message' => ' Что-то пошло не так. Попробуйте позже'];


        } catch (Exception $exception) {
            return ['code' => 500, 'message' => ' Что-то пошло не так. Попробуйте позже'];
        }
    }

    static function close($data)
    {
        $dcr = DCR::find($data['dcrID']);

        if ($dcr) {
            if ($dcr->closed) {
                return ['code' => 409, 'message' => 'Запрос уже закрыт'];
            }

            $dcr->closed = 1;
            $dcr->success = $data['success'];
            $dcr->comment = $data['comment'];
            $dcr->response_user = auth()->user()->id;
            if ($data['success']) {
                if (self::setConfig($dcr->domain)) {
                    $project = Project::find($dcr->project_id);
                    $project->domain = $dcr->domain;
                    $project->save();
                    $dcr->save();
                    return ['code' => 200, 'message' => $dcr];
                }
                return ['code' => 500, 'message' => ' Что-то пошло не так. Попробуйте позже'];
            }
            $dcr->save();
            return ['code' => 200, 'message' => $dcr];

        }
        return ['code' => 404, 'message' => 'Запрос не найден'];
    }


    static function sendMailtoHD($dcr)
    {
        if ($mail = self::mailer()) {
            $domain = $dcr->domain ? $dcr->domain : 'DomainName';
            $mail->addAddress(env('MAIL_TO_HD'));
            $mail->Subject = 'land.uni-dubna.ru: подключение домена ' . $domain;
            $mail->isHTML(true);
            $href1 = (env('APP_DEBUG') ? 'http://localhost:8080' : 'https://land.uni-dubna.ru') . '/domains?auto=true&dcrid=' . $dcr->id;
            $href2 = (env('APP_DEBUG') ? 'http://localhost:8080' : 'https://land.uni-dubna.ru') . '/domains';
            $mail->Body = $domain . ' -  <a href="' . $href1 . '">Ссылка на закрытие запроса</a>' . ' <br>
  <a href="' . $href2 . '">Все запросы</a>';

            if ($mail->send()) {
                return true;
            } else {
                Log::error($mail->ErrorInfo);
                return false;
            }
        }
        return false;
    }

    static function setConfig($domain)
    {

        try {



            $config = '
server {

        listen 443 ssl;
        gzip on;
        gzip_comp_level 4;
        gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript ;

        server_name ' . $domain . '.uni-dubna.ru;
        root /usr/share/nginx/html/land;
        index index.php index.html index.htm;

        set_real_ip_from 10.230.0.33;
        real_ip_header X-Real-IP;

        location / {
                root /usr/share/nginx/html/land/pwa;
                try_files $uri $uri/ /index.html?$query_string;
        }



        location /api {
                try_files $uri /server/public/index.php?$query_string;
        }

        location /media {
                try_files $uri /server/public/index.php?$query_string;
        }

        location ~ \.php$ {
                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
                fastcgi_index index.php;
                fastcgi_param HTTPS on;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                fastcgi_read_timeout 300;
                include fastcgi_params;
        }

        error_page 404 /404.html;
            location = /40x.html {
        }

        error_page 500 502 503 504 /50x.html;
            location = /50x.html {
        }
}


';
            $path = env('APP_DEBUG') ? '' : '/etc/nginx/conf.d/';
            file_put_contents($path . $domain . '.conf', $config);
            env('APP_DEBUG') ? null : exec('service nginx restart');
            return true;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }


}
