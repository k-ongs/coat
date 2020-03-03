<?PHP

namespace plugin;

class Validator{

    static public function isIntChar($str){
		return (preg_match('/^\w{1,}$/', $str)===1 ? true : false);
	}
}