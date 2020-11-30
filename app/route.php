<?PHP
    // [str] 匹配字母及数字
    // [int] 匹配数字
    return [
        '^' => 'index',
        '^[str]' => 'index\$1',
        '^index/index/[str]/u[int].html' => 'index/index/$1/id/$2',
        '^index/v1/index/[str]/u[int].html' => 'index\v1/index/$1/id/$2',
    ];