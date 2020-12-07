<?php


namespace EasySwoole\FileWatcher;


use EasySwoole\Component\Process\Config;
use Swoole\Server;

class FileWatcher
{
    private $rules = [];
    private $onChange;

    function addRule(WatchRule $rule)
    {
        $this->rules[$rule->getPath()] = $rule;
    }

    function setOnChange(callable $call):FileWatcher
    {
        $this->onChange = $call;
        return $this;
    }

    function attachServer(Server $server)
    {
        $config = new Config();
        $config->setArg([
            'onChange'=>$this->onChange,
            'rules'=>$this->rules
        ]);
        $p = new WatcherWorker($config);
        $server->addProcess($p->getProcess());
    }
}