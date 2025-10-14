# TCore PHP
> 方便您继续使用 PHP 完成您的开发

```
// 安装
composer install linnbenson/tcore-php
// 如果您将此组件用作网站系统，请运行以下命令
chmod +x vendor/bin/tcore-install && vendor/bin/tcore-install
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

### 核心驱动器 => TCore\Bootstrap
- 驱动安装状态
  - [boolean] Bootstrap::$status
- 安装驱动
  - `Bootstrap::install( [function]|null:回调函数 )`
  - return [mixed]:回调结果或 null
- 初始化程序
  - `Bootstrap::init( [array]|[]:配置信息 )`
  - 配置数组中包含: debug, timezone
  - return true

#### 第三方库引用声明
- vlucas/phpdotenv
- blocktrail/cryptojs-aes-php