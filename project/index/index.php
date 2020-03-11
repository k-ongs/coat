<?PHP

namespace project\index;
use plugin\View;

class index extends View
{
    public function index()
    {
        $this -> parameter('test', [1,2,3,4,5]);
        // preg_match('/var[\s]*=[\s]*(\'|")(.*?)\1/i', 'var = "$_GET[123]"', $result_arg);
        $this -> display();
        // print_r($result_arg);
    }

    public function test()
    {
        // $cacheFile = new cacheFile();
        // $cacheFile -> set('user','test');
        // return $cacheFile -> get('user');
        // // return $_SERVER;
    }
}