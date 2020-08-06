<?php
/**
 * author: Zhang Rui
 * github: https://github.com/zhangrui-git
 * mail: zhangruirui@zhangruirui.com
 * create: 2020/8/5 6:30 下午
 */


namespace PhpRunner;


use Throwable;

class Bootstrap
{
    private static $instance;
    private function __construct()
    {}

    public static function start()
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        spl_autoload_register([self::$instance, 'autoload']);
//        set_error_handler([self::$instance, 'errorHandler']);
//        set_exception_handler([self::$instance, 'exceptionHandler']);
    }

    private function autoload($className)
    {
        $class_name = explode('\\', $className);
        $file = '';
        switch ($class_name[0]) {
            case 'PhpRunner':
                $file = './'. $class_name[1] .'.php';
                break;
        }
        if (file_exists($file)) {
            include_once $file;
        }
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        echo 'Service error'. PHP_EOL;
        return false;
    }

    private function exceptionHandler(Throwable $e)
    {
        echo 'Service exception'. PHP_EOL;
    }
}