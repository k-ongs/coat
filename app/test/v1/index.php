<?PHP
namespace app\test\v1;

class index
{
    public function get()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : NULL;
        return ['state'=>true, 'msg'=>'当前方法为:' . __METHOD__, 'id'=>$id];
    }
}