<?PHP
    // [str] 匹配字母及数字
    // [int] 匹配数字
    return [
        '^test/index/[str]/u[int].html' => 'test/index/$1/id/$2',
        '^test/v1/index/[str]/u[int].html' => 'test\v1/index/$1/id/$2',
    ];