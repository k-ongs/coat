<?PHP
namespace plugin;

class XDeode{
    static public function encode($num, $length = 8){
        $strbase = str_split("AMBZCJXEQS");
        $num = intval($num);
        $rtn = '';
        foreach(str_split($num) as $v)
        {
            $rtn .= $strbase[$v];
        }

        $ext_len = $length - strlen($rtn);

        for($i=0; $i < $ext_len; $i++)
        {
            if(rand(0, 9) > 5){
                $rtn .= rand(0, 9);
            }else{
                $rtn = rand(0, 9) . $rtn;
            }
        }
        return $rtn;
    }
    //解码 
    static public function decode($code){
        $rtn = "";
        $strbase = "AMBZCJXEQS";
        $code = preg_replace("/\\d+/",'', $code);
        if($code)
        {
            $code_data = str_split(strtoupper($code));
            foreach ($code_data as $v) {
                $rtn .= strpos($strbase, $v);
            }
        }
        return $rtn;
    }
}

?>