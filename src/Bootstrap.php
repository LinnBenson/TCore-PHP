<?php
    namespace TCore;

    /**
     * 核心驱动器
     */
    class Bootstrap {
        // 驱动缓存
        public static $cache = [
            'autoload' => [], // 自动加载
        ];
        public static $status = false; // 驱动安装状态
        /**
         * 安装驱动
         * - [function]|null:回调函数
         * return [mixed]:回调结果或 null
         */
        public static function install( $method = null ) {
            // 标记安装
            Bootstrap::$status = true;
            // 导入系统通用函数
            require_once TCorePath().'support/Helper/System.php';
            // 注册自动加载
            Bootstrap::$cache['autoload'] = import( 'support/autoload.config.php' );
            self::autoload();
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
    }