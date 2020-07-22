<?PHP

namespace project\api\v2;

class test
{
    public function get()
    {
        return ['state'=>true, 'msg'=>'API版本V2'];
    }
}