<?php
    namespace TCore\Provider;
    use TCore\Handler\Request;
    use TCore\Handler\Router;

    class HttpProvider {
        /**
         * 初始化
         */
        public static function init() {
            // 构造访问对象
            $request = new Request([ 'to' => 'Http' ]);
            // 注册路由
            Router::register( 'Http', [
                TCorePath()."system/router/{$request->router}.router.php",
                "router/{$request->router}.router.php"
            ], $request->category );
            // 返回结果
            return Router::search( $request, [
                'type' => $request->type,
                'method' => $request->method,
                'target' => $request->target,
                'category' => $request->category
            ]);
        }
    }