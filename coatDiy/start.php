<?PHP
    //
    // 操作者：bear
    // 说  明：入口文件
    // 日  期：2019年12月21日
    //

    //默认头
    header("Content-type: text/html; charset=utf-8");

    //网站根目录路径
    defined('kPathRoot') or define('kPathRoot', dirname(__DIR__) . '/');
    //主要文件路径
    defined('kPathMain') or define('kPathMain', kPathRoot . 'coatDiy/');
    //缓存文件路径
    defined('kPathCache') or define('kPathCache', kPathRoot . 'cache/');
    //公共文件路径
    defined('kPathPublic') or define('kPathPublic', kPathRoot . 'public/');
    //插件文件路径
    defined('kPathPlugin') or define('kPathPlugin', kPathRoot . 'plugin/');
    //配置文件路径
    defined('kPathConfig') or define('kPathConfig', kPathRoot . 'config/');
    //项目文件路径
    defined('kPathProject') or define('kPathProject', kPathRoot . 'project/');

    //载入系统配置文件
    $_SERVER['config']['system'] = include kPathConfig . 'system.php';

    if($_SERVER['config']['system']['debug']){
         ini_set("display_errors", "On");
         error_reporting(E_ALL | E_STRICT);
    }else{
        ini_set("display_errors", "Off");
        error_reporting(0);
    }

    include kPathMain . 'main.php';

    //自动注册、错误处理
    set_error_handler('\coatDiy\main::errorHandler');
    set_exception_handler("\coatDiy\main::exceptionHandler");
    spl_autoload_register('\coatDiy\main::load');

    \coatDiy\main::run();