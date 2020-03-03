<?PHP

namespace project\index;
use plugin\cacheFile;

class index
{
    public function index()
    {
        print_r($_GET);
    }

    public function test()
    {
        $cacheFile = new cacheFile();
        $cacheFile -> set('user','test');
        return $cacheFile -> get('user');
        // return $_SERVER;
    }
}