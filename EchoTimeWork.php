<?php
/**
 * author: Zhang Rui
 * github: https://github.com/zhangrui-git
 * mail: zhangruirui@zhangruirui.com
 * create: 2020/8/5 6:30 下午
 */


namespace PhpRunner;


class EchoTimeWork extends Work
{
    public function run(&$step = 1): bool
    {
        echo date('Y-m-d H:i:s') .'/'. $step .PHP_EOL;
        sleep(3);
        $step++;
        if ($step > 20) return false;
        return true;
    }
}