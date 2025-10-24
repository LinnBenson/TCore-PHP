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
    function env( string $key, $default = null ) {
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
    function config( string $key, $default = null ) {
        // 键拆分
        $keys = explode( '.', $key );
        // 获取配置
        $value = Bootstrap::cache( 'thread', "config:{$keys[0]}", function()use( $keys ) {
            $result = [];
            $systemFile = TCorePath()."system/config/{$keys[0]}.php";
            if ( file_exists( $systemFile ) ) { $result = require $systemFile; }
            $userFile = "config/{$keys[0]}.php";
            if ( file_exists( $userFile ) ) { $result = array_merge( $result, require $userFile ); }
            if ( Bootstrap::$status && $keys[0] !== 'permission' ) {
                $add = Bootstrap::permission( 'QUERY_CONFIGURATION_INFORMATION', $keys[0], 'all' );
                if ( is_array( $add ) ) { $result = array_merge( $result, $add ); }
            }
            return $result;
        });
        // 获取配置值
        if ( count( $keys ) === 1 && !empty( $value ) ) { return $value; }
        if ( !is_array( $value ) || empty( $value ) ) { return $default; }
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
    function plugin( string $name ) {
        return Bootstrap::cache( 'thread', "plug:{$name}", function()use( $name ) {
            $index = "plugin/{$name}/index.php";
            if ( !file_exists( $index ) ) {
                Bootstrap::log( 'Bootstrap', "{$name} not found.", 'Plugin' );
                return null;
            }
            $plugin = import( $index, false );
            // 确保是 Plug 类的实例
            if ( !is_object( $plugin ) || !is_subclass_of( $plugin, 'TCore\Slots\Plugin' ) ) {
                Bootstrap::log( 'Bootstrap', "{$name} must extend TCore\Slots\Plugin.", 'Plugin' );
                return null;
            }
            // 检查依赖
            if ( is_array( $plugin->rely ) && !empty( $plugin->rely ) ) {
                foreach( $plugin->rely as $rely ) {
                    $rely = "plugin/{$rely}/index.php";
                    if ( !file_exists( $rely ) ) { return null; }
                }
            }
            // 检查版本号
            if ( is_array( $plugin->compatible ) && count( $plugin->compatible ) === 2 ) {
                $low = $plugin->compatible[0];
                if ( $low !== '*' && version_compare( Bootstrap::$version, $low, '<' ) ) { return null; }
                $high = $plugin->compatible[1];
                if ( $high !== '*' && version_compare( Bootstrap::$version, $high, '>' ) ) { return null; }
            }
            // 初始化插件
            $plugin->name = $name;
            $plugin->path = "plugin/{$name}/";
            if ( isPublic( $plugin, 'init' ) ) {
                try {
                    $plugin->init();
                }catch ( \Throwable $e ) {
                    Bootstrap::log( 'Bootstrap', "{$name} init error: ".$e->getMessage(), 'Plugin' );
                    return null;
                }
            }
            // 导出插件
            return $plugin;
        });
    }
    /**
     * 读取语言包
     */
    function __( string $key, array $replace = [], $locale = null ) {
        // 检查是否为双重语言调用
        $check = explode( ':', $key );
        if ( count( $check ) === 2 && empty( $replace ) ) { return __( $check[0], [], $locale ).__( $check[1], [], $locale ); }
        // 整理参数
        $keys = explode( '.', $key );
        if ( empty( $locale ) ) { $locale = config( 'app.lang' ); }
        // 加载使用的语言包
        $text = Bootstrap::cache( 'thread', "lang:{$locale}|{$keys[0]}", function()use( $locale, $keys ) {
            $result = [];
            $folder1 = TCorePath()."system/resource/lang/{$locale}/{$keys[0]}.php";
            $folder2 = "resource/lang/{$locale}/{$keys[0]}.php";
            if ( !file_exists( $folder1 ) && !file_exists( $folder2 ) ) {
                $locale = config( 'app.lang' );
                $folder1 = TCorePath()."system/resource/lang/{$locale}/{$keys[0]}.php";
                $folder2 = "resource/lang/{$locale}/{$keys[0]}.php";
            }
            if ( file_exists( $folder1 ) ) { $result = array_merge( $result, require $folder1 ); }
            if ( file_exists( $folder2 ) ) { $result = array_merge( $result, require $folder2 ); }
            $add = Bootstrap::permission( 'QUERY_LANGUAGE_PACKAGE', $locale, 'all', $keys[0] );
            if ( is_array( $add ) ) { $result = array_merge( $result, $add ); }
            return is_array( $result ) ? $result : [];
        });
        // 查询语言包
        array_shift( $keys ); foreach ( $keys as $k ) {
            if ( !isset( $text[$k] ) ) { return $key; }
            $text = $text[$k];
        }
        if ( !is_string( $text ) ) { return $key; }
        if ( empty( $replace ) ) { return $text; }
        // 替换占位符
        foreach ( $replace as $k => $v ) { $text = str_replace( "{{".$k."}}", $v, $text ); }
        return $text;
    }