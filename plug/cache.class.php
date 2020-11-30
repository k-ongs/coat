<?PHP
    namespace plug;

    class cache{

        private $_token_id;
        private $_cache_data;

        public function __construct($expire = 604800)
        {
            $this -> init($expire);
        }

        // 获取对应缓存值
        public function get($key)
        {
            if(array_key_exists($key, $this -> _cache_data))
                return $this -> _cache_data[$key];
            else
                return false;
        }

        //设置缓存键值
        public function set($key, $value)
        {
            $this -> _cache_data[$key] = $value;
            return $this -> saveCacheDate($this -> _cache_data, 0);
        }

        // 判断缓存是否存在 $key键
        public function has($key)
        {
            return array_key_exists($key, $this -> _cache_data);
        }

        // 删除键值
        public function delete($key)
        {
            unset($this -> _cache_data[$key]);
            return $this -> saveCacheDate($this -> _cache_data, 0);
        }

        // 清空缓存列表
        public function flush()
        {
            $this -> _cache_data = array('CACHEID' => $this -> _cache_data['CACHEID'], 'expire' => $this -> _cache_data['expire']);
            return $this -> saveCacheDate($this -> _cache_data, 0);
        }

        // 删除缓存
        public function remove()
        {
            setcookie("CACHEID", "", time()-1);
            $file_path = CACHE . 'cache/' . $this -> _token_id;
            if(is_file($file_path))
            {
                $url = iconv('utf-8', 'gbk', $file_path);
                //linux
                if(PATH_SEPARATOR == ':'){
                    if(unlink($file_path)) return true;
                }else{
                    if(unlink($url)) return true;
                }
            }
            return false;
        }

        // 初始化
        private function init($expire)
        {
            if(isset($_COOKIE['CACHEID']) && $this->checkExpireToken($_COOKIE['CACHEID']))
                $this -> _token_id = $_COOKIE['CACHEID'];

            if(!$this -> _token_id && isset($_SERVER['HTTP_CACHEID']) && $this->checkExpireToken($_SERVER['HTTP_CACHEID']))
                $this -> _token_id = $_SERVER['HTTP_CACHEID'];

            if(!$this -> _token_id || !$this -> getCacheDate())
            {
                $this -> _token_id = $this -> getToken();
                header("CACHEID: " . $this -> _token_id);
                setcookie('CACHEID', $this -> _token_id, time() + $expire);
                $this -> saveCacheDate(array('CACHEID' => $this -> _token_id, 'expire'=> time() + $expire), $expire);
            }
            $this -> getCacheDate();
        }

        // 读取缓存数据
        private function getCacheDate()
        {
            $file_path = CACHE . 'cache/' . $this -> _token_id;
            if(is_file($file_path))
            {
                @$data = json_decode(file_get_contents($file_path), true);
                if(is_array($data) && isset($data['expire']) && ($data['expire'] > time()))
                {
                    $this -> _cache_data = $data;
                    return true;
                }
            }
            return false;
        }

        // 保存缓存数据
        private function saveCacheDate($data, $expire)
        {
            if(!file_exists(CACHE . 'cache/'))
            {
                if(!mkdir(CACHE . 'cache/', 0777, true))
                {
                    return false;
                }
            }
            $file_path = CACHE . 'cache/' . $this -> _token_id;
            $data_json = json_encode($data,JSON_UNESCAPED_UNICODE);
            if(file_put_contents($file_path, $data_json, LOCK_EX) !== false){
                if($expire)
                    touch($file_path, time() + $expire);
                return true;
            }else{
                return false;
            }
        }

        // 验证token是否有效
        private function checkExpireToken($token_id)
        {
            return (preg_match('/^\w{8}-\w{32}-\w{8}$/', $token_id)===1 ? true : false);
        }

        private function getToken()
        {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()+-';
            $random = $chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)];
            $content = uniqid() . $random . time();
            $time = md5(time());
            return substr($time, 0, 8) . '-' . md5($content) . '-' . substr($time, 8, 8);
        }
    }