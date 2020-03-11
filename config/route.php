<?PHP
    //路由配置文件
    return [
        'index' => 'index/index',
        'api/admin/test' => 'api/[admin/test]',
        '(admin)/(api/\d{1,4}-\d{1,2}-\d{1,2}/\w{1,})' => '$1/[$2]',
    ];