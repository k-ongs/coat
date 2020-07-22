<?PHP
    namespace main;

    //版本号
    defined('VERSION') or define('VERSION', '1.1.3');

    //程序根目录路径
    defined('ROOT') or define('ROOT', dirname(__DIR__) . '/');
    //主要文件路径
    defined('MAIN') or define('MAIN', ROOT . 'main/');
    //缓存文件路径
    defined('CACHE') or define('CACHE', ROOT . 'cache/');
    //公共文件路径
    defined('PUBLIC') or define('PUBLIC', ROOT . 'public/');
    //插件文件路径
    defined('PLUGIN') or define('PLUGIN', ROOT . 'plugin/');
    //第三方插件路径
    defined('VENDOR') or define('VENDOR', ROOT . 'vendor/');
    //配置文件路径
    defined('CONFIG') or define('CONFIG', ROOT . 'config/');
    //项目文件路径
    defined('PROJECT') or define('PROJECT', ROOT . 'project/');

    //调试模式
    defined('DEBUG') or define('DEBUG', true);
    //路由地址后缀
    defined('SUFFIX') or define('SUFFIX', '.html');
    //默认模块
    defined('MODULE') or define('MODULE', 'index');
    //默认方法
    defined('ACTION') or define('ACTION', 'index');
    //默认控制器
    defined('CONTROLLER') or define('CONTROLLER', 'index');
    //是否强制使用路由地址后缀
    defined('FORCE_SUFFIX') or define('FORCE_SUFFIX', true);

    // 加载框架主文件
    include(MAIN . 'main.php');

    \main\main::run();