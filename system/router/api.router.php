<?php
    use TCore\Controller\BaseController;
    use TCore\Handler\Router;

    Router::add( '/base/index' )->controller([ BaseController::class, 'index' ])->save();
    Router::add( '/base/debug' )->controller([ BaseController::class, 'debug' ])->save();