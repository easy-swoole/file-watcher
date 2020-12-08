# file-watcher

## 热重启
例如在Easyswoole开发模式中，我们希望当有代码变动的时候，实现Server重启，示例代码如下：
```
namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FileWatcher\FileWatcher;
use EasySwoole\FileWatcher\WatchRule;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $watcher = new FileWatcher();
        $rule = new WatchRule(EASYSWOOLE_ROOT."/App");
        $watcher->addRule($rule);
        $watcher->setOnChange(function (){
            Logger::getInstance()->info('file change ,reload!!!');
            ServerManager::getInstance()->getSwooleServer()->reload();
        });
        $watcher->attachServer(ServerManager::getInstance()->getSwooleServer());
    }
}
```
> 注意，reload仅仅针对Worker进程加载的代码有效