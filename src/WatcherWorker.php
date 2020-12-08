<?php


namespace EasySwoole\FileWatcher;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;
use EasySwoole\FileWatcher\Scanner\ScannerInterface;

class WatcherWorker extends AbstractProcess
{
    private $driverList = [];
    private $onChange;
    private $onException;

    protected function run($arg)
    {
        $this->onChange = $arg['onChange'];
        $this->onException = $arg['onException'];
        $driverClass = $arg['driver'];
        /** @var WatchRule $rule */
        foreach ($arg['rules'] as $rule){
            $driver = new $driverClass($rule);
            $this->driverList[] = [
                'driver'=>$driver,
                'rule'=>$rule
            ];
        }

        Timer::getInstance()->loop($arg['checkInterval'],function (){
            foreach ($this->driverList as $item){
                /** @var ScannerInterface $driver */
                $driver = $item['driver'];
                $list = $driver->getChangeFiles();
                if(!empty($list) && is_callable($this->onChange)){
                    try{
                        call_user_func($this->onChange,$list,$item['rule']);
                    }catch (\Throwable $exception){
                        if(is_callable($this->onException)){
                            call_user_func($this->onException,$exception);
                        }else{
                            throw $exception;
                        }
                    }
                }
            }
        });
    }
}