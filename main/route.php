<?PHP
    namespace main;

    class route extends main
    {
        private $module;
        private $controller;
        private $action;
        private $load_path;

        public function __construct()
        {
            $this -> module = $this -> getSystem('module');
            $this -> controller = $this -> getSystem('controller');
            $this -> action = $this -> getSystem('action');
        }

        // 路由验证
        private function routeCheck($url)
        {
            $rule = $this -> getConfig('route');
            if($rule)
            {
                krsort($rule);
                foreach ($rule as $key => $value) {
                    $key = trim($key, '/');
                    $value = trim($value, '/');
                    if(preg_match('/^'.str_replace('/','\/', $key).'/', $url))
                    {
                        
                        $url = preg_replace('/'.str_replace('/','\/', $key).'/', $value, $url);
                        $url = preg_replace_callback('/\[(.*?)\]/', function ($matches) { return str_replace('/','\\', $matches[1]); }, $url);
                        return $url;
                    }
                }
            }
            return $url;
        }

        // 设置路由参数
        private function setRouteArgs()
        {
            $request_uri_array = explode('?', urldecode($_SERVER['REQUEST_URI']));
            $request_uri = trim(preg_replace('/(.*?\/)\/{1,}/i', '$1', $request_uri_array[0]), '/');
            $request_uri = str_replace('.html', '', $request_uri);

            if($this -> getSystem('route'))
                $request_uri = $this -> routeCheck($request_uri);
            $parameter_array = explode('/', $request_uri);

            if(!empty($parameter_array[0])){
                if($this -> isIntCharCN($parameter_array[0]))
                    $this -> module = $parameter_array[0];
            }

            if(!empty($parameter_array[1])){
                if($this -> isIntCharCN($parameter_array[1]))
                    $this -> controller = $parameter_array[1];
            }

            if(!empty($parameter_array[2])){
                if($this -> isIntCharCN($parameter_array[2]))
                    $this -> action = $parameter_array[2];
            }

            if(count($parameter_array) >= 5){
                for($i=3;$i<count($parameter_array);$i+=2){
                    if($i+1 >= count($parameter_array)){
                        break;
                    }
                    $_GET[$parameter_array[$i]] = $parameter_array[$i+1];
                }
            }
        }

        // 加载控制器
        private function loadController(){
            $load_path = '\\project\\' . $this -> module . '\\' . $this -> controller;
            if($this -> module == '' || $this -> controller == '' || $this -> action == '')
            {
                throw new \Exception('路由出现严重错误!');
            }
            $load = new $load_path();
            $action = $this -> action;
            $this -> setSystem('module', $this -> module);
            $this -> setSystem('controller', $this -> controller);
            $this -> setSystem('action', $this -> action);

            if(method_exists($load, $action) && is_callable(array($load ,  $action))){
                return $load -> $action();
            }else{
                throw new \Exception('方法不存在：' . $action);
            }
        }

        // 执行路由
        public function routing(){
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
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }

        private function isIntCharCN($str){
            return (preg_match('/^[\w<\x{4e00}-\x{9fa5}>]{1,}$/u', $str)===1 ? true : false);
        }
    }