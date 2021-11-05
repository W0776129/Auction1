<?php
namespace App\Lib;

use http\Encoding\Stream;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionException;


class Database{
    /**
     * @var Database
     */
    private static $databaseObj;

    /**
     * @var PDO
     */
    private $connection;

    /**
     * @return Database
     */
    public static function getConnection() {
        if(!self::$databaseObj)
            self::$databaseObj= new self();
        return self::$databaseObj;
    }

    private function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER,DB_PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch (PDOException $e){
            die();
        }
    }
    public function __destruct(){
        $this->connection=null;
    }

    /**
     * @param string $sql
     * @param string|array $bindVal
     * @param bool|false $retStmt
     * @return bool|\PDOStatement|void
     */
    public function sqlQuery(string $sql, $bindVal = null, bool $retStmt = false){
        try {
            $statement = $this->connection->prepare($sql);
            if(is_array($bindVal)){
                $result = $statement->execute($bindVal);
            }else{
                $result = $statement->execute();
            }
            if($retStmt){
                return $statement;
            }else{
                return $result;
            }

        }catch (PDOException $e){
            die();
        }
    }

    public function fetch(string $sql,string $class,$bindVal = null){
        $statement = $this->sqlQuery($sql,$bindVal,true);
        if($statement->rowCount() == 0){
            return [];
        }
        try {
            $reflect = new ReflectionClass($class);
            if($reflect->getConstructor() == null){
                $ctor_args = [];
            }else{
                $num =count($reflect->getConstructor()->getParameters());
                $ctor_args = array_fill(0,$num,null);
            }
            return $statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,$class,$ctor_args);
        }catch (ReflectionException $e){
            die();
        }
    }

    public function lastInsertId(){
        return $this->connection->lastInsertId();
    }
    public function rowCount(string $sql,$bindVal = null){
        $statement = $this->sqlQuery($sql,$bindVal,true);
        return $statement->rowCount();
    }
}

?>