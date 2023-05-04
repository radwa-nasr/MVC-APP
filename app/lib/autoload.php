<?php

namespace PROJECT\LIB;

class Autoload
{
  public static function autoload($className)
  {
    // $class = str_replace('\\', '/', $className);
    // $classFile = APP_PATH . DS . strtolower($class) . '.php';
    // if (file_exists($classFile)) {
    //   require $classFile;
    // }
    $className = str_replace("PROJECT", '', $className);
    $className = str_replace('\\', '/', $className);
    $className = $className . '.php';
    $className = strtolower($className);

    if (file_exists(APP_PATH . $className)) {
      require_once APP_PATH . $className;
    }
  }
}

spl_autoload_register(__NAMESPACE__  . '\Autoload::autoload');
