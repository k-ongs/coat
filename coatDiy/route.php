<?PHP
    /*
     * 操作者：bear
     * 说  明：路由功能
     * 日  期：2019年9月22日
     */

    namespace coatDiy;
    use \plugin\Validator;

    class route extends main
    {
        private $module;
        private $controller;
        private $action;

        public function __construct()
        {
            $this -> module = $_SERVER['config']['system']['module'];
            $this -> controller = $_SERVER['config']['system']['controller'];
            $this -> action = $_SERVER['config']['system']['action'];
        }

        private function routeCheck($url)
        {
            $rule = $this -> getConfig('route');
            if($rule)
            {
                foreach ($rule as $key => $value) {
                    $key = trim($key, '/');
                    $value = trim($value, '/');
                    if($key == substr($url, 0, strlen($key)))
                        if(count(explode('/', $value)) < 4)
                            return str_replace($key, $value, $url);
                }
            }
            return $url;
        }

        private function setRouteArgs()
        {
            $request_uri_array = explode('?', urldecode($_SERVER['REQUEST_URI']));
            $request_uri = trim(preg_replace('/(.*?\/)\/{1,}/i', '$1', $request_uri_array[0]), '/');

            if($this -> getSysConfig('route'))
                $request_uri = $this -> routeCheck($request_uri);
            $parameter_array = explode('/', $request_uri);

            if(!empty($parameter_array[0])){
                if(Validator::isIntCharCN($parameter_array[0]))
                    $this -> module = $parameter_array[0];
            }

            if(!empty($parameter_array[1])){
                if(Validator::isIntCharCN($parameter_array[1]))
                    $this -> controller = $parameter_array[1];
            }

            if(!empty($parameter_array[2])){
                if(Validator::isIntCharCN($parameter_array[2]))
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

        private function loadController(){
            $load_path = '\\project\\' . $this -> module . '\\' . $this -> controller;
            if($this -> module == '' || $this -> controller == '' || $this -> action == '')
            {
                throw new \Exception('路由出现严重错误!');
            }
            $load = new $load_path();
            $action = $this -> action;
            $_SERVER['system']['module'] = $this -> module;
            $_SERVER['system']['controller'] = $this -> controller;
            $_SERVER['system']['action'] = $this -> action;
            if(method_exists($load, $action) && is_callable(array($load ,  $action))){
                return $load -> $action();
            }else{
                throw new \Exception('方法不存在：' . $action);
            }
        }

        public function routing(){
            $this -> setRouteArgs();
            $response = $this -> loadController();

            if(is_string($response))
            {
                die($response);
            }
            if(is_array($response))
            {
                header('content-type:application/json;charset=utf-8');
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }
    }