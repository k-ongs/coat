<?PHP
    /*
     * 操作者：bear
     * 说  明：框架核心类
     * 日  期：2019年9月22日
     */

    namespace main;

    class main
    {
        static public function run()
        {
            //new route();
        }

        //加载文件
        static public function load($class)
        {
            $path = kPathPlugin . $class . '.php';
            if(is_file($path))
            {
                include $path;
            }else{
                die('找不到文件: ' . $class . '.php');
            }
        }

        //获取配置文件
        static public function getConfig($config_name, $config_name_value = false)
        {

            $path = kPathConfig . $config_name . '.php';
            if(is_file($path))
            {
                $config = include $path;
                if(array_key_exists($config_name_value, $config))

                    
                return include $path;
            }else{
                die('找不到配置文件: ' . $config_name . '.php');
            }
        }
    }