<?php
    /**
     * 批量引用文件
     * - [string|array]:需要引用的文件, [boolean]:是否为系统文件
     * return [mixed]:返回引用的结果
     */
    function import( $file, $systemPath = true ) {
        $systemPath = $systemPath ? TCorePath() : '';
        if ( is_string( $file ) ) { return require $systemPath.$file; }
        if ( is_array( $file ) ) {
            $result = [];
            for ( $i = 0; $i < 9999; $i++ ) {
                if ( empty( $file[$i] ) ) { break; }
                $quote = require_once $systemPath.$file[$i];
                if ( is_array( $quote ) ) { $result = array_merge( $result, $quote ); }
            }
            return $result;
        }
        return null;
    }