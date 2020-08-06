<?php
/**
 * author: Zhang Rui
 * github: https://github.com/zhangrui-git
 * mail: zhangruirui@zhangruirui.com
 * create: 2020/8/5 6:30 下午
 */


namespace PhpRunner;


class Process
{
    private $parallel = 1;
    private $pidFile = './php-runner.pid';
    private $pid = null;
    private $childPid = [];
    private $daemon = true;
    private $works = [];
    /* @var Work */
    private $work = null;
    private $processName = '';

    public function __construct($parallel = 1, $daemon = true)
    {
        pcntl_async_signals(true);
        register_shutdown_function([$this, 'shutdownHandler']);
        pcntl_signal(SIGHUP, [$this, 'signal']);
        pcntl_signal(SIGINT, [$this, 'signal']);
        pcntl_signal(SIGTERM, [$this, 'signal']);

        $this->parallel = $parallel;
        $this->daemon = $daemon;
    }

    public function addWork(Work $work)
    {
        array_push($this->works, $work);
        return $this;
    }

    public function main($argv) {
        if (count($argv) < 2) {
            $this->help($argv[0]);
            exit(0);
        }

        switch ($argv[1]) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->restart();
                break;
            default:
                $this->help($argv[1]);
                break;
        }
        exit(0);
    }

    private function start()
    {
        if (file_exists('./'. $this->pidFile)) {
            echo 'The pid file '. $this->pidFile .' exists.'.PHP_EOL;
            exit(0);
        }

        if (count($this->works) == 0) {
            echo 'There is no task to perform.'.PHP_EOL;
        }

        // 后台启动
        if ($this->daemon) {
            $pid = pcntl_fork();
            if ($pid === 0) {
                $this->parentStart();
            }
            if ($pid < 0) {exit(1);} else {exit(0);}
        }
        // 前台启动
        else {
            $this->parentStart();
        }
    }

    private function parentStart()
    {
        $this->pid = getmypid();
        $this->childStart();
        $this->processName = 'Master';
        echo $this->processName .' start pid = '. $this->pid .PHP_EOL;

        file_put_contents($this->pidFile, $this->pid);

        while (1) {
            sleep(3);
            pcntl_signal_dispatch();
        }
    }

    private function childStart()
    {
        foreach ($this->works as $work) {
            /* @var Work $work */
            $workClass = get_class($work);
            for ($p = 1; $p <= $this->parallel; $p++) {
                $this->processName = $workClass .' '. $p .'/'. $this->parallel;
                $pid = pcntl_fork();
                if ($pid === 0) {
                    $this->pid = getmypid();
                    $work->execute();
                    exit(0);
                } elseif ($pid === -1) {
                    echo 'The child process failed to start while running the '. $workClass .'.'.PHP_EOL;
                    exit(1);
                } else {
                    $this->childPid[] = $pid;
                    echo '['. $this->processName .'] start pid = '. $pid .PHP_EOL;
                }
            }
        }
    }

    private function stop()
    {
        if (file_exists($this->pidFile) == false) {
            echo 'The pid file '. $this->pidFile .' non-exists.'.PHP_EOL;
            exit(0);
        }

        $pid = file_get_contents($this->pidFile);
        if ($pid && is_numeric($pid)) {
            posix_kill($pid, SIGTERM);
        }
        if (file_exists($this->pidFile)) {
            @unlink($this->pidFile);
        }
    }

    private function restart()
    {
        $this->stop();
        $this->start();
    }

    private function help($proc)
    {
        echo 'cli:$ php phprunner.php [start | stop | restart | help] '. $proc .PHP_EOL;
    }

    public function signal($signal_no)
    {
        if ($this->childPid) {
            foreach ($this->childPid as $pid) {
                posix_kill($pid, SIGTERM);
            }
            exit(0);
        } else {
            if ($this->work) {
                $this->work->stop();
            }
        }
    }

    public function shutdownHandler()
    {
        if ($this->pid) {
            echo $this->processName .' shutdown.'.PHP_EOL;
            if (count($this->childPid)) {
                if (file_exists($this->pidFile)) {
                    @unlink($this->pidFile);
                }
            }
        }
    }
}