<?PHP
    namespace coat;

    class coat
    {
        // 入口函数
        public static function run()
        {
            // 将输出放入缓冲区
            ob_start();
            // 注册系统处理程序[自动加载、异常处理、错误处理]
            self::registerHandlerFunction();
            // 路由
            self::route();
        }

        // 注册系统自定义处理程序
        private static function registerHandlerFunction()
        {
            // 生成错误信息html代码
            function debugTips($message, $file, $line)
            {
                // 清空缓冲区数据
                ob_clean();
                // 非调试模式直接退出
                if(!__DEBUG__)
                {
                    header('HTTP/1.1 404 Not Found');
                    header("status: 404 Not Found");
                    die();
                }
                // 输出错误信息
                echo '错误信息：' . $message . '<br>错误行数： 第' . $line . '行<br>';
                echo '错误位置：<br>' . strtr($file, ['<'=>'&lt;', '>'=>'&gt;', 'view://'=>'']);
                die();
            }

            // 注册自动加载
            spl_autoload_register(function ($class){
                // 反转斜线
                $class = str_replace('\\', "/", $class);
                // 拼接文件路径
                $path = __WEB__ . $class . (substr($class, 0, 5) === 'plug/' ? '.class' : '') . '.php';

                // 判断文件是否存在
                if(is_file($path))
                {
                    // 载入文件
                    require $path;
                }
            });

            // 设置默认的异常处理程序
            set_exception_handler(function ($e){
                debugTips($e->getMessage(), $e->getFile(), $e->getLine());
            });

            // 捕获Warning、Notice级别错误
            set_error_handler(function ($type, $message, $file, $line){
                debugTips($message, $file, $line);
            });

            // 捕获Fatal Error、Parse Error等错误
            register_shutdown_function(function (){
                ($e = error_get_last()) && debugTips($e['message'], $e['file'], $e['line']);
            });
        }

        private static function route()
        {
            // 取得当前URL的路径地址
            $request_url = isset($_GET[__ROUTE__]) ? [$_GET[__ROUTE__]] : explode('?', urldecode($_SERVER['REQUEST_URI']));
            // 去除多余的斜线
            $request_url = trim(preg_replace('/\/{2,}/i', '/', $request_url[0]), '/');
            // 路由解析
            $request_url = self::routeParse($request_url);
            // 分割请求url
            $parameter = explode('/', $request_url);

            // 设置模块、控制和方法默认值
            $temp_avgs = ['MODULE' => MODULE, 'CONTROLLER' => CONTROLLER, 'METHOD' => METHOD];
            foreach(array_keys($temp_avgs) as $i => $key)
            {
                // 替换为请求的值
                if(!empty($parameter[$i]) && preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9\\\]+$/u', $parameter[$i]))
                {
                    $temp_avgs[$key] = $parameter[$i];
                }   
            }
            // 设置GET参数
            for($i=4; $i<count($parameter); $i+=2)
            {
                $_GET[$parameter[$i-1]] = $parameter[$i];
            }
            $action = $temp_avgs['METHOD'];
            $controller = '\\'.basename(__APP__).'\\' . $temp_avgs['MODULE'] . '\\' . $temp_avgs['CONTROLLER'];
            // 当前控制器路径
            define('__URL__', $controller);
            // 当前模块
            define('__MODULE__', $temp_avgs['MODULE']);
            // 当前控制器
            define('__CONTROLLER__', $temp_avgs['CONTROLLER']);
            // 当前方法
            define('__ACTION__', $action);

            // 加载公共函数
            if(is_file(__APP__ . 'function.php'))
            {
                require(__APP__ . 'function.php');
            }
            // 实例化对象
            $path = __WEB__ . $controller . '.php';
            // 判断文件是否存在
            if(!is_file($path) && __DEBUG__)
            {
                die('错误信息：无法找到控制器“'.$controller.'”');
            }
            $load = new $controller();

            // 调用方法
            if((!method_exists($load, $action) || !is_callable(array($load , $action))) && __DEBUG__)
            {
                die('错误信息：无法在“'.$controller.'”中找到方法“' . $action . '()”');
            }

            // 调用方法
            $response = $load -> $action();
            // 设置头信息
            if(is_string($response))
            {
                header("Content-type: text/html; charset=" . __CHARSET__);
            }else if(is_array($response))
            {
                header('content-type:application/json; charset=' . __CHARSET__);
                // 将数组转为json
                $response = json_encode($response, JSON_UNESCAPED_UNICODE);
            }
            // 清空缓冲区数据
            ob_clean();
            die($response);
        }   

        // 路由解析
        private static function routeParse($url)
        {
            $rule = [];

            // 读取路由配置
            $file_path =  __APP__ . 'route.php';
            if(is_file($file_path))
            {
                $rule = require($file_path);
                is_array($rule) || $rule = [];
            }

            // 对数组按照键名进行降序排序
            krsort($rule);
            foreach ($rule as $key => $value)
            {
                // 去除字符两端的空格和斜线
                $key = trim($key, '/ ');
                $value = str_replace('\\' , '\\\\', trim($value, '/ '));
                $key = strtr($key, ['[int]'=>'(\d+)', '[str]'=>'(\w+)', '/'=>'\/']);
                // 验证是否有匹配的字符
                if(preg_match('/' . $key . '/', $url))
                {
                    // 替换匹配的字符
                    if($key == '^') $url = '/' . $url;
                    return preg_replace('/' . $key . '/', $value, $url);
                }
            }

            return $url;
        }
    }