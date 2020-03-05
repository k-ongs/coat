<?PHP

namespace plugin;

class Validator{

    static public function custom($pattern, $str){
		return (preg_match($pattern, $str)===1 ? true : false);
	}

	static public function isIntChar($str){
		return (preg_match('/^\w{1,}$/', $str)===1 ? true : false);
	}
	static public function isIntCharCN($str){
		return (preg_match('/^[\w\x00-\xff]{1,}$/', $str)===1 ? true : false);
	}
}