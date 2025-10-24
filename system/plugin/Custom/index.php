<?php
    use TCore\Handler\Request;
    use TCore\Slots\Plugin;

    class Custom extends Plugin {
        // 版本号
        public $version = '1.0.0';
        // 描述信息
        public $describe = '在此，您可以自定义一些您的工作流程。它将通过插件的权限介入来为您开始工作。';
        /**
         * 初始化
         */
        public function init() {
            if ( !$this->config( 'enable' ) ) { return null; }
            $this->intervene( 'SYSTEM_STARTUP', 'SYSTEM_STARTUP' );
            $this->intervene( 'RETURN_RESULT', 'RETURN_RESULT' );
            $this->intervene( 'QUERY_CONFIGURATION_INFORMATION', 'QUERY_CONFIGURATION_INFORMATION' );
            $this->intervene( 'QUERY_LANGUAGE_PACKAGE', 'QUERY_LANGUAGE_PACKAGE' );
            $this->intervene( 'REQUEST_INITIALIZATION_COMPLETED', 'REQUEST_INITIALIZATION_COMPLETED' );
            $this->intervene( 'RETURN_INTERFACE_DATA', 'RETURN_INTERFACE_DATA' );
        }
        /**
         * 系统启动运行
         * - 不处理返回值
         */
        public function SYSTEM_STARTUP() {
            return null;
        }
        /**
         * 修改或监听系统启动返回结果
         * - 传入: [mixed]:系统启动结果
         * - 传出: [mixed]:增加或修改的系统启动结果
         */
        public function RETURN_RESULT( $data ) {
            return null;
        }
        /**
         * 修改配置查询信息
         * - 传入: [string]:配置查询文件
         * - 传出: [array]:增加的配置信息
         */
        public function QUERY_CONFIGURATION_INFORMATION( $config ) {
            return null;
        }
        /**
         * 挂载语言包
         * - 传入: [string]:语言, [string]:访问目标
         * - 传出: [array]:增加或修改的语言包
         */
        public function QUERY_LANGUAGE_PACKAGE( $locale, $target ) {
            return null;
        }
        /**
         * 监听初始化请求
         * - 传入: [Reuqest]:请求对象
         * - 传出: 不处理返回内容
         */
        public function REQUEST_INITIALIZATION_COMPLETED( Request $request ) {
            return null;
        }
        /**
         * 修改或监听接口返回数据
         * - 传入: [array]:接口返回数据, [Reuqest]:请求对象
         * - 传出: [mixed]:修改的接口返回数据
         */
        public function RETURN_INTERFACE_DATA( $res, Request $request ) {
            return null;
        }
    }

    return new Custom();