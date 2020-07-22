<?PHP

namespace project\api\v1;

class test
{
    public function get()
    {
        return ['state'=>true, 'msg'=>'API版本V1'];
    }
}