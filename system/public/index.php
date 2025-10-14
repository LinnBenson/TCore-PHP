<?php
    use TCore\Bootstrap;

    // 重置工作目录
    chdir( dirname( getcwd() ) );
    // 加载 Composer
    require_once 'vendor/autoload.php';
    // 加载驱动
    echo Bootstrap::install(function() {
        return 'aaa';
    });