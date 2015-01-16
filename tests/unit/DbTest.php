<?php

use SO\Db;
use SO\Exception;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-01-16 at 10:24:26.
 */
class DbTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Db
     */
    protected $object;

    CONST HOST = 'localhost';
    CONST DATABASE = 'sodb';
    CONST LOGIN = 'root';
    CONST PASSWORD = 'root';

    protected function connect() {
        if (!($this->object instanceof Db) || !($this->object->connected())) {
            Db::setConnection(self::HOST, self::LOGIN, self::PASSWORD, self::DATABASE);
            $this->object = Db::getInstance();
        }
        $this->object->query("DROP TABLE test_table");
        $this->object->query("
                CREATE TABLE `test_table` (
                    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `nazwa` VARCHAR(50) NOT NULL,
                    `date` DATE NULL DEFAULT NULL,
                    `quantity` INT(11) NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `nazwa` (`nazwa`)
                )
                COLLATE='utf8_general_ci'
                ENGINE=InnoDB");
        $this->object->query("INSERT INTO test_table values (1, 'test', '2015-01-16', 1)");
    }

    public function testConnection() {
        try {
            $this->connect();
            $this->assertTrue($this->object->connected());
        } catch (Exception $ex) {
            $this->assertTrue(false);
        }
    }

    /**
     * @covers SO\Db::getOne
     * @todo   Implement testGetOne().
     */
    public function testGetOne() {
        $this->connect();
        $one = $this->object->getOne("SELECT * FROM test_table");
        $this->assertInstanceOf("stdClass", $one);
        $this->assertTrue($one->id == 1);
    }

    /**
     * @covers SO\Db::getAll
     * @todo   Implement testGetAll().
     */
    public function testGetAll() {
        $this->connect();
        $all = $this->object->getAll("SELECT * FROM test_table");
        $this->assertInternalType("array", $all);
        foreach ($all as $one) {
            $this->assertInstanceOf("stdClass", $one);
            $this->assertTrue($one->id == 1);
            break;
        }
    }

    /**
     * @covers SO\Db::getScalar
     * @todo   Implement testGetScalar().
     */
    public function testGetScalar() {
        $this->connect();
        $scalar = $this->object->getScalar("SELECT * FROM test_table");
        $this->assertInternalType("string", $scalar);
        $this->assertTrue($scalar == 1);
    }

    /**
     * @covers SO\Db::update
     * @todo   Implement testUpdate().
     */
    public function testUpdate() {
        $this->connect();
        $affected = $this->object->update("UPDATE test_table set `quantity` = round(rand()*100) where id = 1");
        $this->assertInternalType("int", $affected);
        $this->assertTrue($affected == 1);
    }

    /**
     * @covers SO\Db::delete
     * @todo   Implement testDelete().
     */
    public function testDelete() {
        $this->connect();
        $affected = $this->object->delete("DELETE FROM test_table where id = 1");
        $this->assertInternalType("int", $affected);
        $this->assertTrue($affected == 1);
    }

}