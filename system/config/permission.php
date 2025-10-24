<?php
    /**
     * 权限介入声明
     */
    return [
        // 系统启动运行
        'SYSTEM_STARTUP' => [
            'Custom'
        ],
        // 修改或监听系统启动返回结果
        'RETURN_RESULT' => [
            'Custom'
        ],
        // 修改配置查询信息
        'QUERY_CONFIGURATION_INFORMATION' => [
            'Custom'
        ],
        // 挂载语言包
        'QUERY_LANGUAGE_PACKAGE' => [
            'Custom'
        ],
        // 监听初始化请求
        'REQUEST_INITIALIZATION_COMPLETED' => [
            'Custom'
        ],
        // 修改或监听接口返回数据
        'RETURN_INTERFACE_DATA' => [
            'Custom'
        ]
    ];