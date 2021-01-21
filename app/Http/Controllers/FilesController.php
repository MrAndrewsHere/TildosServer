<?php


namespace App\Http\Controllers;

use App\Files;
use http\Env;
use Illuminate\Http\Request;

class FilesController extends Controller
{

    private $files;

    public function __construct()
    {
        $this->files = new Files();
    }

    function getcss(){

       return  response(file_get_contents('C:\xampp\htdocs\www\src\styles\elements.css'));

    }

    function uploadFile(Request $request){
        $image = $request->file("file");
        $imageId = $this->files->UploadFile($image);
        $url = env('APP_URL') . "/api/files/" . $imageId;
        $apiUrl = "files/" . $imageId;
        $res = [
            "file" => [
                "url" => $url,
                "apiUrl" => $apiUrl,
            ]
        ];
        return response()->json($res, 200);
    }

    function getFile(Request $request, $fileId){
        $file = $this->files->getFile($fileId);
        if(!$file){
            return response()->json([
                "message" => "Фаил не найден"
            ], 404);
        }
        return response()->download(env("MEDIA_PATH") . '/' . $file->path, $file->name);

    }



}
