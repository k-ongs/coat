<?PHP
    namespace main;

    class route extends main
    {
        private $dividing = '这里是分割线';

        public function __construct()
        {
            $this -> setGlobal('module', MODULE);
            $this -> setGlobal('action', ACTION);
            $this -> setGlobal('controller', CONTROLLER);
        }

        // 路由验证
        private function routeCheck($url)
        {
            // 获取配置文件
            $rule = $this -> getConfig('route');
            if($rule)
            {
                // 对数组按照键名进行降序排序
                krsort($rule);
                foreach ($rule as $key => $value)
                {
                    // 去除字符两端的空格和斜线
                    $key = trim($key, '/ ');
                    $key = str_replace('[int]', '(\d+)', $key);
                    $key = str_replace('[str]', '([\w<\x{4e00}-\x{9fa5}>]{1,})', $key);
                    $key = str_replace('/', '\/' , $key);
                    $value = trim($value, '/ ');
                    if(preg_match('/' . $key . '/u', $url))
                    {
                        $url = preg_replace('/' . $key . '/u', $value, $url);
                        $url = preg_replace_callback('/\[(.*?)\]/', function ($matches) { return str_replace('/', $this -> dividing, $matches[1]); }, $url);
                        return $url;
                    }
                }
            }
            return $url;
        }

        // 设置路由参数
        private function setRouteArgs()
        {
            // 取得当前URL的路径地址
            $request_uri_array = explode('?', urldecode($_SERVER['REQUEST_URI']));
            // 去除多余的斜线
            $request_uri = trim(preg_replace('/(.*?\/)\/{1,}/i', '$1', $request_uri_array[0]), '/');
            // 计算去除后缀的路径长度
            $request_len = strlen($request_uri) - strlen(SUFFIX);
            // 强制使用地址后缀检查
            if($request_uri != '' && FORCE_SUFFIX && substr($request_uri, $request_len) !== SUFFIX)
            {
                throw new \Exception('强制使用地址后缀已开启！');
            }
            // 去除地址后缀
            $request_uri = str_replace(SUFFIX, '', $request_uri);
            // 解析路由规则
            $request_uri = $this -> routeCheck($request_uri);
            $parameter_array = explode('/', $request_uri);
            for($i=0; $i<3; $i++)
            {
                $arr_args = ['module','controller','action'];
                if(!empty($parameter_array[$i]))
                {
                    if($this -> isIntCharCN($parameter_array[$i]))
                    {
                        $this -> setGlobal($arr_args[$i], str_replace($this -> dividing, '\\', $parameter_array[$i]));
                    }
                }

            }
            // 设置GET参数
            if(count($parameter_array) >= 5)
            {
                for($i=3; $i<count($parameter_array); $i+=2)
                {
                    if($i+1 >= count($parameter_array))
                    {
                        break;
                    }
                    $_GET[$parameter_array[$i]] = $parameter_array[$i+1];
                }
            }
        }

        // 加载控制器
        private function loadController()
        {
            $load_path = '\\project\\' . $this -> getGlobal('module') . '\\' . $this -> getGlobal('controller');
            if($this -> getGlobal('module') === false || $this -> getGlobal('controller') === false || $this -> getGlobal('action') === false)
            {
                throw new \Exception('路由模型、控制器或方法不能为空!');
            }

            $load = new $load_path();
            $action = $this -> getGlobal('action');
            if(method_exists($load, $action) && is_callable(array($load , $action)))
            {
                return $load -> $action();
            }else
            {
                throw new \Exception('方法不存在：' . $action);
            }
            
        }

        // 执行路由
        public function routing()
        {
            $this -> setRouteArgs();
            $response = $this -> loadController();
            if(is_string($response))
            {
                header("Content-type: text/html; charset=utf-8");
                die($response);
            }
            if(is_array($response))
            {
                header('content-type:application/json;charset=utf-8');
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }

        private function isIntCharCN($str)
        {
            return (preg_match('/^[\w<\x{4e00}-\x{9fa5}>]{1,}$/u', $str)===1 ? true : false);
        }
    }