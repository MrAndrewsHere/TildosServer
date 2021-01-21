<?php
/**
 * Created by PhpStorm.
 * User: ed
 * Date: 12.03.19
 * Time: 11:54
 */

namespace App;

use DB;

class Files
{
    public function UploadFile($file){
        $originalName = $this->ru2lat($file->getClientOriginalName());
        $extension = $file->getClientOriginalExtension();
        $name = substr(md5($originalName . date('YmdHis')), 0, 5);
        $folder = substr(md5($originalName . date('YmdHis')), 0, 2);
        $file->move(env('MEDIA_PATH') . "/$folder", $name . '.' . $extension );
        $data = [
            'name' => $originalName,
            'path' => "$folder/$name.$extension",
            'extension' => $extension
        ];
        $result = DB::table('files')->insertGetId($data);
        return $result;
    }

    public function getFile($fileId){
        $result = DB::select("select id, name, extension, path from files where id = ?", [$fileId]);
        if(count($result) > 0){
            return $result[0];
        } else {
            return null;
        }
    }

    public function GetTestBySub($subId){
        $result = DB::select("select files.id, files.name, files.extension, files.path from student_course sc left join courses on courses.id = sc.course_id left join files on files.id = courses.test_id where sc.id = ?", [$subId]);
        if(count($result) > 0){
            return $result[0];
        } else {
            return null;
        }
    }
    function ru2lat($str)
    {
        $tr = array(
            "А"=>"a", "Б"=>"b", "В"=>"v", "Г"=>"g", "Д"=>"d",
            "Е"=>"e", "Ё"=>"yo", "Ж"=>"zh", "З"=>"z", "И"=>"i",
            "Й"=>"j", "К"=>"k", "Л"=>"l", "М"=>"m", "Н"=>"n",
            "О"=>"o", "П"=>"p", "Р"=>"r", "С"=>"s", "Т"=>"t",
            "У"=>"u", "Ф"=>"f", "Х"=>"kh", "Ц"=>"ts", "Ч"=>"ch",
            "Ш"=>"sh", "Щ"=>"sch", "Ъ"=>"", "Ы"=>"y", "Ь"=>"",
            "Э"=>"e", "Ю"=>"yu", "Я"=>"ya", "а"=>"a", "б"=>"b",
            "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ё"=>"yo",
            "ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"j", "к"=>"k",
            "л"=>"l", "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p",
            "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f",
            "х"=>"kh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", "щ"=>"sch",
            "ъ"=>"", "ы"=>"y", "ь"=>"", "э"=>"e", "ю"=>"yu",
            "я"=>"ya", " "=>"-", "."=>"", ","=>"", "/"=>"-",
            ":"=>"", ";"=>"","—"=>"", "–"=>"-"
        );
        return strtr($str,$tr);
    }
    ////////////V2///////////////////////////

    public function UploadFileV2($file){
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $name = substr(md5($originalName . date('YmdHis')), 0, 5);
        $folder = substr(md5($originalName . date('YmdHis')), 0, 2);
        $file->move(env('MEDIA_PATH') . "/checks/$folder", $name . '.' . $extension );
        $data = [
            'name' => $originalName,
            'path' => "$folder/$name.$extension",
            'extension' => $extension
        ];
        $result = DB::table('files')->insertGetId($data);
        return $result;
    }

}
