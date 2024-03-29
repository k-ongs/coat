## 框架结构说明
├—
├—coat           # 框架目录
├   ├— coat.php             # 核心文件
├   └— main.php             # 框架入口文件
├—plug            # 插件目录
├    └— name.class.php      # 插件文件
├—app               # 应用目录
├    ├— module              # 模块
├    ├   └— controller.php          # 控制器
├    ├       └— function method()          #方法
├    ├— route.php           # 路由配置
├    └— function.php        # 公共函数
├—run            # 公共目录
├   ├— .htaccess            # Apache伪静态配置
├   ├— .rewrite.conf        # Nginx伪静态配置
└   └— index.php            # 程序入口文件

## 代码规范

1、通用命名规则
    函数命名, 变量命名, 文件命名要有描述性; 少用缩写。
    尽可能使用描述性的命名, 别心疼空间, 毕竟相比之下让代码易于新读者理解更重要。
    不要用只有项目开发者能理解的缩写, 也不要通过砍掉几个字母来缩写单词。

2、文件命名
    文件名要全部小写, 可以包含下划线 (_) 或连字符 (-), 依照项目的约定。如果没有约定, 那么 “_” 更好。

3、变量命名
    变量 (包括函数参数) 和数据成员名一律小写, 单词之间用下划线连接。类的成员变量以下划线开头。

4、常量命名
    声明为 const 的变量，或在程序运行期间其值始终保持不变的, 命名时以 “k” 开头, 大小写混合。声明为 define 的变量，使用全大写。
        const kDaysInAWeek = 7;

5、函数命名
    常规函数使用大小写混合, 取值和设值函数则要求与变量名匹配。
    MyExcitingFunction(), MyExcitingMethod(), my_exciting_member_variable(), set_my_exciting_member_variable()

6、命名空间命名
    命名空间以小写字母命名。最高级命名空间的名字取决于项目名称。
    要注意避免嵌套命名空间的名字之间和常见的顶级命名空间的名字之间发生冲突。
    顶级命名空间的名称应当是项目名或者是该命名空间中的代码所属的团队的名字。
    命名空间中的代码, 应当存放于和命名空间的名字匹配的文件夹或其子文件夹中。
    注意 不使用缩写作为名称 的规则同样适用于命名空间。
    命名空间中的代码极少需要涉及命名空间的名称, 因此没有必要在命名空间中使用缩写。

7、所有系统变量、常量均为大写，以“__”开头“__”结束。

8、项目插件命名均为：插件名.class.php 格式，配置文件统一放入config文件夹里面，命名为：插件名.config.php，第三方类放入vendor文件夹中，使用main::vendor('类名')调用。

9、需满足PSR-0规范即：
    1) 命名空间必须与绝对路径一致
    2) 类名首字母必须大写
    3) 除去入口文件外，其他“.php”必须只有一个类
    4) php类文件必须自动载入，不采用include等
    5) 单一入口

10、路由规则
    使用正则表达，[str]匹配整数、字母、下划线和中文，[int]匹配整数，默认替换全部，例如：
    
    模块/控制器/方法/参数名/参数值/参数名/参数值

    1) 设置默认为index模块，设置后访问其他模块必须添加路由
        {
            "^": "index",
            "admin": "admin"
        }
    2) 替换所有test为index
        {
            "test": "index"
        }
    3) 替换模块test为模块index
        {
            "^test": "index"
        }
    3) 使用[str]和[int]
        {
            "^api/[str]/[str]/[str]/[int]": "api/$1\$2/$3/id/$4"
        }
        访问url"/api/v1/test/get/12" 解析后得到 "api/v1\test/get/id/12"