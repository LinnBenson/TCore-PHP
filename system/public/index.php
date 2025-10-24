<?php
    use TCore\Bootstrap;
    use TCore\Provider\HttpProvider;

    // 重置工作目录
    chdir( dirname( getcwd() ) );
    // 加载 Composer
    require_once 'vendor/autoload.php';
    // 加载驱动
    echo Bootstrap::register(function() {
        return HttpProvider::init();
    });