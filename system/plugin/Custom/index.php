<?php
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
            return $data;
        }
        /**
         * 修改配置查询信息
         * - 传入: [string]:配置查询文件
         * - 传出: [array]:增加或修改的配置信息
         */
        public function QUERY_CONFIGURATION_INFORMATION( $config ) {
            return null;
        }
    }

    return new Custom();