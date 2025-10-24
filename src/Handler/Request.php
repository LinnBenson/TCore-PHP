<?php
    namespace TCore\Handler;
    use TCore\Bootstrap;

    class Request {
        // 预响应代码
        public $code = 200;
        // 允许的请求类型
        private $types = [ 'Http', 'Cli', 'Websocket' ];
        // 请求用户
        public $user = null;
        // 请求数据
        public $type, $id, $lang, $method, $target, $router, $category, $source, $header, $get, $post, $cookie, $file, $ip, $share;
        /**
         * 构造请求
         * - [array]:请求数据
         * return void
         */
        public function __construct( array $data ) {
            switch ( $data['to'] ?? '' ) {
                case 'Http': $this->init(
                    array_merge( $this->toHttpRequest(), $data )
                ); break;

                default: $this->init( $data ); break;
            }
        }
        /**
         * 初始化请求
         * - [array]:渲染数据
         * return void
         */
        public function init( $data = null ) {
            $this->type = isset( $data['type'] ) && in_array( $data['type'], $this->types ) ? $data['type'] : $this->type;
            $this->method = $data['method'] ?? $this->method;
            $this->target = $data['target'] ?? $this->target;
            $this->source = $data['source'] ?? $this->source;
            $this->header = $data['header'] ?? $this->header;
            $this->get = $data['get'] ?? $this->get;
            $this->post = $data['post'] ?? $this->post;
            $this->cookie = $data['cookie'] ?? $this->cookie;
            $this->file = $data['file'] ?? $this->file;
            $this->ip = $data['ip'] ?? $this->ip;
            $this->share = $data['share'] ?? $this->share;
            if ( isset( $data['save'] ) ) { $this->share['save'] = $data['save']; }
            if ( isset( $data['return'] ) ) { $this->share['return'] = $data['return']; }
            // 插件介入
            Bootstrap::permission( 'REQUEST_INITIALIZATION_COMPLETED', $this );
            // 检查请求参数
            $this->type = $this->type ?? 'Unknown';
            $this->method = strtoupper( $this->method ?? 'GET' );
            $this->target = !empty( $this->target ) ? $this->target : '/';
            // 请求路由
            if ( $this->type === 'Http' ) {
                $this->router = 'view';
                if ( startWith( $this->target, '/api/' ) ) {
                    $this->router = 'api';
                    $this->target = substr( $this->target, 4 );
                }else if ( startWith( $this->target, '/storage/' ) ) {
                    $this->router = 'storage';
                    $this->target = substr( $this->target, 8 );
                }
            }
            // 请求分类
            $this->category = explode( '/', trim( $this->target, '/' ) )[0];
            if ( empty( $this->category ) && $this->category !== '0' ) {
                $this->category = 'HOME_PAGE';
            }else {
                $this->target = rtrim( $this->target, '/' );
            }
            $this->source = $this->source ?? '';
            $this->header = array_change_key_case( is_array( $this->header ) ? $this->header : [], CASE_LOWER );
            $this->get = is_array( $this->get ) ? $this->get : [];
            $this->post = is_array( $this->post ) ? $this->post : [];
            $this->cookie = is_array( $this->cookie ) ? $this->cookie : [];
            $this->file = is_array( $this->file ) ? $this->file : [];
            $this->ip = !empty( $this->ip ) && is_string( $this->ip ) ? $this->ip : 'Unknown';
            $this->share = is_array( $this->share ) ? $this->share : [];
            // 设置请求标识与语言
            $this->id = isset( $data['id'] ) ? $data['id'] : $this->header['id'] ?? $this->session['id'] ?? $this->cookie['id'] ?? uuid();
            $this->id = is_uuid( $this->id ) ? $this->id : uuid();
            $this->lang = isset( $data['lang'] ) ? $data['lang'] : $this->header['lang'] ?? $this->session['lang'] ?? $this->cookie['lang'] ?? config( 'app.lang' );
            $this->lang = !empty( $this->lang ) && is_string( $this->lang ) ? $this->lang : config( 'app.lang' );
            // 保存 ID
            if ( isset( $this->share['save'] ) && is_callable( $this->share['save'] ) ) {
                call_user_func( $this->share['save'], 'id', $this->id );
            }
        }
        /**
         * 翻译文本
         * - [string]:文本键, [array]:替换数据
         * return [string]:语言包内容
         */
        public function t( string $key, array $replace = [] ) { return __( $key, $replace, $this->lang ); }
        /**
         * 接口返回
         * - [boolean|int]:状态, [mixed]|null:返回数据, [int]|null:响应代码, [array]:响应头
         * return [string]:返回数据
         */
        public function echo( $status, $data = null, $code = null, $header = [ 'Content-Type' => 'application/json' ] ) {
            // 返回状态
            $statusMap = [ 0 => 'success', 1 => 'fail', 2 => 'error', 3 => 'warning', ];
            if ( is_bool( $status ) ) {
                if ( is_array( $data ) && count( $data ) === 1 ) {
                    $addState = $status ? 'base.true' : 'base.false';
                    $data[0] = "{$data[0]}:{$addState}";
                }
                $status = $status ? 0 : 1;
            }
            $stateName = $statusMap[$status] ?? 'UNKNOWN';
            // 语言处理
            if ( is_array( $data ) && count( $data ) <= 2 && !empty( $data[0] ) && is_string( $data[0] ) ) {
                $msg = $this->t( $data[0], $data[1] ?? [] );
                if ( $msg !== $data[0] ) { $data = $msg; }
            }
            // 处理返回
            $res = [
                'state' => $stateName,
                'code' => is_numeric( $code ) ? $code : $this->code,
                'time' => time(),
                'data' => $data,
            ];
            // 系统流程干预
            $res = Bootstrap::permission( 'RETURN_INTERFACE_DATA', $res, 'last', $this );
            // 返回数据
            if ( isset( $this->share['return'] ) && is_callable( $this->share['return'] ) ) {
                $res = call_user_func( $this->share['return'], $this, $res, $header );
            }
            return is_array( $res ) ? json_encode( $res, JSON_UNESCAPED_UNICODE ) : $res;
        }
        /**
         * 转为HTTP请求数据
         * return array
         */
        private function toHttpRequest() {
            $ip = function() {
                if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                    return $_SERVER['HTTP_CLIENT_IP'];
                }else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }else {
                    return $_SERVER['REMOTE_ADDR'];
                }
                return 'Unknown';
            };
            $post = function() {
                if ( !empty( $_POST ) ) { return $_POST; }
                $rawBody = file_get_contents( 'php://input' );
                if ( empty( $rawBody ) ) { return []; }
                $json = json_decode( $rawBody, true );
                if ( json_last_error() === JSON_ERROR_NONE && is_array( $json ) ) { return $json; }
                parse_str( $rawBody, $parsed );
                return is_array( $parsed ) ? $parsed : [];
            };
            return [
                'type' => 'Http',
                'method' => $_SERVER['REQUEST_METHOD'],
                'target' => parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ),
                'source' => $_SERVER['HTTP_REFERER'] ?? '',
                'header' => getallheaders(),
                'get' => $_GET,
                'post' => $post,
                'cookie' => $_COOKIE,
                'file' => $_FILES,
                'ip' => $ip(),
                'share' => [
                    'save' => function( $key, $value ) {
                        setcookie( $key, $value, time() + 30 * 24 * 60 * 60, '/' );
                        return true;
                    },
                    'return' => function( Request $request, $res, $header ) {
                        if ( is_array( $res ) && is_numeric( $res['code'] ) ) { http_response_code( $res['code'] ?? $request->code ); }
                        foreach ( $header as $key => $value ) { header( "{$key}: {$value}" ); }
                        return $res;
                    }
                ]
            ];
        }
    }