<?php
    namespace TCore\Slots;
    use TCore\Bootstrap;

    class Plugin {
        // 插件名称
        public $name = null;
        // 插件根目录
        public $path = null;
        // 插件版本号
        public $version = '1.0.0';
        // 插件描述
        public $describe = 'This plugin has no description.';
        // 依赖
        public $rely = [];
        // 兼容性
        public $compatible = [ '*', '*' ];
        // 权限注册信息
        public $permission = [];
        /**
         * 权限注册
         * - [string]:权限名称, [function|string]:运行方法
         * return [boolean]:挂载结果
         */
        public function intervene( string $name, $method ) {
            // 检查权限合法性
            $plugins = config( "permission.{$name}" );
            if ( !is_array( $plugins ) || !in_array( $this->name, $plugins ) ) { return false; }
            // 开始写入权限
            if ( is_string( $method ) && isPublic( $this, $method ) ) {
                $method = function( ...$data )use ( $method ) {
                    return $this->$method( ...$data );
                };
            }
            if ( !is_callable( $method ) ) { return false; }
            $this->permission[$name] = $method;
            return true;
        }
        /**
         * 文件引用
         * - [string|array]:需要引用的文件
         * return [mixed]:返回引用的结果
         */
        public function import( $file ) {
            if ( is_array( $file ) ) {
                foreach( $file as $key => $value ) {
                    $file[$key] = $this->path.$value;
                }
            }
            if ( is_string( $file ) ) { $file = $this->path.$file; }
            return import( $file, false );
        }
        /**
         * 注册到自动加载
         * - [array]:需要注册的文件
         * return [boolean]:注册结果
         */
        public function auoload( array $data = [] ) {
            foreach( $data as $key => $value ) {
                $data[$key] = $this->path.$value;
            }
            return array_merge( Bootstrap::$cache['autoload'], $data );
        }
        /**
         * Config 配置
         * - [string]:键名, [mixed]|null:默认值
         * return [mixed]:键值
         */
        public function config( string $key, $default = null ) {
            $value = Bootstrap::cache( 'thread', "config:plugin_{$this->name}", function() {
                $result = [];
                $systemFile = $this->path.'config.php';
                if ( file_exists( $systemFile ) ) { $result = require $systemFile; }
                $userFile = "config/plugin/{$this->name}.php";
                if ( file_exists( $userFile ) ) { $result = array_merge( $result, require $userFile ); }
                return $result;
            });
            // 键拆分
            $keys = explode( '.', $key );
            // 获取配置值
            if ( !is_array( $value ) || empty( $value ) ) { return $default; }
            foreach ( $keys as $k ) {
                if ( isset( $value[$k] ) ) {
                    $value = $value[$k];
                }else {
                    return $default;
                }
            }
            return $value;
        }
    }