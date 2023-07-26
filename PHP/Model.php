<?php 
namespace App\Models;

use Exception;
use mysqli;

class Model{

    protected $db;

    public function __construct(){
        $this->db = new mysqli( config("DATABASE_HOST") , config("DATABASE_USER"), config("DATABASE_PASSWORD"), config("DATABASE_NAME"));
        if ( $this->db->connect_errno ){
            throw new Exception("Error while Connecting to DB:".$this->db->connect_error);
        }
    }


    public static function __callStatic($name, $arguments)
    {
        $obj = self::getInstance();

        if ( method_exists($obj,'_'.$name) ){
            return call_user_func( [$obj,'_'.$name], ...$arguments);
        }

        throw new Exception("Method not exits ");
    }



    public function __call($name, $arguments)
    {
        $obj = self::getInstance();
        
        if ( method_exists($obj,'_'.$name) ){
            return call_user_func( [$obj,'_'.$name], ...$arguments);
        }

        throw new Exception("Method not exits ");
    }



    protected static function getInstance(){
        $class = get_called_class();
        return new $class;
    }
}
    