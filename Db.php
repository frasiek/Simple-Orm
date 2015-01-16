<?php
namespace SO;
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."Exception.php";
/**
 * SODb
 * 2015-01-16 11:13:38
 * @author Michał Fraś m.fras@eurohost.com.pl
 */
class Db {
    /**
     * Połączenie z bazą danych
     * @var \mysqli
     */
    private $db;
    private static $host;
    private static $user;
    private static $password;
    private static $database;
    private static $port;
    private static $socket;
    
    static function setHost($host) {
        self::$host = $host;
        return self;
    }

    static function setUser($user) {
        self::$user = $user;
        return self;
    }

    static function setPassword($password) {
        self::$password = $password;
        return self;
    }

    static function setDatabase($database) {
        self::$database = $database;
        return self;
    }

    static function setPort($port) {
        self::$port = $port;
        return self;
    }

    static function setSocket($socket) {
        self::$socket = $socket;
        return self;
    }

    static function setInstance(SODb $instance) {
        self::$instance = $instance;
        return self;
    }

    /**
     * Ustawienie połączenia z bazą danych
     * 
     * Metoda ustawia globalne połączenie z bazą danych
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param string $port
     * @param string $socket
     */
    static public function setConnection($host, $user, $password, $database, $port = null, $socket = null){
        self::$host = $host;
        self::$user = $user;
        self::$password = $password;
        self::$database = $database;
        self::$port = $port;
        self::$socket = $socket;
    }
        
    /**
     * @var Db
     */
    private static $instance = null;
    
    /**
     * Zwraca instancje klasy Db
     * @return Db
     */
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new Db(self::$host, self::$user, self::$password, self::$database, self::$port, self::$socket);
        }
        return self::$instance;
    }
    
    private function __construct($host, $user, $password, $database, $port = 3306) {
        $this->db = new \mysqli($host, $user, $password, $database, $port);
        if($this->db->connect_errno > 0){
            throw new SOException($this->db->connect_error, $this->db->connect_errno);
        }
        $this->db->query("USE `$database`");
        if($this->db->errno > 0){
            throw new Exception($this->db->error, $this->db->errno);
        }
        $this->db->query("SET NAMES utf8");
        if($this->db->errno > 0){
            throw new Exception($this->db->error, $this->db->errno);
        }
    }
    
    /**
     * Zwraca informację - czy połączenie jest utrzymane
     * @return bool
     */
    public function connected(){
        return (bool)$this->db->ping();
    }
    
    /**
     * Zamyka połącznie z bazą danych
     * @return boolean
     */
    public function close(){
        if($this->connected()){
            return $this->db->close();
        }
        return false;
    }

    /**
     * Zwraca pierwszy wynik zapytania
     * @param string $query
     * @return object | null
     * @throws Exception
     */
    public function getOne($query){
        $result = $this->db->query($query);
        if($this->db->errno > 0){
            throw new Exception($this->db->error, $this->db->errno);
        }
        if(!$result || !($result instanceof \mysqli_result)){
            return null;
        }
        $data = $result->fetch_object();
        $result->close();
        return $data;
    }
    
    /**
     * Zwraca wszystkie wyniki zapytania
     * @param string $query
     * @return object[] | null
     * @throws Exception
     */
    public function getAll($query){
        $result = $this->db->query($query);
        if($this->db->errno > 0){
            throw new Exception($this->db->error, $this->db->errno);
        }
        if(!$result || !($result instanceof \mysqli_result)){
            return null;
        }
        $data = array();
        while($tmp = $result->fetch_object()){
            $data[] = $tmp;
        }
        $result->close();
        return $data;
    }
    
    /**
     * Zwraca pierwsza kolumne pierwszego wiersza
     * @param string $query
     * @return string
     * @throws Exception
     */
    public function getScalar($query){
        $result = $this->db->query($query);
        if($this->db->errno > 0){
            throw new Exception($this->db->error, $this->db->errno);
        }
        if(!$result || !($result instanceof \mysqli_result)){
            return null;
        }
        $data = $result->fetch_array();
        $result->close();
        return array_shift($data);
    }
    
    /**
     * Wykonuje zadane modyfkacje i zwraca ilosc zmodyfikowanych
     * @param string $query
     * @return int
     * @throws Exception
     */
    public function update($query){
        $this->db->query($query);
        if($this->db->errno > 0){
            throw new Exception($this->db->error, $this->db->errno);
        }
        return $this->db->affected_rows;
    }
    
    /**
     * Dodanie nowego i zwraca ilosc zmodyfikowanych
     * @param string $query
     * @return int
     * @throws Exception
     */
    public function insert($query){
        return $this->update($query);
    }
    
    /**
     * Wykonuje zadane usuniecia i zwraca ilosc zmodyfikowanych
     * @param string $query
     * @return int
     * @throws Exception
     */
    public function delete($query){
        return $this->update($query);
    }
    
    /**
     * Wykonuje zapytanie na bazie danych i sprawdza czy nie bylo bledow
     * @param string $query
     * @return mixed
     */
    public function query($query){
        $result = $this->db->query($query);
        if($this->db->errno > 0){
            throw new Exception($this->db->error, $this->db->errno);
        }
        return $result;
    }
    
    /**
     * Wycina potencjalnie niebezpieczne znaki ze stringu
     * @param string $str string to escape
     * @return string escaped string
     */
    public function escape($str){
        return $this->db->escape_string($str);
    }
    
}
