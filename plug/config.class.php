<?PHP
    namespace plug;

    class config
    {
        private $_data = [];
        private $_path = '';

        // 构造方法
        function __construct($name)
        {
            $this->_path = __CONFIG__ . $name . '.config.php';
            if(is_file($this->_path))
            {
                $config = require($this->_path);
                is_array($config) && $this->_data = $config;
            }
        }

        // 判断配置文件是否包含某一项
        public function has($name)
        {
            return isset($this->_data[$name]);
        }

        // 获取配置值
        public function get($name)
        {
            if(isset($this->_data[$name]))
            {
                return $this->_data[$name];
            }
            return false;
        }

        // 设置配置值
        public function set($name, $value)
        {
            if(is_int($name) || is_string($name))
            {
                $this->_data[$name] = $value;
                return true;
            }
            return false;
        }

        // 保存配置信息
        public function save()
        {
            if(!is_dir(__CONFIG__))
            {
                mkdir(__CONFIG__, 0777, true);
            }
            $current = file_put_contents($this->_path, "<?PHP " . PHP_EOL . 'return ' . var_export($this->_data, true) . ';' . PHP_EOL, LOCK_EX);
            return ($current === false) ? false : true;            
        }

        // 清空配置信息
        public function clear()
        {
            $this->_data = [];
            return $this;
        }

        // 获取全部配置信息
        public function getAll()
        {
            return $this->_data;
        }

        // 设置全部配置信息
        public function setAll($data)
        {
            if(is_array($data))
            {
                $this->_data = $data;
                return true;
            }
            return false;
        }
    }