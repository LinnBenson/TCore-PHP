<?php
    namespace TCore\Controller;
    use TCore\Bootstrap;
    use TCore\Handler\Request;

    class BaseController {
        /**
         * 系统信息接口
         */
        public function index( Request $request ) {
            return $request->echo( 0, [
                'debug' => config( 'app.debug' ),
                'title' => config( 'app.title' ),
                'version' => Bootstrap::$version,
                'language' => $request->lang,
                'timezone' => config( 'app.timezone' ),
                'host' => config( 'app.host' )
            ]);
        }
        /**
         * 调试接口
         */
        public function debug( Request $request, $s1 = null, $s2 = null ) {
            dd([ $s1, $s2 ]);
        }
    }