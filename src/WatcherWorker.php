<?php


namespace EasySwoole\FileWatcher;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;
use EasySwoole\FileWatcher\Scanner\FileScanner;
use EasySwoole\FileWatcher\Scanner\ScannerInterface;

class WatcherWorker extends AbstractProcess
{
    private $driverList = [];
    private $onChange;

    protected function run($arg)
    {
        $this->onChange = $arg['onChange'];
        /** @var WatchRule $rule */
        foreach ($arg['rules'] as $rule){
            $driver = new FileScanner($rule);
            $this->driverList[] = [
                'driver'=>$driver,
                'rule'=>$rule
            ];
        }

        Timer::getInstance()->loop(1000,function (){
            foreach ($this->driverList as $item){
                /** @var ScannerInterface $driver */
                $driver = $item['driver'];
                $list = $driver->getChangeFiles();
                if(!empty($list) && is_callable($this->onChange)){
                    call_user_func($this->onChange,$list,$item['rule']);
                }
            }
        });
    }
}