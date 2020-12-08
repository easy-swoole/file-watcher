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
        $curr = $this->getStatus();

        //对比curr 和$this->currentStatus
        $ret = array_diff_assoc(
            array_column($curr, 'mtime','inode'),
            array_column($this->currentStatus,'mtime','inode')
        );
        if ($ret) {
            $this->currentStatus = $curr;
        }

        $inodes = array_keys($ret);

        $result = [];
        foreach ($curr as $key => $value) {
            if (in_array($value['inode'],$inodes)) {
                $result[] = $value['file'];
            }
        }

        return $result;
    }

    private function getStatus(): array
    {
        $watchItems = $this->rule->scan2Items();
        $fileList = array_merge($watchItems->getFiles(), $watchItems->getPaths());
        $res = [];
        foreach ($fileList as $file) {
            $info = new \SplFileInfo($file);
            $inode = $info->getInode();
            $mtime = $info->getMTime();
            $res[md5($file)] = [
                'file' => $file,
                'inode' => $inode,
                'mtime' => $mtime
            ];
        }
        return $res;
    }
}