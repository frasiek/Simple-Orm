<?php
namespace SO;
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."Exception.php";
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."Db.php";
/**
 * Model
 * 2015-01-16 12:39:53
 * @author Michał Fraś m.fras@eurohost.com.pl
 */
abstract class Model {
    const INSERT = 'insert';
    const UPDATE = 'insert';
    
    /**
     * Przechowuje nazwe tabeli do ktorej sie odnosi
     * @var string
     */
    private $tableName;
    
    /**
     * Przechowuje nazwe pol bedacych kluczami podstawowymi
     * @var string | array
     */
    private $pk;
    
    /**
     * Tryb pracy - INSERT | UPDATE
     * @var string
     */
    private $mode;
    
    /**
     * obiekt bazy danych
     * @var Db
     */
    protected $db;
    
    
    /**
     * Przechowuje wartosci pol modelu
     * @var array
     */
    protected $attributes = array();
    
    /**
     * Zwraca nowa instancje modelu
     * @return __CLASS__
     */
    public static function model(\stdClass $attributes = null){
        $className = get_called_class();
        return new $className($attributes); 
    }
    
    /**
     * Ustawia tryb pracy oraz tabele oraz PK
     */
    public function __construct(\stdClass $attributes = null) {
        $this->mode = self::INSERT;
        $this->pk = $this->getPrimaryKey();
        $this->pk = is_array($this->pk)?$this->pk:array($this->pk);
        $this->tableName = $this->getTableName();
        
        if($attributes !== null){
            $this->objectToAttributes($attributes);
        }
    }
    
    /**
     * Zwraca obiekt Model wg podanego klucza podstawowego
     * 
     * Kluczem podstawowym moze byc skalar lub tablica indeksowana wg nazw pol
     * @param array | string $pk
     * @return \SO\Model
     * @throws Exception
     */
    public function findByPk($pk){
        $where = '';
        if(is_array($pk)){
            $where = $this->arrayToWhere($pk);
        } else {
            if(count($this->pk)>0){
                throw new Exception("Specyfy all primary keys");
            }
            $where = "`{$this->pk}` = ".$this->quote($pk);
        }
        $raw = $this->db->getOne("SELECT * from `{$this->tableName}` WHERE $where");
        if(!$raw){
            return null;
        }
        $this->objectToAttributes($raw);
        return $this;
    }
    
    /**
     * Zwraca jeden obiek wg zadanych atrybutow 
     * @param array $attrs
     * @return \SO\Model
     */
    public function findByAttributes(array $attrs){
        $where = $this->arrayToWhere($attrs);
        $raw = $this->db->getOne("SELECT * from `{$this->tableName}` WHERE $where");
        if(!$raw){
            return null;
        }
        $this->objectToAttributes($raw);
        return $this;
    }
    
    /**
     * Zwraca obiek wg warunku
     * @param type $where
     * @return \SO\Model
     */
    public function findByCondition($where){
        $raw = $this->db->getOne("SELECT * from `{$this->tableName}` WHERE $where");
        if(!$raw){
            return null;
        }
        $this->objectToAttributes($raw);
        return $this;
    }
    
    /**
     * Zwraca tablice obiektow wg atrybutow
     * @param array $attrs
     * @return Model[]
     */
    public function findAllByAttributes(array $attrs){
        $where = $this->arrayToWhere($attrs);
        $raw = $this->db->getAll("SELECT * from `{$this->tableName}` WHERE $where");
        if(!$raw){
            return null;
        }
        $return = array();
        foreach($raw as $new){
            $return[] = self::model($new);
        }
        return $return;
    }
    
    /**
     * Zwraca tablice obiektow wg warunku
     * @param string $where
     * @return Model[]
     */
    public function findAllByCondition($where){
        $raw = $this->db->getAll("SELECT * from `{$this->tableName}` WHERE $where");
        if(!$raw){
            return null;
        }
        $return = array();
        foreach($raw as $new){
            $return[] = self::model($new);
        }
        return $return;
    }
    
    /**
     * Zapisuje rekord do bazy
     * @return boolean | Model
     */
    public function save(){
        try{
            switch($this->mode){
                case self::INSERT:
                    $keys = array_keys($this->attributes);
                    foreach($keys as $key){
                        $key = "`{$key}`";
                    }
                    $keys = implode(",",$keys);

                    $values = array_values($this->attributes);
                    foreach($values as $val){
                        $val = $this->quote($val);
                    }
                    $values = implode(",",$values);

                    $this->db->insert("INSERT INTO `{$this->tableName}` ({$keys}) VALUES ({$values})");
                    $this->findByPk($this->getPkValues());
                    break;
                case self::UPDATE:
                    $fields = array();
                    foreach($this->attributes as $key=>$val){
                        $fields[$key] = " `$key` = {$this->quote($val)} ";
                    }

                    $pk = $this->pk;
                    $where = array();
                    foreach($pk as $field){
                        unset($fields[$field]);
                        $where[] = " `$field` = {$this->quote($this->$field)} ";
                    }
                    $fields = implode(",", $where);
                    $where = implode("AND", $where);

                    $this->db->update("UPDATE `{$this->tableName}` set $fields WHERE $where");
                    break;
            }
            return $this;
        } catch(Exception $ex){
            return false;
        }
    }
    
    /**
     * Ustawiacz atrybutow obiektu
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Zwracacz atrybutów projetu
     * @param string $name
     * @return string
     */
    public function __get($name) {
        if(!array_key_exists($name, $this->attributes)){
            return null;
        }
        return $this->attributes[$name];
    }
    
    protected function getPkValues(){
        $return = array();
        $pk = $this->pk;
        foreach($pk as $field){
            $return[$field] = $this->$field;
        }
        return $return;
    }
    
    /**
     * Zamienia stdClass na pola w modelu oraz zmiania tryb pracy
     * @param \stdClass $obj
     */
    protected function objectToAttributes(\stdClass $obj){
        foreach($obj as $key => $val){
            $this->$key = $val;
        }
        $this->mode = self::UPDATE;
    }
    
    /**
     * Zamienia tablice na string pasujacy do WHERE {str}
     * @param array $array
     * @return string
     */
    protected function arrayToWhere(array $array){
        $where = array();
        foreach($array as $key=>$val){
            $where[] = " `{$key}` = {$this->quote($val)} ";
        }
        return implode("AND", $where);
    }
    
    /**
     * Zwraca sescapowany i squotowany string
     * @param string $string
     * @return string
     */
    protected function quote($string){
        if($string === null){
            return 'NULL';
        }
        if(is_numeric($string)){
            return $string;
        }
        return "'".$this->db->escape($string)."'";
    }
    
    /**
     * @return string nazwa tabeli
     */
    protected abstract function getTableName();
    
    /**
     * @return array | string pole/pola z primary key
     */
    protected abstract function getPrimaryKey();
    
    
}
