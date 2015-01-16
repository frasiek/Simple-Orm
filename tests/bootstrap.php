<?php

/**
 * @author mfras
 */
// TODO: check include path
//ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../../../Apache/php/ext'.PATH_SEPARATOR.dirname(__FILE__).'/../../../Apache/php');

spl_autoload_register(function($className){
    $className = str_replace("SO\\", "", $className);
    $file = dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.$className.".php";
    if(file_exists($file)){
        require_once $file;
        return true;
    }
    return false;
},false,true);