<?PHP
    namespace plug;

    class view
    {
        // 输出缓存
        private $_cache = '';
        // 参数列表
        private $_parameter = [];

        // 获取标签的属性值
        private function getAttribute($html, $name, $exp = '.')
        {
            if(preg_match('/'.$name.'[ ]*=[ ]*([\'"])((?:(?!\1)'.$exp.')+?)\1/', $html, $match))
            {
                return ($match[2] == '' ? false : $match[2]);
            }
            return false;
        }

        // 解析for标签
        private function convertForTags()
        {
            $tag_end = '</for>';
            if(preg_match_all('/<for .+?>/', $this -> _cache, $result))
            {
                foreach(array_reverse($result[0]) as $web_tag)
                {
                    $start_index = strripos($this -> _cache, $web_tag);
                    $html_length = strpos(substr($this -> _cache, $start_index), $tag_end);
                    $handle_html = substr($this -> _cache, $start_index, $html_length + strlen($tag_end));

                    $web_tag_arg_end = $this -> getAttribute($web_tag, 'end', '[-\d]');
                    $web_tag_arg_name = $this -> getAttribute($web_tag, 'name', '\w');
                    $web_tag_arg_step = $this -> getAttribute($web_tag, 'step', '\d');
                    $web_tag_arg_start = $this -> getAttribute($web_tag, 'start', '[-\d]');

                    if($web_tag_arg_end !== false && $web_tag_arg_name !== false && $web_tag_arg_start !== false)
                    {
                        $web_tag_arg_end = intval($web_tag_arg_end);
                        $web_tag_arg_step = intval($web_tag_arg_step);
                        $web_tag_arg_step = $web_tag_arg_step == 0 ? 1 : $web_tag_arg_step;
                        $web_tag_arg_start = intval($web_tag_arg_start);
                        $web_tag_arg_symbol = $web_tag_arg_end > $web_tag_arg_start ? '<' : '>';
                        $web_tag_arg_operation = $web_tag_arg_end > $web_tag_arg_start ? '+' : '-';

                        $web_tag_convert = '<?PHP for($'.$web_tag_arg_name.'='.$web_tag_arg_start.'; $'. $web_tag_arg_name . $web_tag_arg_symbol . $web_tag_arg_end . '; $'.$web_tag_arg_name.$web_tag_arg_operation.'='.$web_tag_arg_step.'){ ?>';
                        
                        $handle_html_temp = str_replace($tag_end, '<?PHP } ?>', $handle_html);
                        $handle_html_temp = str_replace($web_tag,$web_tag_convert, $handle_html_temp);
                        $this -> _cache = str_replace($handle_html, $handle_html_temp, $this -> _cache);
                    }else{
                        $this -> _cache = str_replace($handle_html, '<!--解析失败-->', $this -> _cache);
                    }
                }
            }
        }

        // 解析if标签
        private function convertIfTags()
        {
            $tag_end = '</if>';
            if(preg_match_all('/<if .+?>/', $this -> _cache, $result))
            {
                foreach(array_reverse($result[0]) as $web_tag)
                {
                    $start_index = strripos($this -> _cache, $web_tag);
                    $html_length = strpos(substr($this -> _cache, $start_index), $tag_end);
                    $handle_html = substr($this -> _cache, $start_index, $html_length + strlen($tag_end));

                    $web_tag_arg_value = $this -> getAttribute($web_tag, 'value');
                    if($web_tag_arg_value !== false)
                    {
                        $web_tag_convert = '<?PHP if('.$web_tag_arg_value.'){ ?>';
                        $handle_html_temp = str_replace($tag_end, '<?PHP } ?>', $handle_html);
                        if(preg_match_all('/<elif .+?>/', $handle_html, $result2))
                        {
                            foreach(array_reverse($result2[0]) as $web_tag2)
                            {   
                                $web_tag_arg_value2 = $this -> getAttribute($web_tag2, 'value');
                                if($web_tag_arg_value2 === false) $web_tag_arg_value2 = 'false';
                                $handle_html_temp = str_replace($web_tag2, '<?PHP }elseif('. $web_tag_arg_value2 . '){ ?>', $handle_html_temp);
                            }
                        }
                        $handle_html_temp = str_replace('<else>', '<?PHP }else{ ?>', $handle_html_temp);
                        $handle_html_temp = str_replace($web_tag,$web_tag_convert, $handle_html_temp);
                        $this -> _cache = str_replace($handle_html, $handle_html_temp, $this -> _cache);
                    }else{
                        $this -> _cache = str_replace($handle_html, '<!--解析失败-->', $this -> _cache);
                    }
                }
            }
        }

        // 解析foreach标签
        private function convertForeachTags()
        {
            $tag_end = '</foreach>';
            if(preg_match_all('/<foreach .+?>/', $this -> _cache, $result))
            {
                foreach(array_reverse($result[0]) as $web_tag)
                {
                    $start_index = strripos($this -> _cache, $web_tag);
                    $html_length = strpos(substr($this -> _cache, $start_index), $tag_end);
                    $handle_html = substr($this -> _cache, $start_index, $html_length + strlen($tag_end));

                    $web_tag_arg_key = $this -> getAttribute($web_tag, 'key', '\w');
                    $web_tag_arg_name = $this -> getAttribute($web_tag, 'name');
                    $web_tag_arg_value = $this -> getAttribute($web_tag, 'value', '\w');

                    if($web_tag_arg_key !== false && $web_tag_arg_name !== false && $web_tag_arg_value !== false)
                    {

                        $web_tag_convert = '<?PHP foreach('.$web_tag_arg_name.' as $'.$web_tag_arg_key.' => $'.$web_tag_arg_value.'){ ?>';
                        $handle_html_temp = str_replace($tag_end, '<?PHP } ?>', $handle_html);
                        $handle_html_temp = str_replace('<else>', '<?PHP } if(empty('.$web_tag_arg_name.')){ ?>', $handle_html_temp);
                        $handle_html_temp = str_replace($web_tag,$web_tag_convert, $handle_html_temp);
                        $this -> _cache = str_replace($handle_html, $handle_html_temp, $this -> _cache);
                    }else{
                        $this -> _cache = str_replace($handle_html, '<!--解析失败-->', $this -> _cache);
                    }
                }
            }
        }

        // 解析所有变量
        private function convert()
        {
            // 去除没有用的注释
            $this -> _cache = preg_replace('/<!--.*?-->/is' , '' , $this -> _cache);

            // 解析标签
            $this -> convertForTags();
            $this -> convertIfTags();
            $this -> convertForeachTags();

            // 解析变量
            if(preg_match_all('/{{([\w.\$\*\/\+\- \[\]\"\':\(\)]*?)}}/', $this -> _cache, $result))
            {
                foreach($result[1] as $match)
                {
                    $this -> _cache = str_replace('{{'.$match.'}}', '<?PHP echo '. $match .';?>', $this -> _cache);
                }
            }

            // 去除的空行
            $this -> _cache = preg_replace("/(\r\n|\n|\r|\t){2,}/i", PHP_EOL, $this -> _cache);
          
        }

        // 解析视图
        public function fetch($_view_temp_route = '')
        {
            if(!in_array('view', stream_get_wrappers()))
            {
                stream_wrapper_register("view", "\plug\VariableStream");
            }

            $_view_temp_route_array = array_merge(array_filter(explode('/', $_view_temp_route)));
            switch(count($_view_temp_route_array))
            {
                case 0:
                    $_view_temp_route_path = __MODULE__ . '/' . __CONTROLLER__ . '/' . __ACTION__;
                    break;
                case 1:
                    $_view_temp_route_path = __MODULE__ . '/' . __CONTROLLER__ . '/' . $_view_temp_route_array[0];
                    break;
                case 2:
                    $_view_temp_route_path = __MODULE__ . '/' . $_view_temp_route_array[0] . '/' . $_view_temp_route_array[1];
                    break;
                default:
                    $_view_temp_route_path = $_view_temp_route_array[0] . '/' . $_view_temp_route_array[1] . '/' . $_view_temp_route_array[2];
            }

            $this -> view_path = __WEB__ .'view/' . str_replace('\\', '/', $_view_temp_route_path) . '.html';

            unset($_view_temp_route);
            unset($_view_temp_route_path);
            unset($_view_temp_route_array);

            // 解析参数成为局部变量
            foreach(($this -> _parameter) as $_view_temp_name => $_view_temp_vaule)
            {
                if(is_string($_view_temp_name))
                {
                    $$_view_temp_name = $_view_temp_vaule;
                }
            }
            unset($_view_temp_name);
            unset($_view_temp_vaule);

            if(is_file($this -> view_path))
            {
                $this -> _cache = file_get_contents($this -> view_path);
                $this -> convert();
                ob_clean();
                require('view://' . $this -> _cache);
                $this -> _cache = ob_get_clean();

                return $this -> _cache;
            }else{
                throw new \Exception('找不到视图文件: ' . $this -> view_path);
            }
        }

        public function display(string $route = '')
        {
            header("Content-type:text/html; charset=" . __CHARSET__);
            die($this -> fetch($route));
        }

        // 设置参数
        public function args(string $name, $vaule)
        {
            $this -> _parameter[$name] = $vaule;
        }

    }

    class VariableStream {
        private $string;
        private $position;
    
        public function stream_open($path, $mode, $options, &$opened_path) {
            $this -> string = str_replace('view://', '', $path);
            $this -> position = 0;
            return true;
        }
        public function stream_read($count) {
            $ret = substr($this->string, $this->position, $count);
            $this->position += strlen($ret);
            return $ret;
        }
        public function stream_eof() {
            return $this -> position >= strlen($this -> string);
        }
        public function stream_stat() {}
        public function stream_cast() {}
    }