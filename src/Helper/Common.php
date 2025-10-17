<?php
    use Blocktrail\CryptoJSAES\CryptoJSAES;
    use TCore\Bootstrap;

    /**
     * 变量检查：是否为 json
     * ---
     * - [mixed]:变量
     * return [boolean]:判断结果
     */
    if ( !function_exists( 'is_json' ) ) {
        function is_json( $val ) {
            if ( !is_string( $val ) || $val === '' ) { return false; }
            $jsonData = json_decode( $val );
            return (
                json_last_error() === JSON_ERROR_NONE &&
                ( is_object( $jsonData ) || is_array( $jsonData ) )
            );
        }
    }
    /**
     * 变量检查：是否为 UUID
     * ---
     * - [mixed]:变量
     * return [boolean]:判断结果
     */
    if ( !function_exists( 'is_uuid' ) ) {
        function is_uuid( $val ) {
            if ( !is_string( $val ) || $val === '' ) { return false; }
            $pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';
            return preg_match( $pattern, $val ) === 1;
        }
    }
    /**
     * 生成 UUID
     * ---
     * return [string]:UUID
     */
    if ( !function_exists( 'uuid' ) ) {
        function uuid() {
            $data = random_bytes( 16 );
            $data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 );
            $data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 );
            return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
        }
    }
    /**
     * 强制转为字符串
     * ---
     * - [mixed]:转换内容
     * return [string]:转换后的内容
     */
    if ( !function_exists( 'toString' ) ) {
        function toString( $value ) {
            if ( is_string( $value ) ) { return $value; }
            if ( is_array( $value ) ) {
                $text = true;
                foreach( $value as $arrayKey => $arrayValue ) {
                    if ( !is_numeric( $arrayKey ) || !is_string( $arrayValue ) ) {
                        $text = false;
                    }
                }
                return $text ? implode( "\n", $value ) : var_export( $value, true );
            }
            if ( is_bool( $value ) ) { return $value ? '[true]' : '[false]'; }
            if ( is_null( $value ) ) { return '[null]'; }
            if ( is_numeric( $value ) ) { return (string)$value; }
            if ( is_object( $value ) ) { return '[object:'.get_class( $value ).']'; }
            if ( is_callable( $value ) ) { return '[function]'; }
            return var_export( $value, true );
        }
    }
    /**
     * 路径保护式创建
     * ---
     * - [string]:目录或文件路径, [number]|0777:创建权限
     * return [string]:传入的路径
     */
    if ( !function_exists( 'inFolder' ) ) {
        function inFolder( $dir, $permissions = 0777 ) {
            $path = preg_match( '/[^\/\\\\]+\.\w+$/' , $dir ) ? dirname( $dir ) : $dir;
            if ( !is_dir( $path ) ) {
                if ( !mkdir( $path, $permissions, true ) && !is_dir( $path ) ) {
                    return null;
                }
            }
            return $dir;
        }
    }
    /**
     * 删除目录
     * - [string]:目录路径
     * return [boolean]:删除结果
     */
    if ( !function_exists( 'deleteDir' ) ) {
        function deleteDir( $dir ) {
            if ( !is_dir( $dir ) ) { return false; }
            foreach( scandir( $dir ) as $file ) {
                if ( $file === '.' || $file === '..' ) { continue; }
                $path = "{$dir}/{$file}";
                is_dir( $path ) ? deleteDir( $path ) : unlink( $path );
            }
            return rmdir( $dir );
        }
    }
    /**
     * 复制目录
     * - [string]:源目录路径, [string]:目标路径
     * return [boolean]:复制结果
     */
    if ( !function_exists( 'copyDir' ) ) {
        function copyDir( $src, $dst ) {
            // 去掉末尾的 /
            $src = rtrim( $src, '/\\' );
            $dst = rtrim( $dst, '/\\' );
            // 检查目录可用性
            if ( !is_dir( $src ) ) { return false; }
            if ( !is_dir( $dst ) ) { mkdir( $dst, 0777, true ); }
            // 开始递归复制
            $dir = opendir( $src );
            while ( false !== ( $file = readdir( $dir ) ) ) {
                if ( $file == '.' || $file == '..' ) { continue; }
                $srcFile = "{$src}/{$file}";
                $dstFile = "{$dst}/{$file}";
                if ( is_dir( $srcFile ) ) {
                    copyDir( $srcFile, $dstFile );
                } else {
                    copy( $srcFile, $dstFile );
                }
            }
            closedir( $dir );
            return true;
        }
    }
    /**
     * 时间值转换
     * - [number|string]|null:转换内容
     * - 转换内容为 null 则输出当前时间
     * return [string]:格式化时间
     */
    if ( !function_exists( 'toDate' ) ) {
        function toDate( $time = null ) {
            if ( $time === null ) { return date( 'Y-m-d H:i:s' ); }
            if ( !empty( $time ) && is_numeric( $time ) ) { return date( 'Y-m-d H:i:s', $time ); }
            $dt = DateTime::createFromFormat( "Y-m-d\TH:i:s.u\Z", $time, new DateTimeZone("UTC") );
            if ( !$dt ) { return $time; }
            $dt->setTimezone( new DateTimeZone( date_default_timezone_get() ) );
            return $dt->format( "Y-m-d H:i:s" );
        };
    }
    /**
     * 参数序列化
     * - [mixed]:序列化值, [boolean]:是否为保存模式
     * return [mixed]:序列化或反序列化后的参数
     */
    if ( !function_exists( 'toValue' ) ) {
        function toValue( $value, $save ) {
            if ( $save ) {
                if ( is_bool( $value ) ) { return $value ? '[:true:]' : '[:false:]'; }
                if ( is_null( $value ) ) { return '[:null:]'; }
                if ( is_array( $value ) ) { return json_encode( $value, JSON_UNESCAPED_UNICODE ); }
                if ( is_numeric( $value ) ) { return (string)$value; }
                if ( !is_string( $value ) ) { return ''; }
            }else {
                if ( $value === '[:true:]' ) { return true; }
                if ( $value === '[:false:]' ) { return false; }
                if ( $value === '[:null:]' ) { return null; }
                if ( is_numeric( $value ) ) { return strpos( $value, '.' ) !== false ? floatval( $value ) : intval( $value ); }
                if ( is_json( $value ) ) { return json_decode( $value, true ); }
            }
            return $value;
        }
    }
    /**
     * 字符串转数组
     * - [string]:转换字符串
     * return [array]数组
     */
    if ( !function_exists( 'toArray' ) ) {
        function toArray( $str ) {
            if ( !is_string( $str ) ) { return []; }
            $result = [];
            foreach ( explode( '|', $str ) as $item ) {
                [ $key, $value ] = explode( ':', $item, 2 );
                if ( empty( $value ) && $value !== 0 && $value !== '0' ) { $value = true; }
                if ( $value === 'true' ) {
                    $value = true;
                }elseif ( $value === 'false' ) {
                    $value = false;
                }elseif ( $value === 'null' ) {
                    $value = null;
                }elseif ( is_numeric( $value ) ) {
                    $value = $value + 0;
                }
                $result[$key] = $value;
            }
            return is_array( $result ) ? $result : [];
        }
    }
    /**
     * 判断内容是否以指定内容开始
     * - [string]:内容, [string]:开始的参数
     * return [boolean]:判断结果
     */
    if ( !function_exists( 'startWith' ) ) {
        function startWith( $string, $prefix ) {
            if ( !is_string( $string ) || !is_string( $prefix ) ) { return false; }
            return substr( $string, 0, strlen( $prefix ) ) === $prefix;
        }
    }
    /**
     * 判断内容是否以指定内容结束
     * - [string]:内容, [string]:结束的参数
     * return [boolean]:判断结果
     */
    if ( !function_exists( 'endWith' ) ) {
        function endWith( $string, $prefix ) {
            if ( !is_string( $string ) || !is_string( $prefix ) ) { return false; }
            return substr( $string, -strlen( $prefix ) ) === $prefix;
        }
    }
    /**
     * 加密一段内容
     * - [string]:加密文本, [string]|null:加密密钥
     * return [string|null]:加密后的内容
     */
    if ( !function_exists( 'encrypt' ) ) {
        function encrypt( $string, $key = null ) {
            if ( Bootstrap::$status && $key === null ) { // 驱动已注册时从本地加载 key
                $key = env( 'APP_KEY', 'DefaultKey' );
            }
            if ( empty( $key ) || !is_string( $key ) ) { return null; }
            return CryptoJSAES::encrypt( toString( $string ), $key );
        }
    }
    /**
     * 解密一段内容
     * - [string]:解密文本, [string]|null:解密密钥
     * return [string|null]:解密后的内容
     */
    if ( !function_exists( 'decrypt' ) ) {
        function decrypt( $string, $key = null ) {
            if ( Bootstrap::$status && $key === null ) { // 驱动已注册时从本地加载 key
                $key = env( 'APP_KEY', 'DefaultKey' );
            }
            if ( empty( $key ) || !is_string( $key ) ) { return null; }
            return CryptoJSAES::decrypt( toString( $string ), $key );
        }
    }
    /**
     * 哈希一个参数
     * - [string]:传入的内容
     * - return [string|null]:返回哈希后的字符串或 null
     */
    if ( !function_exists( 'h' ) ) {
        function h( $string ) {
            $key = '';
            if ( Bootstrap::$status ) { // 驱动已注册时从本地加载 key
                $key = env( 'APP_KEY', 'DefaultKey' );
            }
            return hash( 'sha256', toString( $string ).$key );
        }
    }
    /**
     * 调试参数
     * - [mixed]:传入的值, [bool]|true:是否退出程序
     * return void
     */
    if ( !function_exists( 'dd' ) ) {
        function dd( $val, $exit = true ) {
            $echo = $val;
            if ( is_json( $val ) ) {
                $echo = "Json ".print_r( json_decode( $val ), true);
            }else if ( is_uuid( $val ) ) {
                $echo = "UUID( '{$val}' )";
            }else if ( is_string( $val ) ) {
                $echo = "String( '{$val}' )";
            }else if ( is_numeric( $val ) ) {
                $echo = "Number( '{$val}' )";
            }else if ( is_bool( $val ) ) {
                $val = $val ? 'true' : 'false'; $echo = "Boolean( '{$val}' )";
            }else if ( $val === null ) {
                $echo = "Null( '' )";
            }
            print_r( $echo ); echo PHP_EOL.PHP_EOL; if ( $exit ) { exit(); }
        }
    }
    /**
     * 判断是否为公开方法
     * - [object]:对象, [string]:方法名称
     * return [boolean]:判断结果
     */
    if ( !function_exists( 'isPublic' ) ) {
        function isPublic( $object, $method ) {
            return ( method_exists( $object, $method ) && !(new \ReflectionMethod( $object, $method ))->isPrivate() );
        }
    }
    /**
     * 获取 TCore PHP 组件目录
     * return [string]:组件目录
     */
    function TCorePath() { return 'vendor/linnbenson/tcore-php/'; }