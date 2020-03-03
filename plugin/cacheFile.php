<?PHP

namespace plugin;

class cacheFile{

    protected $token_id;
    protected $cache_data;

    public function __construct($expire = 604800)
    {
        $this -> init($expire);
    }

    // 获取对应缓存值
    public function get($key)
    {
        if(array_key_exists($key, $this -> cache_data))
            return $this -> cache_data[$key];
        else
            return false;
    }

    //设置缓存键值
    public function set($key, $value)
    {
        $this -> cache_data[$key] = $value;
        return $this -> saveCacheDate($this -> cache_data, 0);
    }

    // 判断缓存是否存在 $key键
    public function has($key)
    {
        return array_key_exists($key, $this -> cache_data);
    }

    // 删除键值
    public function delete($key)
    {
        unset($this -> cache_data[$key]);
        return $this -> saveCacheDate($this -> cache_data, 0);
    }

    // 清空缓存列表
    public function flush()
    {
        $this -> cache_data = array('CACHEID' => $this -> cache_data['CACHEID'], 'expire' => $this -> cache_data['expire']);
        return $this -> saveCacheDate($this -> cache_data, 0);
    }

    // 删除缓存
    public function remove()
    {
        setcookie("CACHEID", "", time()-1);
        $file_path = kPathCache . $this -> token_id;
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
    protected function init($expire)
    {
        if(isset($_COOKIE['CACHEID']) && $this->checkExpireToken($_COOKIE['CACHEID']))
            $this -> token_id = $_COOKIE['CACHEID'];

        if(!$this -> token_id && isset($_SERVER['HTTP_CACHEID']) && $this->checkExpireToken($_SERVER['HTTP_CACHEID']))
            $this -> token_id = $_SERVER['HTTP_CACHEID'];

        if(!$this -> token_id || !$this -> getCacheDate())
        {
            $this -> token_id = $this -> getToken();
            header("CACHEID: " . $this -> token_id);
            setcookie('CACHEID', $this -> token_id, time() + $expire);
            $this -> saveCacheDate(array('CACHEID' => $this -> token_id, 'expire'=> time() + $expire), $expire);
        }
        $this -> getCacheDate();
    }

    // 读取缓存数据
    protected function getCacheDate()
    {
        $file_path = kPathCache . $this -> token_id;
        if(is_file($file_path))
        {
            @$data = json_decode(file_get_contents($file_path), true);
            if(is_array($data) && isset($data['expire']) && ($data['expire'] > time()))
            {
                $this -> cache_data = $data;
                return true;
            }
        }
        return false;
    }

    // 保存缓存数据
    protected function saveCacheDate($data, $expire)
    {
        $file_path = kPathCache . $this -> token_id;
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
    protected function checkExpireToken($token_id)
    {
        return (preg_match('/^\w{8}-\w{32}-\w{8}$/', $token_id)===1 ? true : false);
    }

    protected function getToken()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()+-';
        $random = $chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)];
        $content = uniqid() . $random . time();
        $time = md5(time());
        return substr($time, 0, 8) . '-' . md5($content) . '-' . substr($time, 8, 8);
    }
}