<?PHP

namespace plugin;
use main\main;

class View
{
    // 输出缓存
    private $html = '';
    // 配置信息
    private $config = [];
    // 参数列表
    private $parameter = [];

    public function display($route = '', $analysis = false)
    {
        header("Content-type:text/html;charset=utf-8");
        echo $this -> fetch($route, $analysis);
    }

    public function convert()
    {
        $this -> convertVariable();
        $this -> convertIf();
        $this -> convertForeach();
        $this -> convertFor();
        
    }

    // 设置参数
    public function parameter($name, $vaule)
    {
        $this -> parameter[$name] = $vaule;
    }

    // 解析所有变量
    // {{$i}}
    private function convertVariable()
    {
        $this -> html = preg_replace("/{{([\$][A-Za-z_]{1}[\w\[\]\$\'\"]*?)}}/i",'<?PHP echo $1; ?>', $this -> html);
        return $this;
    }

    // 解析所有if语句
    // <if val="条件"></if>
    private function convertIf()
    {
        while($row = preg_match_all('/<if[^>]*>((?!<if).)*?<\/if>/is', $this -> html, $result_for_array))
        {
            $result_for_array = array_unique($result_for_array[0]);
            foreach($result_for_array as $result_for_val)
            {
                $val = '';
                preg_match('/val[\s]*=[\s]*(\'|")(.*?)\1/i', $result_for_val, $result_arg);
                if(!empty($result_arg[2]))
                {
                    $val = $result_arg[2];
                    $html_for = '<?php if('.$val.') { ?>';
                    $html = preg_replace('/<if[^>]*>/', $html_for, $result_for_val);
                    $html = str_replace('<else>', '<?php }else{ ?>', $html);
                    $html = str_replace('</if>', '<?php } ?>', $html);
                    $this -> html = str_replace($result_for_val, $html, $this -> html);
                }else{
                    $this -> html = str_replace($result_for_val, '', $this -> html);
                }
            }
        }
        return $this;
    }

    // 解析所有foreach语句
    // <foreach name="arr" key="key" value="value"></foreach>
    private function convertForeach()
    {
        while($row = preg_match_all('/<foreach[^>]*>((?!<foreach).)*?<\/foreach>/is', $this -> html, $result_for_array))
        {
            $result_for_array = array_unique($result_for_array[0]);
            foreach($result_for_array as $result_for_val)
            {
                $name = '';
                $key =  'key';
                $value = 'value';

                foreach(['name', 'key', 'value'] as $args_array_val)
                {
                    preg_match('/'.$args_array_val.'[\s]*=[\s]*(\'|")(.*?)\1/i', $result_for_val, $result_arg);
                    if(!empty($result_arg[2]))
                    {
                        $$args_array_val = $result_arg[2];
                    }
                }

                if($name != '')
                {
                    $html_for = '<?php foreach($'.$name.' as $'.$key.' => $' . $value . ') { ?>';
                    $html = preg_replace('/<foreach[^>]*>/', $html_for, $result_for_val);
                    $html = str_replace('</foreach>', '<?php } ?>', $html);
                    $this -> html = str_replace($result_for_val, $html, $this -> html);
                }else{
                    $this -> html = str_replace($result_for_val, '', $this -> html);
                }
            }
        }
        return $this;
    }

    // 解析所有for语句
    // <for start="0" end="1" step="1" name="i">
    private function convertFor()
    {
        while($row = preg_match_all('/<for[^>]*>((?!<for ).)*?<\/for>/is', $this -> html, $result_for_array))
        {
            $result_for_array = array_unique($result_for_array[0]);
            foreach($result_for_array as $result_for_val)
            {
                $end = '';
                $start = '';
                $name = 'i';
                $step =  '1';

                foreach(['end', 'start', 'name', 'step'] as $args_array_val)
                {
                    preg_match('/'.$args_array_val.'[\s]*=[\s]*(\'|")(.*?)\1/i', $result_for_val, $result_arg);
                    if(!empty($result_arg[2]))
                    {
                        $$args_array_val = $result_arg[2];
                    }
                }
                $end = intval($end);
                $start = intval($start);
                if($end != $start)
                {
                    $html_for = '<?php for($'.$name.'=0; $'.$name.($start < $end ? '<' : '>') . $end . '; $'.$name. ($start < $end ? '+=' : '-=') . $step .') { ?>';
                    $html = preg_replace('/<for[^>]*>/', $html_for, $result_for_val);
                    $html = str_replace('</for>', '<?php } ?>', $html);
                    $this -> html = str_replace($result_for_val, $html, $this -> html);
                }else{
                    $this -> html = str_replace($result_for_val, '', $this -> html);
                }
            }
        }
        return $this;
    }

    // 获取视图参数
    private function getConfig()
    {
        if(empty($this -> config))
        {
            $this -> config = main::getConfig('view');
            if(empty($this -> config))
            {
                $this -> config =[ 'analysis' => true, 'suffix' => '.html', 'path'=>'view'];
            }
        }
    }

    // 获取视图路径
    public function getPath($route = '')
    {
        $this -> getConfig();
        $route_array = array_merge(array_filter(explode('/', $route)));
        switch(count($route_array))
        {
            case 0:
                $view_path = main::getSysVar('module') . '/' . main::getSysVar('controller') . '/' . main::getSysVar('action');
                break;
            case 1:
                $view_path = main::getSysVar('module') . '/' . main::getSysVar('controller') . '/' . $route_array[0];
                break;
            case 2:
                $view_path = main::getSysVar('module') . '/' . $route_array[0] . '/' . $route_array[1];
                break;
            default:
                $view_path = $route_array[0] . '/' . $route_array[1] . '/' . $route_array[2];
        }

        $view_path .=  $this -> config['suffix'];
        $view_path = trim($this -> config['path'], '/') .'/' . $view_path;
        return $view_path;
    }

    // 解析视图
    public function fetch($route = '', $analysis = false)
    {
        $this -> getConfig();

        // 解析参数成为局部变量
        foreach(($this -> parameter) as $name => $vaule)
        {
            if(is_string($name))
            {
                $$name = $vaule;
            }
        }
        unset($name);
        unset($vaule);

        $view_path = $this -> getPath($route);

        if(is_file(kPathRoot . $view_path))
        {
            ob_start();
            $this -> html = file_get_contents(kPathRoot . $view_path);
            if($analysis || $this -> config['analysis'])
            {
                $this -> convert();
            }
            require('var://' . $this -> html);
            $this -> html = ob_get_clean();
        }else{
            throw new \Exception('找不到视图文件: ' . $view_path);
        }

        return $this -> html;
    }
}