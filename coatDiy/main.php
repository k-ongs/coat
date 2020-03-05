<?PHP
    /*
     * 操作者：bear
     * 说  明：框架核心类
     * 日  期：2019年9月22日
     */

    namespace coatDiy;

    class main
    {
        static public function run()
        {
            $route = new route();
            $route -> routing();
        }

        //加载文件
        static public function load($class)
        {
            $class = str_replace('\\', "/", $class);
            $path = kPathRoot . $class . '.php';
            if(is_file($path))
            {
                include $path;
            }else{
                throw new \Exception('找不到文件: ' . $class . '.php');
            }
        }

        //错误处理
        static public function errorHandler($errno, $errstr, $errfile, $errline)
        {
            if($_SERVER['config']['system']['debug'])
                die(\coatDiy\main::getErrorHtml($errfile, $errline, $errstr));
            else{
                if(isset($_SERVER['config']['system']['page404']) && is_file($_SERVER['config']['system']['page404']))
                {
                    die(file_get_contents($_SERVER['config']['system']['page404']));
                }else{
                    die(\coatDiy\main::getPage404());
                }
            }
        }

        //异常处理
        static public function exceptionHandler($e){
            if($_SERVER['config']['system']['debug']){
                $html = '';
                if(!empty($e->getTrace()))
                {
                    $html .= '<div class="title2">执行流程跟踪</div>';
                    foreach ($e->getTrace() as $v){
                        $trace_info = '';
                        if(isset($v['file'])){
                            $trace_info .= '/' . str_replace(kPathRoot, "", $v['file']) . '(' . $v['line'] . ')  ';
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
                        $html .= '<div class="quote">' . str_replace(kPathRoot, "/", $trace_info) . '</div>';
                    }
                }
                die(\coatDiy\main::getErrorHtml($e->getFile(), $e->getLine(), $e->getMessage(), $html));
            }else{
                if(isset($_SERVER['config']['system']['page404']) && is_file($_SERVER['config']['system']['page404']))
                {
                    die(file_get_contents($_SERVER['config']['system']['page404']));
                }else{
                    die(\coatDiy\main::getPage404());
                }
            }
        }

        //生成错误信息html代码
        static public function getErrorHtml($file, $line, $str, $other='')
        {
            $html = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta http-equiv="X-UA-Compatible" content="ie=edge"><title>错误信息</title><style>.block{margin:10px;background:#FFF;border-radius:4px;border:1px solid #f6f6f6;box-shadow:0 0 2px rgba(0,0,0,.05)}.block>.block-header{padding:8px 10px;border-bottom:1px solid #f6f6f6}.title{font-size:20px;font-weight:bold}.block>.block-body{padding:8px 10px;overflow-x:auto}.quote{padding:10px;font-size:14px;background-color:#f2f2f2;border-left:5px solid #db3a00;border-radius:0 2px 2px 0;margin:10px 0;word-wrap:break-word}.title2{font-size:14px;color:#999}</style></head><body style="background-color:#f1f1f1;"><div class="block"><div class="block-header">';
            $html .= '<span class="title">系统发生错误</span></div><div class="block-body"><div class="title2">错误位置：</div>';
            $html .= '<div class="quote">'.str_replace(kPathRoot, "/", $file) . '(' . $line . ')</div><div class="title2">错误信息：</div>';
            $html .= '<div class="quote">' . str_replace(kPathRoot, "/", $str) . '</div>';
            $html .= $other . '</div></div></body></html>';
            return $html;
        }

        //生成错误信息html代码
        static public function getPage404()
        {
            $html = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta http-equiv="X-UA-Compatible" content="ie=edge"><title>404</title><style>html,body{width: 100%;height: 100%;margin: 0;padding: 0;}</style></head><body><div style="display: table;width: 100%;height: 100%;"><div style="display: table-cell;text-align: center;vertical-align: middle;"><div style="font-size: 100px; font-weight:bold;">404</div><div>抱歉，您所访问的页面不存在，请重新加载!</div></div></div></body></html>';
            return $html;
        }
        
        //获取配置文件
        static public function getConfig($config_name)
        {
            $path = kPathConfig . $config_name . '.php';
            if(is_file($path))
            {
                $config = include $path;
                if(is_array($config)){
                    return $config;
                }
            }
            return false;
        }

        //获取配置文件
        static public function getSysConfig($key)
        {
            
            if(empty($_SERVER['config']['system']) || empty($_SERVER['config']['system'][$key]))
                return false;
            return $_SERVER['config']['system'][$key];
        }
    }