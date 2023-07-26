<?php 
require_once($_SERVER['DOCUMENT_ROOT']."/app/config/app.php");

function config($key){
    $app = $GLOBALS['app'];
    return isset($app[$key])? $app[$key] : null;
}

function url($path){
    $url = config('base_url').$path;
    return preg_replace('/([^:])(\/{2,})/', '$1/', $url);
}


function stringBetween($str,$first,$last){
    $startPos = strpos($str,$first);

    $endPos = strpos($str,$last,$startPos);

    $exludingPart = count(
        str_split($first)
    );

    $startPos += $exludingPart;

    $length = ($endPos - $startPos);

    return substr($str,$startPos,$length);
}

function json($data){
    return json_encode($data);
}
function storage_path($path){
    $pathToStorage=$_SERVER['DOCUMENT_ROOT'].'/storage/'.$path."/";

    if( !file_exists($pathToStorage) ){
        mkdir($pathToStorage);
    }

    return $pathToStorage;
}

function public_path($path){
    $path =  $_SERVER['DOCUMENT_ROOT']."/public/". $path;

    return preg_replace('/([^:])(\/{2,})/', '$1/', $path);
}

function currentUrl(){
    $url = url($_SERVER['REQUEST_URI']);

    $cleanUrl  = preg_replace('/([^:])(\/{2,})/', '$1/', $url);

    return $cleanUrl;
}

function str_slug($string){
    $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    return strtolower($slug);
 }

 function postImagePath($name){
    $path =  $_SERVER['DOCUMENT_ROOT']."/public/images/posts/". $name;

    return preg_replace('/([^:])(\/{2,})/', '$1/', $path);
 }
 function setErrorHandler(){
    set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
    
        throw new ErrorException($errstr,$errno, $errno, $errfile, $errline);
    });
 }