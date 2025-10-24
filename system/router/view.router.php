<?php
    use TCore\Handler\Request;
    use TCore\Handler\Router;

    Router::add( '/', 'GET' )->to(function( Request $request ) {
        return view( 'welcome', [ 'request' => $request ] );
    })->save();