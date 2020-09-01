<?PHP
    namespace coat;

    class core
    {
        public static function run()
        {
            // 将输出放入缓冲区
            ob_start();
            // 注册系统处理程序[自动加载、异常处理、错误处理]
            self::registerHandlerFunction();
            // 路由解析
            return self::route();
        }

        // 注册系统自定义处理程序
        private static function registerHandlerFunction()
        {
            // 生成错误信息html代码
            function debugTipsHtml(string $message, string $file, int $line)
            {
                // 设置一个存储html代码的变量
                $content = '';
                // 清空缓冲区数据
                ob_clean();
                // 非调试模式直接退出
                if(!DEBUG) header('HTTP/1.1 500 Internal Server Error') || exit();
                // 获取错误代码行数
                $eol_conu = substr_count($file, PHP_EOL);
                // 设置css代码片段行数
                for($i=1; $i <= $eol_conu + 1; $i++) $content .= $i . '.\A ';
                // 拼接为html代码
                $content = '<html><head><meta charset="UTF-8"><title>错误信息</title><style>.c::before{content:"' . $content . '";left:18px;width:40px;text-align:right;position:absolute;padding-right:4px;background-color:#1E1E1E;border-right:2px solid #404040;}body{background:#f1f1f1;}.m{margin:10px 0px;}.p{padding:8px 10px;}.f{font-size:14px;}.b{background:#FFF;border-radius:4px;}.h{font-size:20px;font-weight:bold;border-bottom:1px solid #f6f6f6}.d{overflow-x:auto}.q{background-color:#f2f2f2;border-left:5px solid #db3a00;border-radius:0 2px 2px 0;word-wrap:break-word}.c{color:#fff;background-color:#1E1E1E;white-space:pre;word-break:break-all;overflow-x:auto;padding-left:50px;}</style></head><body><div class="b m p"><div class="h f p">系统发生错误</div><div class="d m"><div class="f p">错误信息：</div><div class="q p f">' . $message . '</div><div class="f p">错误位置：</div>' . (($eol_conu > 1) ? ('<div class="q p f">错误位置在如下代码第' . $line . '行</div><div class="c f">' . strtr($file, ['<'=>'&lt;', '>'=>'&gt;', 'view://'=>'']) . '</div>') : ('<div class="q p f">' . str_replace('\\', '/', $file) . '(第' . $line . '行)</div>')) . '</div></div></body></html>';
                // 隐藏根目录，防止物理地址暴露
                $content = str_replace(ROOT, "/", $content);
                $content = str_replace(str_replace('/', "\\", ROOT), "/", $content);
                // 输出并结束
                die($content);
            }

            // 注册自动加载
            spl_autoload_register(function (string $class){
                // 反转斜线
                $class = str_replace('\\', "/", $class);
                // 拼接文件路径
                $path = ROOT . $class . ((substr($class, 0, 7) === 'plugin/') ? '.class' : '') . '.php';
                // 判断文件是否存在
                if(is_file($path))
                {
                    // 载入文件
                    include $path;
                }
            });

            // 设置默认的异常处理程序
            set_exception_handler(function ($e){
                debugTipsHtml($e->getMessage(), $e->getFile(), $e->getLine());
            });

            // 捕获Warning、Notice级别错误
            set_error_handler(function (int $type, string $message, string $file, int $line){
                debugTipsHtml($message, $file, $line);
            });

            // 捕获Fatal Error、Parse Error等错误
            register_shutdown_function(function (){
                ($error = error_get_last()) && debugTipsHtml($error['message'], $error['file'], $error['line']);
            });
        }
        
        private static function route(): string
        {
            // 清空缓冲区数据
            ob_clean();
            // 取得当前URL的路径地址
            $request_url = explode('?', urldecode($_SERVER['REQUEST_URI']));
            // 去除多余的斜线
            $request_url = trim(preg_replace('/(.*?\/)\/{1,}?/i', '$1', $request_url[0]), '/');
            // 路由解析
            if(ROUTE){
                $request_url = self::routeParse($request_url);
            }
            // 分割请求url
            $parameter = explode('/', $request_url);
            // 设置模块、控制和方法默认值
            $temp_avgs = ['MODULE' => MODULE, 'CONTROLLER' => CONTROLLER, 'METHOD' => METHOD];
            foreach(array_keys($temp_avgs) as $i => $key)
            {
                // 替换为请求的值
                if(!empty($parameter[$i]))
                {
                    $temp_avgs[$key] = preg_replace('/[^\w\\\]/', '', $parameter[$i]);
                }   
            }
            // 设置GET参数
            for($i=4; $i<count($parameter); $i+=2)
            {
                $_GET[$parameter[$i-1]] = $parameter[$i];
            }

            $action = $temp_avgs['METHOD'];
            $controller = '\\'.basename(APP).'\\' . $temp_avgs['MODULE'] . '\\' . $temp_avgs['CONTROLLER'];
            // 当前控制器路径
            define('__URL__', $controller);
            // 当前模块
            define('__MODULE__', $temp_avgs['MODULE']);
            // 当前控制器
            define('__CONTROLLER__', $temp_avgs['CONTROLLER']);
            // 当前方法
            define('__ACTION__', $action);

            // 加载公共函数
            if(is_file(APP . 'function.php'))
            {
                include(APP . 'function.php');
            }
            // 实例化对象
            $load = new $controller();
            // 调用方法
            if(method_exists($load, $action) && is_callable(array($load , $action)))
            {
                // 调用方法
                $response = $load -> $action();
                // 设置头信息
                if(is_string($response))
                {
                    header("Content-type: text/html; charset=" . CHARSET);
                }else if(is_array($response))
                {
                    header('content-type:application/json; charset=' . CHARSET);
                    // 将数组转为json
                    $response = json_encode($response, JSON_UNESCAPED_UNICODE);
                }
                return $response;
            }

            if(DEBUG) throw new \Exception('Call to undefined method '.$controller.'::' . $action . '()');
        }

        // 路由解析
        private static function routeParse(string $url):string
        {
            $rule = [];
            // 读取路由配置
            $file_path =  APP . 'route.php';
            if(is_file($file_path))
            {
                $rule = include($file_path);
                is_array($rule) || $rule = [];
            }

            if($rule)
            {
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
                        return preg_replace('/' . $key . '/', $value, $url);
                    }
                }
            }
            return $url;
        }
    }