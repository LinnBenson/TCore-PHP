<?php
    return [
        'add' => [
            // 增加基础文件
            'public/index.php'
        ],
        'update' => [
            TCorePath().'README.md' => 'README.md'
        ],
        'delete' => [
            // 删除根目录可能存在的默认文件
            'index.html',
            'index.htm',
            'index.php',
            'robots.txt',
            'favicon.ico',
        ],
        'path' => [
            // 维持基础目录存在
            'app/controller',
            'app/service',
            'app/model'
        ]
    ];