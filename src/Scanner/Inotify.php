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
        $this->inotifyResource = inotify_init();
        stream_set_blocking($this->inotifyResource, false);
    }

    public function getChangeFiles(): array
    {
        $watchItems = $this->rule->scan2Items();
        $fileList = array_merge($watchItems->getFiles(), $watchItems->getPaths());

        foreach ($fileList as $item) {
            inotify_add_watch($this->inotifyResource, $item, IN_CREATE | IN_MODIFY | IN_DELETE | IN_MOVE);
        }

        $ret = inotify_read($this->inotifyResource);
        return $ret ? array_column($ret, 'name') : [];
    }

    public function __destruct()
    {
        fclose($this->inotifyResource);
    }
}