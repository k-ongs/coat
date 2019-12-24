<?PHP
    //
    // 操作者：bear
    // 说  明：入口文件
    // 日  期：2019年12月21日
    //

    //默认头
    header("Content-type: text/html; charset=utf-8");

    defined('kPathMain') or define('kPathMain', __DIR__ . DIRECTORY_SEPARATOR);  //主要文件路径
    defined('kPathCache') or define('kPathCache', dirname(__DIR__) . '/cache/');  //缓存文件路径
    defined('kPathPublic') or define('kPathPublic', dirname(__DIR__) . '/public/');  //公共文件路径
    defined('kPathPlugin') or define('kPathPlugin', dirname(__DIR__) . '/plugin/');  //插件文件路径
    defined('kPathConfig') or define('kPathConfig', dirname(__DIR__) . '/config/');  //配置文件路径
    defined('kPathProject') or define('kPathProject', dirname(__DIR__) . '/project/');  //项目文件路径

    /**$_SERVER['config'] = include PATH_COM.'config/config.php';   //载入配置文件

    if($_SERVER['config']['BASIC']['debug']){
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
    }else{
        ini_set("display_errors", "Off");
        error_reporting(0);
    }

    include FRAMEWORK . '/main.php';

    spl_autoload_register('\FRAMEWORK\FRAMEWORK::load');

    \FRAMEWORK\FRAMEWORK::run();*/
    include kPathMain . '/main.php';
    spl_autoload_register('\main\main::load');
    \main\main::run();

    \a::user;
?>