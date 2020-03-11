<?PHP

namespace plugin;
use main\main;

class View
{
    public $view_html;
    public $parameter;

    public function display($route = '', $analysis = false)
    {
        $this -> analysis($route, $analysis);
    }

    public function parameter($name, $vaule)
    {
        $this -> parameter[$name] = $vaule;
    }
    
    public function convert()
    {
        $this -> convertVariable();
        $this -> convertFor();
        $this -> convertForeach();
        $this -> convertIf();
    }

    // 解析所有变量
    // {{$i}}
    public function convertVariable()
    {
        $this -> view_html = preg_replace("/{{([\$][A-Za-z_]{1}[\w\[\]\$]*?)}}/i",'<?PHP echo $1; ?>', $this -> view_html);
        return $this;
    }

    // 解析所有for语句
    // <for start="0" end="1" step="1" name="i">
    public function convertFor()
    {
        preg_match_all('/<for (.*?)>/', $this -> view_html, $result_for_array);
        if(!empty($result_for_array))
        {
            $result_for_array[0] = array_unique($result_for_array[0]);
            foreach($result_for_array[0] as $result_for_val)
            {
                $start = '';
                $step =  '1';
                $name = 'i';
                $end = '';
                $args_array = ['start', 'step', 'name', 'end'];

                foreach($args_array as $args_array_val)
                {
                    preg_match('/'.$args_array_val.'=["\'](.*?)["\']/i', $result_for_val, $result_arg);
                    if($result_arg)
                    {
                        if($args_array_val != 'name')
                        {
                            $tem = strval(intval($result_arg[1]));
                            if($tem != $result_arg[1])
                                break 2;
                        }
                        $$args_array_val = $result_arg[1];
                    }
                }
                $html_for = '<?php for($'.$name.'=0; $'.$name.($start < $end ? '<' : '>') . $end . '; $'.$name. ($start < $end ? '+=' : '-=') . $step .') { ?>';
                $this -> view_html = str_replace($result_for_val, $html_for, $this -> view_html);
            }
            $this -> view_html = str_replace('</for>', '<?php } ?>', $this -> view_html);
        }
        return $this;
    }

    // 解析所有foreach语句
    // <foreach name="arr" key="key" value="value"></foreach>
    public function convertForeach()
    {
        
        preg_match_all('/<foreach (.*?)>/', $this -> view_html, $result_for_array);
        if(!empty($result_for_array))
        {
            $result_for_array[0] = array_unique($result_for_array[0]);
            foreach($result_for_array[0] as $result_for_val)
            {
                $name = '';
                $key =  'key';
                $value = 'value';
                $args_array = ['name', 'key', 'value'];

                foreach($args_array as $args_array_val)
                {
                    preg_match('/'.$args_array_val.'=["\'](.*?)["\']/i', $result_for_val, $result_arg);
                    if($result_arg)
                    {
                        if($result_arg[1] == '')
                            break 2;
                        $$args_array_val = $result_arg[1];
                    }
                }
                $html_for = '<?php foreach($'.$name.' as $'.$key.' => $' . $value . ') { ?>';
                $this -> view_html = str_replace($result_for_val, $html_for, $this -> view_html);
            }
            $this -> view_html = str_replace('</foreach>', '<?php } ?>', $this -> view_html);
        }
        return $this;
    }
    // 解析所有if语句
    // <if val="条件"></if>
    public function convertIf()
    {
        
        preg_match_all('/<if (.*?)>/', $this -> view_html, $result_for_array);
        if(!empty($result_for_array))
        {
            $result_for_array[0] = array_unique($result_for_array[0]);
            foreach($result_for_array[0] as $result_for_val)
            {
                $val = 'true';
                preg_match('/val=["\'](.*?)["\']/i', $result_for_val, $result_arg);
                if($result_arg)
                {
                    $val = $result_arg[1];
                }
                $html_for = '<?php if('.$val.') { ?>';
                $this -> view_html = str_replace($result_for_val, $html_for, $this -> view_html);
            }
            $this -> view_html = str_replace('<else>', '<?php }else{ ?>', $this -> view_html);
            $this -> view_html = str_replace('</if>', '<?php } ?>', $this -> view_html);
        }
        return $this;
    }

    // 获取视图路径
    public function getViewPath($route = '')
    {
        $route_array = array_merge(array_filter(explode('/', $route)));
        switch(count($route_array))
        {
            case 0:
                $view_path = main::getSysVar('module') . '/view/' . main::getSysVar('controller') . '/' . main::getSysVar('action');
                break;
            case 1:
                $view_path = main::getSysVar('module') . '/view/' . main::getSysVar('controller') . '/' . $route_array[0];
                break;
            case 2:
                $view_path = main::getSysVar('module') . '/view/' . $route_array[0] . '/' . $route_array[1];
                break;
            default:
                $view_path = $route_array[0] . '/view/' . $route_array[1] . '/' . $route_array[2];
        }

        $view_path .= main::getSysConfig('suffix');
        return $view_path;
    }

    // 解析视图
    public function analysis($route, $view_analysis = false)
    {
        foreach(($this -> parameter) as $name => $vaule)
        {
            $$name = $vaule;
        }
        unset($name);
        unset($vaule);
        // $this -> setViewHtml($route);
        $view_path = $this -> getViewPath($route);
        if(is_file(kPathProject . $view_path))
        {
            if($view_analysis || main::getSysConfig('view_analysis'))
            {
                $this -> view_html = file_get_contents(kPathProject . $view_path);
                $this -> convert();
                require('var://' . $this -> view_html);
            }else{
                require(kPathProject . $view_path);
            }
        }else{
            throw new \Exception('找不到视图文件: ' . $view_path);
        }
    }
}