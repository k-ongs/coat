<?PHP
namespace app\index;

use plug\view;

class index extends view
{
    public function index()
    {
        $this -> args('arr', [1,2,4,5,6,7,8]);
        $this -> display();
        // $id = isset($_GET['id']) ? $_GET['id'] : NULL;
        // return ['state'=>true, 'msg'=>'当前方法为:' . __METHOD__, 'id'=> $id];
    }
}