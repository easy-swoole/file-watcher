<?php


namespace EasySwoole\FileWatcher\Scanner;


use EasySwoole\FileWatcher\WatchRule;

class Inotify implements ScannerInterface
{
    private $rule;
    private $inotifyResource;

    public function __construct(WatchRule $rule)
    {
        $this->rule = $rule;
        $this->init();
    }

    public function getChangeFiles(): array
    {
        $ret = inotify_read($this->inotifyResource);
        $ret = $ret ? array_column($ret, 'name') : [];
        if(!empty($ret)){
            $this->init();
        }
        return $ret;
    }

    public function __destruct()
    {
        if($this->inotifyResource){
            fclose($this->inotifyResource);
            $this->inotifyResource = null;
        }
    }

    private function init()
    {
        if($this->inotifyResource){
            fclose($this->inotifyResource);
        }
        $this->inotifyResource = inotify_init();
        stream_set_blocking($this->inotifyResource, false);
        $watchItems = $this->rule->scan2Items();
        $fileList = array_merge($watchItems->getFiles(), $watchItems->getPaths());
        foreach ($fileList as $item) {
            inotify_add_watch($this->inotifyResource, $item, IN_CREATE | IN_MODIFY | IN_DELETE | IN_MOVE);
        }
    }
}