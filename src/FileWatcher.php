<?php


namespace EasySwoole\FileWatcher;


use EasySwoole\Component\Process\Config;
use EasySwoole\FileWatcher\Scanner\FileScanner;
use EasySwoole\FileWatcher\Scanner\Inotify;
use EasySwoole\FileWatcher\Scanner\ScannerInterface;
use Swoole\Server;

class FileWatcher
{
    private $rules = [];
    private $onChange;
    private $onException;
    private $driver;
    private $checkInterval = 1000;

    function __construct()
    {
        if(function_exists('inotify_init')){
            $this->driver = Inotify::class;
        }else{
            $this->driver = FileScanner::class;
        }
    }


    function setScannerDriver(string $class):FileWatcher
    {
        $ref = new \ReflectionClass($class);
        if($ref->implementsInterface(ScannerInterface::class)){
            $this->driver = $class;
            return $this;
        }else{
            throw new Exception("{$class} not a ScannerInterface class");
        }
    }

    function addRule(WatchRule $rule):FileWatcher
    {
        $this->rules[$rule->getPath()] = $rule;
        return $this;
    }

    function getRules():array
    {
        return $this->rules;
    }

    function setOnChange(callable $call):FileWatcher
    {
        $this->onChange = $call;
        return $this;
    }

    function setOnException(callable $call):FileWatcher
    {
        $this->onException = $call;
        return $this;
    }

    /**
     * @return int
     */
    public function getCheckInterval(): int
    {
        return $this->checkInterval;
    }

    /**
     * @param int $checkInterval
     */
    public function setCheckInterval(int $checkInterval): void
    {
        $this->checkInterval = $checkInterval;
    }

    function attachServer(Server $server)
    {
        $config = new Config();
        $config->setArg([
            'onChange'=>$this->onChange,
            'rules'=>$this->rules,
            'driver'=>$this->driver,
            'onException'=>$this->onException,
            'checkInterval'=>$this->checkInterval
        ]);
        $p = new WatcherWorker($config);
        $server->addProcess($p->getProcess());
    }
}