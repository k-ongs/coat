<?PHP
    require 'core.php';

    // 版本号
    define('VERSION', '1.2.0');

    // 根目录
    define('ROOT', str_replace('\\', '/', dirname(__DIR__)) . '/');
    // 应用目录
    define('APP', ROOT . 'app/');
    // 公共目录
    define('PUBLIC', ROOT . 'public/');
    // 插件目录
    define('PLUGIN', ROOT . 'plugin/');
    // 配置目录
    define('CONFIG', ROOT . 'config/');
    // 缓存目录
    define('STORAGE', ROOT . 'storage/');

    // 调试模式
    defined('DEBUG') or define('DEBUG', true);
    // 路由解析
    defined('ROUTE') or define('ROUTE', true);
    // 网页编码
    defined('CHARSET') or define('CHARSET', 'utf-8');
    // 默认模块
    defined('MODULE') or define('MODULE', 'index');
    // 默认控制器
    defined('CONTROLLER') or define('CONTROLLER', 'index');
    // 默认方法
    defined('METHOD') or define('METHOD', 'index');

    // 初始化核心类
    echo \coat\core::run();