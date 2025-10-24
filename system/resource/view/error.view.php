<html lang="">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="Cache-Control" content="no-siteapp">
        <title><?=$code?> - <?=$msg?></title>
        <style>
            * { margin: 0; padding: 0; font-size: 15px; }
            div#content {
                display: flex;
                width: 100%;
                height: 100vh;
                align-items: center;
                justify-content: center;
                -webkit-display: flex;
                -webkit-align-items: center;
                -webkit-justify-content: center;
            }
            div#content div.info { padding-bottom: 120px; }
            div#content div.info, div#content div.info * {
                font-weight: bold;
                font-size: 18px;
            }
            div#content div.info p {
                display: inline-block;;
            }
            div#content div.info p:first-of-type {
                margin-right: 8px;
            }
            div#content div.info p:last-of-type {
                margin-left: 8px;
            }
        </style>
    </head>
    <body>
        <div id="content" class="center">
            <div class="info">
                <p><?=$code?></p>|<p><?=$msg?></p>
            </div>
        </div>
    </body>
</html>