<?php
    namespace TCore\Handler;
    use TCore\Exception\ApiException;
use TCore\Exception\LogException;

    /**
     * 路由构造器
     */
    class Router {
        // 路由缓存
        public static $cache = [ 'register' => null, 'category' => null, 'parent' => null ];
        /**
         * 注册路由
         * - [strng]:注册类型, [array|function]:路由列表或函数, [string]|null:路由分类
         * return [boolean]:注册结果
         */
        public static function register( string $type, $routers = [], $category = null ) {
            if ( empty( $type ) || !is_string( $type ) || !empty( self::$cache['register'] ) ) {
                // 路由类型错误
                throw new LogException([
                    'error' => 'Route registration type error.',
                    'file' => debug_backtrace()[0]['file'],
                    'line' => debug_backtrace()[0]['line']
                ]);
            }
            // 标记注册开始
            self::$cache['register'] = $type;
            self::$cache['category'] = $category;
            // 如果是传入了路由列表
            if ( is_array( $routers ) ) {
                foreach ( $routers as $router ) {
                    if ( is_string( $router ) && file_exists( $router ) ) {
                        import( $router, false );
                    }
                }
            }
            // 函数式注册
            if ( is_callable( $type ) ) { $type(); }
            // 标记注册结束
            self::$cache['register'] = null;
            self::$cache['category'] = null;
            return true;
        }
        /**
         * 搜索路由
         * - [Request]:请求对象, [array]:路由数据, [array]:附加参数
         * return [mixed]:路由结果
         */
        public static function search( Request $request, array $data, array $parameter = [] ) {
            $routers = self::$cache[$data['type']] ?? [];
            // 路由错误处理函数
            $error404 = function()use ( $request, $routers ) {
                $setError = $routers['ERROR_404'] ?? [];
                $setError = $setError['/ERROR_404'] ?? null;
                if ( is_object( $setError ) ) {
                    return self::runRouter( $request, $setError, [ $request->t( 'base.error.404' ), 404 ] );
                }
                return self::errer( $request, $request->t( 'base.error.404' ), 404 );
            };
            $routers = $routers[$data['category']] ?? [];
            if ( empty( $routers ) ) { return $error404(); }
            // 精准匹配
            $check = $routers["{$data['method']}|{$data['target']}"] ?? false;
            if ( is_object( $check ) ) { return self::runRouter( $request, $check, $parameter ); }
            $check = $routers["ANY|{$data['target']}"] ?? false;
            if ( is_object( $check ) ) { return self::runRouter( $request, $check, $parameter ); }
            // 模糊匹配
            foreach ( $routers as $key => $router ) {
                if ( strpos( $key, '{{' ) === false ) { continue; }
                $routerKey = explode( '|', $key );
                if ( $routerKey[0] !== $data['method'] && $routerKey[0] !== 'ANY' ) { continue; }
                $pattern = preg_replace( '#{{[^?]+}}#', '([^/]+)', $routerKey[1] );
                $pattern = preg_replace( '#{{.*?\?}}#', '(.*?)', $pattern );
                if ( preg_match( "#^{$pattern}$#", $data['target'], $matches ) === 1 ) {
                    array_shift( $matches );
                    $matches = array_merge( $parameter, $matches );
                    return self::runRouter( $request, $router, $matches );
                }
            }
            // 未找到路由
            return $error404();
        }
        /**
         * 执行路由
         * - [Request]:请求对象, [object]:路由对象, [array]:附加参数
         * return [mixed]:路由结果
         */
        private static function runRouter( Request $request, $router, $parameter ) {
            if ( !is_object( $router ) ) { return self::errer( $request, $request->t( 'base.error.500' ), 500 ); }
            try {
                foreach ( $router->auth as $auth ) {
                    if ( is_callable( $auth ) ) {
                        $result = call_user_func( $auth, array_merge( [ $request ], $parameter ) );
                        if ( $result !== null ) { return $result; }
                    }
                }
                // 执行路由函数
                $func = $router->func;
                return call_user_func_array( $func, array_merge( [ $request ], $parameter ) );
            } catch ( \Exception $e ) {
                // 接口错误
                if ( $e instanceof ApiException ) {
                    return $e->echo( $request );
                }
                // 服务器错误
                return self::errer( $request, $request->t( 'base.error.500' ), 500 );
            }
        }
        /**
         * 路由错误处理
         * - [Request]:请求对象, [string]:错误信息, [int]:错误代码
         * return [mixed]:错误处理结果
         */
        public static function errer( Request $request, string $message, int $code ) {
            switch ( $request->router ) {
                case 'view': return view( TCorePath().'system/resource/view/error.view.php', [ 'code' => $code, 'msg' => $message ] );

                default: return $request->echo( 2, $message, $code );
            }
        }
        /**
         * 使用控制器
         * - [string|array]:控制器, Request:请求对象, ...params:其他参数
         * return [mixed]:控制器返回值
         */
        public static function useController( $controller, Request $request, ...$params ) {
            if ( is_string( $controller ) ) {
                $controller = explode( '@', $controller );
                if ( count( $controller ) !== 2 ) {
                    // 控制器格式错误
                    throw new LogException([
                        'error' => 'Controller format error.',
                        'file' => debug_backtrace()[0]['file'],
                        'line' => debug_backtrace()[0]['line'],
                        'controller' => $controller
                    ]);
                }
                $controller[0] = 'App\\Controller\\'.$controller[0];
            }
            if ( is_array( $controller ) ) {
                if ( class_exists( $controller[0] ) ) {
                    $obj = new $controller[0]();
                    if ( method_exists( $obj, $controller[1] ) ) {
                        return call_user_func_array( [ $obj, $controller[1] ], array_merge( [ $request ], $params ) );
                    }
                }
                throw new ApiException( $request->t( 'base.error.404' ), 404 );
            }
            // 控制器类型错误
            throw new LogException([
                'error' => 'Controller type error.',
                'file' => debug_backtrace()[0]['file'],
                'line' => debug_backtrace()[0]['line']
            ]);
        }
        /**
         * 添加一个新路由
         * - [string]:路由路径, [string]|ANY:路由方法
         * return [object|null]:路由结果
         */
        public static function add( string $path, $method = null ) { return new RouterBuild( $path, $method ); }
    }