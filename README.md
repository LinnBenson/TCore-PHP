# TCore PHP
> 方便您继续使用 PHP 完成您的开发

```
// 安装
composer require linnbenson/tcore-php

// 如果您将此组件用作网站系统，请运行以下命令1
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

### 核心驱动器 => TCore\Bootstrap
- 驱动安装状态
  - [boolean]|false Bootstrap::$status
- 存储器目录
  - [string]|storage/ Bootstrap::$storage
- [站点] 注册驱动
  - `Bootstrap::register( [function]|null:回调函数 )`
  - return [mixed]:回调结果或 null
- 初始化程序
  - `Bootstrap::init( [array]|[]:配置信息 )`
  - 配置数组中包含: debug, timezone
  - return true
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
- 哈希一个参数
  - `h( [string]:传入的内容 )`
  - return [string|null]:返回哈希后的字符串或 null
- 插件使用
  - `plugin( [string]:插件名称 )`
  - return [object|null]:插件对象