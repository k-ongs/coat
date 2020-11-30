<?PHP
    require 'coat.php';

    // 网站目录
    define('__WEB__', str_replace('\\', '/', dirname(__DIR__)) . '/');
    // 应用目录
    define('__APP__', __WEB__ . 'app/');
    // 运行目录
    define('__RUN__', __WEB__ . 'run/');
    // 插件目录
    define('__PLUG__', __WEB__ . 'plug/');
    // 配置目录
    define('__CONFIG__', __WEB__ . 'config/');
    // 缓存目录
    define('__STORAGE__', __WEB__ . 'storage/');

    // 调试模式
    defined('__DEBUG__') or define('__DEBUG__', true);
    // 路由标识符
    defined('__ROUTE__') or define('__ROUTE__', 's');
    // 网页编码
    defined('__CHARSET__') or define('__CHARSET__', 'utf-8');

    // 默认模块
    defined('MODULE') or define('MODULE', 'index');
    // 默认控制器
    defined('CONTROLLER') or define('CONTROLLER', 'index');
    // 默认方法
    defined('METHOD') or define('METHOD', 'index');

    // 初始化核心类
    \coat\coat::run();