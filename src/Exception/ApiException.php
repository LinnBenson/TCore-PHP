<?php
    namespace TCore\Exception;
    use Exception;
    use TCore\Handler\Request;

    /**
     * API异常处理
     */
    class ApiException extends Exception {
        protected $code; // 状态码
        public function __construct( $message, int $code = 400 ) {
            parent::__construct( $message, $code );
        }
        /**
         * 输出异常信息
         * - [object]:请求对象
         * return [mixed]:输出结果
         */
        public function echo( Request $request ) {
            return $request->echo( 2, $this->message, $this->code );
        }
    }