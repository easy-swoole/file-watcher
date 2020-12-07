<?php


namespace EasySwoole\FileWatcher\Scanner;


use EasySwoole\FileWatcher\WatchRule;

class FileScanner implements ScannerInterface
{
    private $currentStatus = [];
    private $rule;
    public function __construct(WatchRule $rule)
    {
        $this->rule = $rule;
        $this->currentStatus = $this->getStatus();
    }

    function getChangeFiles(): array
    {
        $ret = [];
        //对比curr 和$this->currentStatus
        $curr = $this->getStatus();
        //对比后
        $this->currentStatus = $curr;
        return $ret;
    }

    private function getStatus():array
    {
        $temp = $this->rule->scan2Items()->getFiles();
        $res = [];
        foreach ($temp as $file){
            $info = new \SplFileInfo($file);
            $inode = $info->getInode();
            $mtime = $info->getMTime();
            $res[md5($file)] = [
                'file'=>$file,
                'inode'=>$inode,
                'mtime'=>$mtime
            ];
        }
        return $res;
    }
}