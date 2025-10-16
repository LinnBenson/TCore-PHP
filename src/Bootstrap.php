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
        /**
         * 注册驱动
         * - [function]|null:回调函数
         * return [mixed]:回调结果或 null
         */
        public static function register( $method = null ) {
            // 标记安装
            Bootstrap::$status = true;
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
            // 回调结果
            return is_callable( $method ) ? $method() : null;
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
         * 自动加载
         * return [boolean]:加载结果
         */
        private static function autoload() {
            spl_autoload_register(function( $class ) {
                if ( isset( Bootstrap::$cache['autoload'][$class] ) && is_file( Bootstrap::$cache['autoload'][$class] ) ) {
                    require_once import( Bootstrap::$cache['autoload'][$class] );
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
                    if ( env( 'APP_DEBUG', false ) === true ) { return $method(); }
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