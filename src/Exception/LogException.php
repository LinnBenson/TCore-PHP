<?php
    namespace TCore\Exception;
    use Exception;
    use TCore\Bootstrap;

/**
 * 需要记录日志的异常
 */
class LogException extends Exception {
    public function __construct( $message ) {
        if ( is_array( $message ) ) { $message = json_encode( $message, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES ); }
        parent::__construct( $message );
        Bootstrap::log( 'Bootstrap', $message, 'Exception' );
    }
}