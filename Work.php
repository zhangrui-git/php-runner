<?php
/**
 * author: Zhang Rui
 * github: https://github.com/zhangrui-git
 * mail: zhangruirui@zhangruirui.com
 * create: 2020/8/5 6:30 下午
 */


namespace PhpRunner;


use ReflectionException;
use ReflectionMethod;

abstract class Work
{
    private $parameter = null;
    private $run       = true;

    public function execute()
    {
        try {
            $fireArgs = [];
            $refMethod = new ReflectionMethod(get_class($this), 'run');
            foreach ($refMethod->getParameters() as $arg) {
                if ($this->parameter[$arg->getName()]) {
                    $fireArgs[$arg->getName()] = &$this->parameter[$arg->getName()];
                } else {
                    $fireArgs[$arg->getName()] = null;
                }
            }
            while ($this->run) {
                $this->run &= $refMethod->invokeArgs($this, $fireArgs);
            }
        } catch (ReflectionException $e) {
            $this->run = false;
            echo $e->getMessage() .PHP_EOL;
        }
    }

    public function stop()
    {
        $this->run = false;
    }

    public function __set($name, $value)
    {
        $this->parameter[$name] = $value;
    }

    abstract public function run(): bool;
}