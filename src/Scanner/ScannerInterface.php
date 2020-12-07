<?php


namespace EasySwoole\FileWatcher\Scanner;


use EasySwoole\FileWatcher\WatchRule;

interface ScannerInterface
{
    function __construct(WatchRule $rule);
    function getChangeFiles():array;
}