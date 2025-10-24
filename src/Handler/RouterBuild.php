<?php
    namespace TCore\Handler;
    use TCore\Bootstrap;
use TCore\Exception\LogException;

    /**
     * 路由构造器 - 创建
     */
    class RouterBuild {
        private $status = false;
        public $path, $method, $category, $func, $auth;
        /**
         * 构造路由对象
         * - [string]:路由类型, [string]:路由路径, [string]:路由方法
         * return void
         */
        public function __construct( string $path, $method ) {
            $group = Router::$cache['parent'];
            // 保存传递的参数
            $path = str_replace( '.', '/', is_object( $group ) ? $group->path.$path : $path );
            if ( !startWith( $path, '/' ) ) { $path = "/{$path}"; }
            $this->path = $path;
            $this->method = strtoupper( empty( $method ) ? ( is_object( $group ) ? $group->method : 'ANY' ) : $method );
            $this->category = explode( '/', trim( $this->path, '/' ) )[0];
            $this->auth = is_object( $group ) ? $group->auth : [];
            // 处理并检查请求类别
            if ( empty( $this->category ) && $this->category !== '0' ) {
                $this->category = 'HOME_PAGE';
            }
            if ( Router::$cache['category'] === null || Router::$cache['category'] === $this->category ) {
                $this->status = true;
            }
        }
        /**
         * 功能 - 访问函数
         * - [function]:路由处理函数
         * return [object]:路由对象
         */
        public function to( callable $method ) {
            if ( !$this->status ) { return $this; }
            if ( is_callable( $method ) ) {
                $this->func = function( Request $request, ...$params )use ( $method ) {
                    return call_user_func_array( $method, array_merge( [ $request ], $params ) );
                };
            }
            return $this;
        }
        /**
         * 功能 - 重定向URL
         * - [string]:重定向URL
         * return [object]:路由对象
         */
        public function url( string $url ) {
            if ( !$this->status ) { return $this; }
            if ( is_string( $url ) ) {
                $this->func = function()use ( $url ){
                    return "<script type=\"text/javascript\">window.location.href='{$url}';</script>";
                };
            }
            return $this;
        }
        /**
         * 功能 - 控制器
         * - [string|array]:控制器名称或数组
         * return [object]:路由对象
         */
        public function controller( $controller ) {
            if ( !$this->status ) { return $this; }
            if ( is_string( $controller ) || is_array( $controller ) ) {
                $this->func = function( Request $request, ...$params )use ( $controller ) {
                    return Router::useController( $controller, $request, ...$params );
                };
            }
            return $this;
        }
        /**
         * 添加路由认证
         * - [function]:认证函数
         * return [object]:路由对象
         */
        public function auth( $auth ) {
            if ( !$this->status ) { return $this; }
            if ( is_callable( $auth ) ) {
                $this->auth[] = function( Request $request, ...$params )use ( $auth ) {
                    return call_user_func_array( $auth, array_merge( [ $request ], $params ) );
                };
            }
            if ( is_string( $auth ) || is_array( $auth ) ) {
                $this->auth[] = function( Request $request, ...$params )use ( $auth ) {
                    return Router::useController( $auth, $request, ...$params );
                };
            }
            return $this;
        }
        /**
         * 群组式路由
         * - [function]:路由分组函数
         * return [object]:路由对象
         */
        public function group( callable $method ) {
            if ( !$this->status ) { return $this; }
            Router::$cache['parent'] = $this;
            $method();
            Router::$cache['parent'] = null;
            return $this;
        }
        /**
         * 保存路由
         * return [boolean]:保存结果
         */
        public function save() {
            // 检查路由类型
            if ( empty( Router::$cache['register'] ) ) {
                // 路由类型未标记
                throw new LogException([
                    'error' => 'The route type is not marked before generating the route.',
                    'file' => debug_backtrace()[0]['file'],
                    'line' => debug_backtrace()[0]['line'],
                    'method' => $this->method,
                    'path' => $this->path
                ]);
            }
            // 检查必要参数
            if (
                !$this->status ||
                $this->path === null || !is_string( $this->path ) ||
                $this->method === null || !is_string( $this->method ) ||
                $this->category === null || !is_string( $this->category ) ||
                !is_callable( $this->func )
            ) { return $this; }
            // 保存完成
            Router::$cache[Router::$cache['register']] ??= [];
            Router::$cache[Router::$cache['register']][$this->category] ??= [];
            Router::$cache[Router::$cache['register']][$this->category]["{$this->method}|{$this->path}"] = $this;
            $this->status = false;
            return $this;
        }
    }