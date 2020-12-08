<?php


namespace EasySwoole\FileWatcher;


use EasySwoole\Utility\File;

class WatchRule
{
    private $path;

    private $scanType = self::SCAN_TYPE_SUFFIX_MATCH;

    const SCAN_TYPE_SUFFIX_MATCH = 1;
    const SCAN_TYPE_IGNORE_SUFFIX = 2;

    private $ignoreFiles = [];
    private $ignorePaths = [];
    private $suffix = [];

    function __construct(string $path)
    {
        $this->path = $path;
        return $this;
    }

    function getPath()
    {
        return $this->path;
    }

    function setIgnoreFiles(array $files):WatchRule
    {
        $this->ignoreFiles = $files;
        return $this;
    }

    function setIgnorePaths(array $paths):WatchRule
    {
        $this->ignorePaths = $paths;
        return $this;
    }

    function setSuffix(array $suffixes):WatchRule
    {
        $this->suffix = $suffixes;
        return $this;
    }

    function setType(int $type):WatchRule
    {
        $this->scanType = $type;
        return $this;
    }

    function scan2Items():WatchItems
    {
        $item = new WatchItems();
        $files = File::scanDirectory($this->path);

        $watchFiles  = array_diff($files['files'],$this->ignoreFiles);
        $watchPaths = array_diff($files['dirs'],$this->ignorePaths);
        foreach ($watchFiles as $index => $file) {
            if (!$this->suffix) {
                break;
            }

            $ret = in_array(pathinfo($file,PATHINFO_EXTENSION), $this->suffix);
            if ($this->scanType === static::SCAN_TYPE_SUFFIX_MATCH && !$ret) {
                unset($watchFiles[$index]);
                continue;
            }

            if ($this->scanType === static::SCAN_TYPE_IGNORE_SUFFIX && $ret) {
                unset($watchFiles[$index]);
            }
        }

        $item->setFiles($watchFiles);
        $item->setPaths($watchPaths);

        return $item;
    }
}