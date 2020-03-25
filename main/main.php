<?PHP
    namespace main;

    class main
    {
        static public function run()
        {
            // 将PHP输出，放入缓冲区
            ob_start();

            // 注册自动加载类
            spl_autoload_register('\main\main::autoload');
            // 捕获Warning、Notice级别错误
            set_error_handler('\main\main::errorHandler');
            // 捕获PHP的错误：Fatal Error、Parse Error等，这个方法是PHP脚本执行结束前最后一个调用的函数
            register_shutdown_function('\main\main::shutdownFunction');
            // 设置默认的异常处理程序
            set_exception_handler('\main\main::exceptionHandler');

            // 设置系统变量
            self::setSystem();

            if(self::getSystem('DEBUG'))
            {
                ini_set("display_errors", "On");
                error_reporting(E_ALL | E_STRICT);
            }else{
                ini_set("display_errors", "Off");
                error_reporting(0);
            }

            $route = new route();
            $route -> routing();
        }

        //自动加载文件
        static public function autoload($class)
        {
            $class = str_replace('\\', "/", $class);
            $path = ROOT . $class . '.php';
            if(is_file($path))
            {
                include($path);
                return true;
            }else{
                return false;
            }
        }

        //加载第三方类
        static public function vendor($class, $action=false)
        {
            $class = str_replace('\\', "/", $class);
            $path = VENDOR . $class . '.php';
            if(is_file($path))
            {
                include($path);
                if($action)
                {
                    return new $action();
                }
                return true;
            }else{
                return false;
            }
        }

        // Warning、Notice级别错误处理
        static public function errorHandler($type, $message, $file, $line)
        {
            die(self::getErrorHtml($message, $file, $line));
        }

        // Fatal Error、Parse Error等错误处理
        static public function shutdownFunction()
        {
            /* error_get_last()
             *   [type]      - 错误类型
             *   [message]   - 错误消息
             *   [file]      - 发生错误所在的文件
             *   [line]      - 发生错误所在的行
             */
            if ($error = error_get_last()) {
                die(self::getErrorHtml($error['message'], $error['file'], $error['line']));
            }
        }

        // 默认的异常处理程序
        static public function exceptionHandler($exception){
            die(self::getErrorHtml($exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTrace()));
        }

        //生成错误信息html代码
        static private function getErrorHtml($message, $file, $line, $trace = array())
        {
            ob_clean();
            if(!self::getSystem('DEBUG'))
            {
                if(self::getSystem('page404') && is_file(self::getSystem('page404')))
                {
                    die(file_get_contents(self::getSystem('page404')));
                }else{
                    die(self::getPage404());
                }
            }
            $content = '';
            $eol_conu = substr_count($file, PHP_EOL);
            for($i=1; $i <= $eol_conu + 1; $i++)
            {
                $content .= $i . '.\A ';
            }
            $style = '<style>.block{margin:10px;background:#FFF;border-radius:4px;border:1px solid #f6f6f6;box-shadow:0 0 2px rgba(0,0,0,.05)}.block>.block-header{padding:8px 10px;border-bottom:1px solid #f6f6f6}.title{font-size:20px;font-weight:bold}.block>.block-body{padding:8px 10px;overflow-x:auto}.quote{padding:10px;font-size:14px;background-color:#f2f2f2;border-left:5px solid #db3a00;border-radius:0 2px 2px 0;margin:10px 0;word-wrap:break-word}.title2{font-size:14px;color:#999}.code {color: #fff;padding:10px 0 10px 52px;font-size: 14px;line-height: 18px;background-color: #1E1E1E;font-family: "Lucida Console", Consolas, Monaco;white-space: pre;word-break: break-all;position: relative;overflow: hidden;overflow-x: auto;margin: 10px 0;}.code::before {content: "'.$content.'";left: 0;top: 10px;width: 44px;bottom: 10px;color:#858585;overflow: hidden;text-align: right;line-height: 18px;padding-right: 6px;position: absolute;display: inline-block;background-color:#1E1E1E;border-right: 1px solid #404040;}</style>';
            $html = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta http-equiv="X-UA-Compatible" content="ie=edge"><title>错误信息</title>' . $style . '</head><body style="background-color:#f1f1f1;"><div class="block"><div class="block-header"><span class="title">系统发生错误</span></div>';
            $html .= '<div class="block-body">';
            $html .= '<div class="title2">错误信息：</div>';
            $html .= '<div class="quote">' . str_replace(ROOT, "/", $message) . '</div>';
            $html .= '<div class="title2">错误位置：</div>';
            if($eol_conu > 1)
            {
                $file = str_replace(ROOT, "/", $file);
                $file = str_replace('<', "&lt;", $file);
                $file = str_replace('var://', "", $file);
                $html .= '<div class="quote">错误位置在如下代码第'.$line.'行</div>';
                $html .= '<div class="code">'.$file.'</div>';
            }else{
                $html .= '<div class="quote">'.$file.'(第'.$line.'行)</div>';
            }
            if(!empty($trace))
            {
                $html .= '<div class="title2">执行流程跟踪</div>';
                foreach ($trace as $v){
                    $trace_info = '';
                    if(isset($v['file'])){
                        $trace_info .= '/' . str_replace(ROOT, "", $v['file']) . '(' . $v['line'] . ')  ';
                    }
                    if(isset($v['class'])){
                        $trace_info .= $v['class'];
                    }
                    if(isset($v['type'])){
                        $trace_info .= $v['type'];
                    }
                    if(isset($v['function'])){
                        $trace_info .=  $v['function'];
                        if(isset($v['args'])){
                            if(is_array($v['args'])){
                                foreach ($v['args'] as $ki => $vi) {
                                    if(!is_int($vi)){
                                        if(is_array($vi))
                                            $vi = json_encode($vi);
                                        if(is_object($vi))
                                            $vi = get_class($vi);
                                        $v['args'][$ki] = '"' . str_replace('"', '\\"', $vi) .'"';
                                    }
                                }
                                // 经过上面的处理数组剩下的元素都是基本类型的了
                                $trace_info .= '(' . implode(',', $v['args']) . ')';
                            }else{
                                $trace_info .= '("' . $v['args'] . '")';
                            }
                        }else{
                            $trace_info .= '()';
                        }
                    }
                    $html .= '<div class="quote">' . str_replace(ROOT, "/", $trace_info) . '</div>';
                }
            }
            $html .= ' </div></div></body></html>';
            return $html;
        }

        //获取配置信息
        static public function getConfig($config_name)
        {
            $path = CONFIG . $config_name . '.php';
            if(is_file($path))
            {
                $config = include $path;
                if(is_array($config)){
                    return $config;
                }
            }
            return false;
        }

        //设置全局变量
        static public function setSystem($name=false, $data=false)
        {
            if(!$name)
            {
                $_SERVER['CONFIG'] = [
                    'DEBUG'=>false, 'ROUTE'=>false, 'VERSION'=>'1.1.2',
                    'MODULE'=>'index', 'CONTROLLER'=>'index', 'ACTION'=>'index'
                ];
                $system = self::getConfig('system');
                if($system)
                {
                    foreach($system as $key => $val)
                    {
                        $_SERVER['CONFIG'][strtoupper($key)] = $val;
                    }
                }
            }else{
                $_SERVER['CONFIG'][strtoupper($name)] = $data;
            }
        }

        //获取全局变量
        static public function getSystem($key)
        {
            if(in_array(strtoupper($key), $_SERVER['CONFIG']))
            {
                return $_SERVER['CONFIG'][strtoupper($key)];
            }
            return false;
        }

        //生成错误信息html代码
        static public function getPage404()
        {
            $html = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta http-equiv="X-UA-Compatible" content="ie=edge"><title>404</title><style>html,body{width: 100%;height: 100%;margin: 0;padding: 0;}</style></head><body><div style="display: table;width: 100%;height: 100%;"><div style="display: table-cell;text-align: center;vertical-align: middle;"><div style="font-size: 100px; font-weight:bold;">404</div><div>抱歉，您所访问的页面不存在，请重新加载!</div></div></div></body></html>';
            return $html;
        }
    }