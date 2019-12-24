<?PHP
    /*
     * 操作者：bear
     * 说  明：框架核心类
     * 日  期：2019年9月22日
     */

    namespace main;

    class route
    {
        public $controller;
        public $method;

        public function __construct()
        {
            $controller = $_SERVER['config']['BASIC']['controller'];
            $method = $_SERVER['config']['BASIC']['method'];
            $requestUrl = substr(urldecode($_SERVER['REQUEST_URI']),1);
            $urlEnd = strpos($requestUrl,'?');
            if($urlEnd)
                $requestUrl = substr($requestUrl,0, ($requestUrl[$urlEnd-1] == '/' ? $urlEnd-1 : $urlEnd));
            $parameterArr = explode('/',$requestUrl);

            if(!empty($parameterArr[0]) && $parameterArr[0] === 'api' )
            {
                $controller_path = 'Application\api\\';
                array_shift($parameterArr);
            }else
                $controller_path = 'Application\controller\\';
            if(!empty($parameterArr[0]) && filter::isIntChar($parameterArr[0])){
                $controller = $parameterArr[0];
                $_SERVER['config']['BASIC']['controller'] = $controller;
            }
            if(count($parameterArr) > 1 && !empty($parameterArr[1]) && filter::isIntChar($parameterArr[1])){
                $method = $parameterArr[1];
                $_SERVER['config']['BASIC']['method'] = $method;
            }

            $controller = $controller_path . $controller;

            if(count($parameterArr) >= 4){
                for($i=2;$i<count($parameterArr);$i+=2){
                    if($i+1 >= count($parameterArr)){
                        break;
                    }
                    if(filter::isIntChar($parameterArr[$i]) && filter::isIntChar($parameterArr[$i+1])){
                        $_GET[$parameterArr[$i]] = $parameterArr[$i+1];
                    }
                }
            }

            $load = new $controller();

            if(method_exists($load,$method) && is_callable(array($load ,  $method))){
                FRAMEWORK::echoJson($load -> $method(), JSON_UNESCAPED_UNICODE);     //实例化方法
            }else{
                if($_SERVER['config']['BASIC']['debug']){
                    echo '方法不存在：' . $method;
                }else{
                    pageSkip::Error404();
                }
            }
        }
    }