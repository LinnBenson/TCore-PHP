# TCore PHP
> 1.0.4
> 方便您继续使用 PHP 完成您的开发

```
// 安装
composer require linnbenson/tcore-php

// 如果您将此组件用作网站系统，请运行以下命令
chmod +x vendor/bin/tcore-install && vendor/bin/tcore-install
// 强制刷新所有: vendor/bin/tcore-install all
```

### 全局函数 => Helper/Common.php
- 变量检查：是否为 json
  - `is_json( [mixed]:变量 )`
  - return [boolean]:判断结果
- 变量检查：是否为 UUID
  - `is_uuid( [mixed]:变量 )`
  - return [boolean]:判断结果
- 生成 UUID
  - `uuid()`
  - return [string]:UUID
- 路径保护
  - `inFolder( [string]:目录或文件路径, [number]|0777:创建权限 )`
  - return [string]:传入的路径
- 删除目录
  - `deleteDir( [string]:目录路径 )`
  - return [boolean]:删除结果
- 复制目录
  - `copyDir( [string]:源目录路径, [string]:目标路径 )`
  - return [boolean]:复制结果
- 强制转为字符串
  - `toString( [mixed]:转换内容 )`
  - return [string]:转换后的内容
- 时间值转换
  - `toDate( [number|string]|null:转换内容 )`
  - 转换内容为 null 则输出当前时间
  - return [string]:格式化时间
- 参数序列化
  - `toValue( [mixed]:序列化值, [boolean]:是否为保存模式 )`
  - return [mixed]:序列化或反序列化后的参数
- 字符串转数组
  - 转换类似 'a:1|b:true|c:null' 格式为数组
  - `toArray( [string]:转换字符串 )`
  - return [array]数组
- 判断内容是否以指定内容开始
  - `startWith( [string]:内容, [string]:开始的参数 )`
  - return [boolean]:判断结果
- 判断内容是否以指定内容结束
  - `endWith( [string]:内容, [string]:结束的参数 )`
  - return [boolean]:判断结果
- 加密一段内容
  - `encrypt( [string]:加密文本, [string]|null:加密密钥 )`
  - return [string|null]:加密后的内容
- 解密一段内容
  - `encrypt( [string]:解密文本, [string]|null:解密密钥 )`
  - return [string|null]:解密后的内容
- 哈希一个参数
  - `h( [string]:传入的内容 )`
  - return [string|null]:返回哈希后的字符串或 null
- 调试参数
  - `dd( [mixed]:传入的值, [bool]|true:是否退出程序 )`
  - return void
- 判断是否为公开方法
  - `isPublic( [object]:对象, [string]:方法名称 )`
  - return [boolean]:判断结果
- 渲染视图模板
  - `view( [string|array]:模板路径, [array]|[]:模板数据 )`
  - return [string]:渲染结果

### 核心驱动器 => TCore\Bootstrap
- 驱动安装状态
  - [boolean]|false Bootstrap::$status
- 存储器目录
  - [string]|storage/ Bootstrap::$storage
- 驱动器版本号
  - [string] Bootstrap::$version
- [站点] 注册驱动
  - `Bootstrap::register( [function]|null:回调函数 )`
  - return [mixed]:回调结果或 null
- 初始化程序
  - `Bootstrap::init( [array]|[]:配置信息 )`
  - 配置数组中包含: debug, timezone
  - return true
- [站点] 插件权限介入
  - `Bootstrap::permission( [string]:权限名称, [mixed]|null:传递参数, [string]|null:回传方式, ...[mixed]:附加参数 )`
  - return [mixed]:传递参数
- 记录日志
  - `Bootstrap::log( [string]:日志名称, [string|object]日志信息, [string]|null:日志标题 )`
  - return [boolean]:日志记录结果
- 缓存处理
  - `Bootstrap::cache( [thread|file]:缓存方式, [string]:缓存名称, [function]:缓存内容 )`
  - return [mixed]:缓存值

### 便捷工具集 => TCore\Helper\Tool
- 随机数生成器
  - `Tool::rand( [number]:生成长度, [all|number|letter]|all:随机生成类型 )`
  - return [string]:随机内容
- 配置文件覆盖
  - `Tool::coverConfig( [string]:文件路径, [array]:配置信息 )`
  - return [boolean]:覆盖结果

### 请求构造器 => TCore\Handler\Request
- 预请求代码
  - [int]|200 $this->code
- 允许的请求类型
  - [array]|[ 'Http', 'Cli', 'Websocket' ] $this->type
- 请求类型
  - [string] $this->type
- 请求用户
  - [object|null] $this->user
- 请求对象属性
  - [string] $this->id 请求 ID
  - [string] $this->lang 请求语言
  - [string] $this->method 请求方法
  - [string] $this->target 请求目标
  - [string] $this->category 请求类别
  - [string] $this->source 请求来源
  - [array] $this->header Header 参数
  - [array] $this->get GET 参数
  - [array] $this->post POST 参数
  - [array] $this->cookie Cookie 参数
  - [array] $this->file 上传文件参数
  - [string] $this->ip 请求 IP 地址
  - [array] $this->share 请求共享数据
  - save( [string]:键名, [mixed]:键值 ) 保存数据到 Cookie
  - return( [Request]:请求对象, [mixed]:返回数据, [array]:响应头 ) 自定义接口返回数据格式
#### 方法说明
- 初始化请求
  - `new Request( [array]:请求数据 )`
  - return void
- 初始化请求
  - `Request->init( [array]|null:请求数据 )`
  - return void
- 翻译文本
  - `Request->t( [string]:文本键, [array]:替换数据 )`
  - return [string]:语言包内容
- 接口返回
  - `Request->echo( [boolean|int]:状态, [mixed]|null:返回数据, [int]|null:响应代码, [array]:响应头 )`
  - return [string]:返回数据


### 路由构造器 => TCore\Handler\Router
- 注册路由
  - `Router::register( [strng]:注册类型, [array|function]:路由列表或函数, [string]|null:路由分类 )`
  - return [boolean]:注册结果
- 搜索路由
  - `Router::search( [Request]:请求对象, [array]:路由数据, [array]|[]:附加参数 )`
  - return [mixed]:路由结果
- 路由错误处理
  - `Router::error( [Request]:请求对象, [string]:错误信息, [int]:错误代码 )`
  - return [mixed]:错误处理结果
- 使用控制器
  - `Router::useController( [string|array]:控制器, [Request]:请求对象, ...[mixed]:附加参数 )`
  - return [mixed]:控制器返回结果
- 添加路由
  - `Router::add( [string]:路由路径, [string]|ANY:路由方法 )`
  - return [object|null]:路由结果

#### API 异常错误 => TCore\Exception\ApiException
- 错误代码
  - [int] $this->code
- 错误信息
  - [mixed] $this->message
- 构造错误
  - `new ApiException( [mixed]:错误信息, [int]|400:错误代码 )`
  - return void

#### 写入日志的异常错误 => TCore\Exception\LogException
- 错误信息
  - [mixed] $this->message
- 构造错误
  - `new LogException( [mixed]:错误信息 )`
  - return void

#### 第三方库引用声明
- vlucas/phpdotenv
- blocktrail/cryptojs-aes-php

#### [站点] 通用函数
- 批量引用文件
  - `import( [string|array]:需要引用的文件, [boolean]|true:是否为系统文件 )`
  - return [mixed]:返回引用的结果
- ENV 变量
  - `env( [string]:键名, [mixed]|null:默认值 )`
  - return [mixed]:键值
- Config 配置
  - `config( [string]:键名, [mixed]|null:默认值 )`
  - return [mixed]:键值
- 插件使用
  - `plugin( [string]:插件名称 )`
  - return [object|null]:插件对象

#### [站点] 插件渲染器 TCore\Slots\Plugin
- 插件名称
  - [string] $this->name
- 插件根目录
  - [string] $this->path
- 插件版本号
  - [string] $this->version
- 插件描述
  - [string] $this->describe
- 依赖
  - [array] $this->rely
- 兼容性
  - [array] $this->compatible
  - [ '1.0.1', '2.0.3' ] => 兼容 1.0.1 - 2.0.3
  - [ '*', '2.0.3' ] => 兼容 2.0.3 以下
- 权限注册信息
  - [array] $this->permission
- 权限注册
  - `$this->intervene( [string]:权限名称, [function|string]:运行方法 )`
  - return [boolean]:挂载结果
- 批量引用文件
  - `$this->import( [string|array]:需要引用的文件 )`
  - return [mixed]:返回引用的结果
- 注册到自动加载
  - `$this->auoload( [array]:需要注册的文件 )`
  - return [boolean]:注册结果
- Config 配置
  - `$this->config( [string]:键名, [mixed]|null:默认值 )`
  - return [mixed]:键值

#### [站点] 插件权限声明
- 系统启动运行
  - SYSTEM_STARTUP()
  - 不处理任何值
- 修改或监听系统启动返回结果
  - RETURN_RESULT
  - 传入: [mixed]:系统启动结果
  - 传出: [mixed]:增加或修改的系统启动结果
- 修改配置查询信息
  - QUERY_CONFIGURATION_INFORMATION
  - 传入: [string]:配置查询文件
  - 传出: [array]:增加的配置信息
- 挂载语言包
  - QUERY_LANGUAGE_PACKAGE
  - 传入: [string]:语言, [string]:访问目标
  - 传出: [array]:增加的语言包
- 请求构造完成
  - REQUEST_INITIALIZATION_COMPLETED
  - 传入: [Reuqest]:请求对象
  - 传出: 不处理返回内容
- 修改或监听接口返回数据
  - RETURN_INTERFACE_DATA
  - 传入: [array]:接口返回数据, [Reuqest]:请求对象
  - 传出: [array]:修改的接口返回数据