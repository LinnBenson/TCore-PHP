<?php
    return [
        // 如果文件不存在，则增加文件
        'add' => [
            'config/app.php',
            'config/database.php',
            'config/permission.php',
            'public/index.php'
        ],
        // 强制复制文件
        'update_file' => [
            TCorePath().'README.md' => 'README.md',
            TCorePath().'system/plugin/Custom/config.php' => 'config/plugin/Custom.php'
        ],
        // 强制复制目录
        'update_dir' => [
            TCorePath().'system/plugin/Custom' => 'plugin/Custom'
        ],
        // 删除文件
        'delete_file' => [
            'index.html',
            'index.htm',
            'index.php',
            'robots.txt',
            'favicon.ico',
        ],
        // 删除目录
        'delete_dir' => [

        ],
        // 保持目录存在
        'path' => [
            // 维持基础目录存在
            'app/Controller',
            'app/Service',
            'app/Model',
            'storage/cache'
        ]
    ];