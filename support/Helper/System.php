<?php
    use TCore\Bootstrap;

    /**
     * 批量引用文件
     * - [string|array]:需要引用的文件, [boolean]|true:是否为系统文件
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
    /**
     * ENV 变量
     * - [string]:键名, [mixed]|null:默认值
     * return [mixed]:键值
     */
    function env( $key, $default = null ) {
        $value = isset( $_ENV[$key] ) && $_ENV[$key] !== '' ? $_ENV[$key] : $default;
        if ( $value === 'true' ) { return true; }
        if ( $value === 'false' ) { return false; }
        if ( $value === 'null' ) { return null; }
        return $value;
    }
    /**
     * Config 配置
     * - [string]:键名, [mixed]|null:默认值
     * return [mixed]:键值
     */
    function config( $key, $default = null ) {
        // 键拆分
        $keys = explode( '.', $key );
        // 获取配置
        $value = Bootstrap::cache( 'thread', "config:{$keys[0]}", function()use( $keys ) {
            $result = [];
            $systemFile = TCorePath()."system/config/{$keys[0]}.php";
            if ( file_exists( $systemFile ) ) { $result = require $systemFile; }
            $userFile = "config/{$keys[0]}.php";
            if ( file_exists( $userFile ) ) { $result = array_merge( $result, require $userFile ); }
            return $result;
        });
        // 获取配置值
        if ( count( $keys ) === 1 && !empty( $value ) ) { return $value; }
        if ( !is_array( $value ) || empty( $value ) ) { return $default; }
        if ( empty( $value ) ) { return $default;}
        array_shift( $keys ); foreach ( $keys as $k ) {
            if ( isset( $value[$k] ) ) {
                $value = $value[$k];
            }else {
                return $default;
            }
        }
        return $value;
    }
    /**
     * 插件使用
     * - [string]:插件名称
     * return [object|null]:插件对象
     */
    function plugin( $name ) {
        // ........
    }