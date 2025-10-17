<?php
    namespace TCore;
    use Dotenv\Dotenv;

    /**
     * 核心驱动器
     */
    class Bootstrap {
        // 驱动缓存
        public static $cache = [
            'autoload' => [], // 自动加载
            'thread' => [], // 线程缓存
        ];
        public static $status = false; // 驱动安装状态
        public static $storage = 'storage/'; // 存储器目录
        public static $version = '1.0.5'; // 驱动器版本号
        /**
         * 注册驱动
         * - [function]|null:回调函数
         * return [mixed]:回调结果或 null
         */
        public static function register( $method = null ) {
            if ( Bootstrap::$status ) { return null; } // 要求驱动未注册
            // 导入系统通用函数
            require_once TCorePath().'support/Helper/System.php';
            // 注册自动加载
            Bootstrap::$cache['autoload'] = import( 'support/autoload.config.php' );
            Bootstrap::autoload();
            // 加载 .env 文件
            try {
                $dotenv = Dotenv::createImmutable( getcwd() );
                $dotenv->load();
            }catch( \Throwable $e ) {
                trigger_error( ".env import error: ".$e->getMessage(), E_USER_WARNING );
            }
            if ( !env( 'APP_ENABLE', false ) ) { exit( 'This application is currently closed.' ); }
            // 初始化系统
            Bootstrap::init([
                'debug' => config( 'app.debug' ),
                'timezone' => config( 'app.timezone' )
            ]);
            // 标记安装
            Bootstrap::$status = true;
            // 权限介入
            Bootstrap::permission( 'SYSTEM_STARTUP' );
            // 回调结果
            return Bootstrap::permission( 'RETURN_RESULT', is_callable( $method ) ? $method() : null );
        }
        /**
         * 初始化程序
         * - [array]|[]:配置信息
         * return true
         */
        public static function init( $config = [] ) {
            $config = is_array( $config ) ? $config : [];
            $debug = $config['debug'] ?? false;
            $timezone = $config['timezone'] ?? 'Asia/Singapore';
            // 调试模式
            if ( $debug ) {
                error_reporting( E_ALL );
                ini_set( 'display_errors', 1 );
            }else {
                error_reporting( 0 ); ini_set( 'display_errors', 0 );
            }
            // 设定时区
            if ( !@date_default_timezone_set( $timezone ) ) {
                trigger_error( "Error in setting timezone: $timezone", E_USER_WARNING );
                return false;
            }
            return true;
        }
        /**
         * 插件权限介入
         * - [string]:权限名称, [mixed]|null:传递参数
         * return [mixed]:传递参数
         */
        public static function permission( $permission, $argv = null ) {
            if ( !Bootstrap::$status ) { return $argv; } // 要求驱动已注册
            // 加载执行的插件
            $plugins = config( "permission.{$permission}" );
            if ( !is_array( $plugins ) ) { $plugins = []; }
            if ( empty( $plugins ) ) { return $argv; }
            // 顺序执行
            foreach( $plugins as $plugin ) {
                // 插件介入
                $plugin = plugin( $plugin );
                if ( !is_object( $plugin ) ) { continue; }
                if ( isset( $plugin->permission[$permission] ) && is_callable( $plugin->permission[$permission] ) ) {
                    $argv = $plugin->permission[$permission]( $argv );
                }
            }
            return $argv;
        }
        /**
         * 记录日志
         * - [string]:日志名称, [string|object]日志信息, [string]|null:日志标题
         * return [boolean]:日志记录结果
         */
        public static function log( $name, $info, $title = null ) {
            // 参数检查
            if ( empty( $name ) || !is_string( $name ) || empty( $info ) ) { return false; }
            // 日志保存位置
            $file = inFolder( Bootstrap::$storage."log/".str_replace( '.', '/', $name ).'_'.date( 'Ymd' ).'.log' );
            // 传入为对象时认为是异常对象
            if ( is_object( $info ) && method_exists( $info, 'getFile' ) && method_exists( $info, 'getLine' ) && method_exists( $info, 'getMessage' ) ) {
                $info = "{$info->getFile()}[{$info->getLine()}]: {$info->getMessage()}";
            }
            // 再次检查是否为字符串
            if ( is_array( $info ) || is_object( $info ) ) { $info = json_encode( $info, JSON_UNESCAPED_UNICODE ); }
            if ( !is_string( $info ) ) { return false; }
            // 准备写入内容
            $title = $title ? "{$title} | " : '';
            $content = date( 'H:i:s' )." {$title}{$info}\n";
            if ( file_exists( $file ) && filesize( $file ) > 3 * 1024 * 1024 ) { rename( $file, "{$file}.".date( 'His' ).".bak" ); }
            return file_put_contents( $file, $content, FILE_APPEND ) ? true : false;
        }
        /**
         * 自动加载
         * return [boolean]:加载结果
         */
        private static function autoload() {
            spl_autoload_register(function( $class ) {
                if ( isset( Bootstrap::$cache['autoload'][$class] ) ) {
                    import( Bootstrap::$cache['autoload'][$class] );
                    return true;
                }
                if ( startWith( $class, 'App\\' ) ) {
                    $path = str_replace( '\\', '/', $class );
                    $path = preg_replace( '/^App/', 'app', $path );
                    if ( file_exists( "{$path}.php" ) ) { import( "{$path}.php", false ); }
                    return true;
                }
                return false;
            });
        }
        /**
         * 缓存处理
         * - [thread|file]:缓存方式, [string]:缓存名称, [function]:缓存内容
         * return [mixed]:缓存值
         */
        public static function cache( $type, $name, $method ) {
            if ( !is_callable( $method ) ) { return null; }
            switch ( $type ) {
                case 'thread':
                    // 从线程中搜索
                    if ( isset( Bootstrap::$cache['thread'][$name] ) ) { return Bootstrap::$cache['thread'][$name]; }
                    // 如果没有数据，则执行回调
                    Bootstrap::$cache['thread'][$name] = $method();
                    return Bootstrap::$cache['thread'][$name];
                    break;
                case 'file':
                    // 调试模式禁用时不从文件加载缓存
                    if ( Bootstrap::$status && env( 'APP_DEBUG', false ) === true ) { return $method(); }
                    // 从文件中搜索
                    $fileType = endWith( $name, 'php' ) ? 'php' : 'txt';
                    $name = h( $name );
                    $file = inFolder( Bootstrap::$storage."cache/Bootstrap/{$name}.{$fileType}" );
                    if ( file_exists( $file ) ) { return $fileType === 'php' ? require $file : file_get_contents( $file ); }
                    // 如果没有数据，则执行回调
                    $result = $method();
                    if ( $fileType === 'php' ) {
                        if ( is_array( $result ) ) {
                            file_put_contents( $file, "<?php\nreturn ".var_export( $result, true ).";\n" );
                        }else {
                            file_put_contents( $file, $result ); $result = require $file;
                        }
                        return $result;
                    }
                    file_put_contents( $file, $result );
                    return $result;
                    break;

                default: break;
            }
            // 查询失败
            return null;
        }
    }