> [![fork](https://gitee.com/zhangrui-git/php-runner/badge/fork.svg?theme=dark)](https://gitee.com/zhangrui-git/php-runner/members) [![star](https://gitee.com/zhangrui-git/php-runner/badge/star.svg?theme=dark)](https://gitee.com/zhangrui-git/php-runner/stargazers)
> php pcntl process project 
```php
<?php
/**
 * author: Zhang Rui
 * github: https://github.com/zhangrui-git
 * mail: zhangruirui@zhangruirui.com
 * create: 2020/8/5 6:30 下午
 */

include_once './Bootstrap.php';
PhpRunner\Bootstrap::start();
$echo = new PhpRunner\EchoTimeWork();
$echo->step = 10;

$proc = new PhpRunner\Process(4, false);
$proc->addWork($echo);
$proc->main($argv);
```
`cli$ php phprunner start`