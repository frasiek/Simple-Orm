<?php

/**
 * SampleModel
 * 
 * plik testowego modelu
 * 2015-01-16 13:09:31
 * @author Michał Fraś m.fras@eurohost.com.pl
 */
class SampleModel extends SO\Model{
    
    protected function getPrimaryKey() {
        return 'id';
    }

    protected function getTableName() {
        return 'test_table';
    }
    
}
