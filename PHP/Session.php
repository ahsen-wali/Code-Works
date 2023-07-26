<?php 
namespace App;

use App\Log;

class Session{
    
    private static $instance;
    public function __construct(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * create a singletong instance
     */
    private static function getSingleton(){
        if ( self::$instance == null ){
            self::$instance = new self;
        }

        return self::$instance;
    }
    /**
     * Put value in session
     */
    public function a_put($key,$value){
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a value exists in session
     */
    public function a_has($key){
        return isset( $_SESSION[$key] ) ? $_SESSION[$key] : false;
    }

    /**
     * get a value from session
     */
    public function a_get($key,$default=null){
        return isset( $_SESSION[$key] ) ? $_SESSION[$key] : $default;
    }

    /**
     * get and forgot value from session
     */
    public function a_pull($key,$default=null){
        $value = isset( $_SESSION[$key] ) ? $_SESSION[$key] : $default;
        unset($_SESSION[$key]);

        return $value;
    }

    /**
     * Catch all staticaly function calls
     */
    public static function __callStatic($name, $arguments){
        $methodName = 'a_'.$name;
        return call_user_func( [self::getSingleton(),$methodName ],...$arguments);
    }
}