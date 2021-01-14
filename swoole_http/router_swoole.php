<?php
$start_memory=memory_get_usage();
$start_time=microtime(true);

//載入Composer套件
include __DIR__."/../config/config_inc.php";

//路由套件:https://github.com/nikic/FastRoute
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    //登入頁面
    $r->addRoute('GET', '/', 'ControllerMemberLogin@login');//Login View
    //使用者
    $r->addGroup('/api', function (FastRoute\RouteCollector $r) {
        /** 需要驗證Token【有會員身分】 ***/
        //會員
        $r->addRoute('GET', '/member', 'ControllerMember@getInfo');//取得自己的會員資料
        //跑馬燈
        $r->addRoute('GET', '/marquee', 'ControllerMarquee@get');//
    });

});


//HTTP伺服器 入口設定
$URL = "xxx.com.net";
$PORT = 8088;
$http = new Swoole\Http\Server($URL, $PORT);
$http->set([
    'worker_num' => 12,
    'dispatch_mode' => 1,//1:轮循模式 3:抢占模式
]);

$DB_CASINO["M"] = new DBconn(DB_HOST_M, DB_USER_M, DB_PASSWD_M, DB_NAME);
$DB_CASINO["S"] = new DBconn(DB_HOST_S, DB_USER_S, DB_PASSWD_S, DB_NAME);
$DB_CTL["M"] = new DBconn(CTL_DB_HOST_M, CTL_DB_USER_M, CTL_DB_PASSWD_M, CTL_DB_NAME);
$DB_CTL["S"] = new DBconn(CTL_DB_HOST_S, CTL_DB_USER_S, CTL_DB_PASSWD_S, CTL_DB_NAME);

$http->on("start", function ($server) use($URL,$PORT) {
    echo "Swoole http server is started at ".$URL.":".$PORT."\n";
    //ReLink Database
    $DB_CASINO["M"] = new DBconn(DB_HOST_M, DB_USER_M, DB_PASSWD_M, DB_NAME);
    $DB_CASINO["S"] = new DBconn(DB_HOST_S, DB_USER_S, DB_PASSWD_S, DB_NAME);
    $DB_CTL["M"] = new DBconn(CTL_DB_HOST_M, CTL_DB_USER_M, CTL_DB_PASSWD_M, CTL_DB_NAME);
    $DB_CTL["S"] = new DBconn(CTL_DB_HOST_S, CTL_DB_USER_S, CTL_DB_PASSWD_S, CTL_DB_NAME);

});

$http->on("request", function ($request, $response)use($http,$dispatcher,$DB_CASINO,$DB_CTL) {
    //重載 Swoole
    if( $request->get['act']=="reload" ){
        //Reload
        $http->reload();
        //Res
        $response->header("Content-Type", "application/json; charset=utf-8");
        $_JSON['code']=200;
        $_JSON['msg']="Swoole Reload!";
        $response->end(json_encode($_JSON));
    }

    /**** mem/log/POST 紀錄 START *****/
//    var_dump($request);
    $json_arr = json_decode($request->rawContent(), true);
    $jsonstr = '';
    $jsonstr .= date("H:i:s", time()) . "\t"
        . $request->server["remote_addr"] . " PORT:"
        . $request->server["remote_port"] . " ("
        . $request->server['http_x_forwarded_for'] . ")("
        . $request->server['http_client_ip'] . ")\t"
        . $request->header["authorization"] . " "
        . $request->server["request_uri"] . ":\n"
        . $request->header['host'] . "\nCookie:"
        . http_build_query((array)$request->cookie) . "\n"
        . $request->header['user_agent'] . "\n";
    if (is_array($json_arr)) {
        foreach ($json_arr as $_key => $_value) {
            $jsonstr .= "[$_key] = $_value\n";
        }
        txt_record('POST', $jsonstr,"POST");
    }else{
        txt_record('GET', $jsonstr,"GET");
    }
    unset($jsonstr);
    /**** mem/log/POST 紀錄 END *****/

    // 從$_SERVER取路徑(URI)和方法
    $httpMethod = $request->server["request_method"];
    $uri = $request->server["request_uri"];
    // 去除query string(?foo=bar) and decode URI
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);
    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

    $response->header("Access-Control-Allow-Origin", "*");
    $response->header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, Authorization,Tpl");
    $response->header("Access-Control-Allow-Methods", "PUT, PATCH, POST, GET, DELETE, OPTIONS");
    //讓OPTIONS通過
    if($httpMethod=="OPTIONS"){
        $response->header("Content-Type", "application/json; charset=utf-8");
        $_JSON['code']=200;
        $_JSON['msg']="OK";
        $response->end(json_encode($_JSON));
    }else{
        //分配路由狀態
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                //當uri路徑找不到
                $response->header("Content-Type", "application/json; charset=utf-8");
                $_JSON['code']=404;
                $_JSON['msg']="404 Not Found";
                $response->end(json_encode($_JSON));
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                // 當uri路徑找到，方法不對(GET POST PUT.....)
                $response->header("Content-Type", "application/json; charset=utf-8");
                $_JSON['code']=405;
                $_JSON['msg']="405 Method Not Allowed";
                $response->end(json_encode($_JSON));
            case FastRoute\Dispatcher::FOUND:
                //路徑、方法都對了，執行Controller
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                //自定義$handler 第一個參數是 string class@method 第二個之後是$vars
                list($class, $method) = explode('@',$handler,2);
                try{
                    $response->header("Content-Type", "application/json; charset=utf-8");
                    $oDatetime = new DateTime();
                    $oMiddle = new Middleware($request,$response,$oDatetime,$DB_CASINO,$DB_CTL);
                    $obj = new $class($oMiddle);//類別進行物件化
                    $ReturnData =  $obj->{$method}($vars);//傳入參數
                    $response->end($ReturnData);
                }catch (Exception $e){
                    //使用例外方式處理錯誤訊息
                    $ReturnData = ResData::failException($e,"",$oMiddle->MID,$request,$response,$oDatetime,print_r($oMiddle->ReqData,true),$oMiddle->DB_CASINO);
                    $response->header("Content-Type", "application/json; charset=utf-8");
                    $response->end($ReturnData);
                }
                break;
        }
    }

});

$http->start();

function txt_record($name, $wstr , $Dir)
{
    $today_post = time();
    if (!is_dir(SITE_PATH . "/log/Swoole/".$Dir)) {
        mkdir(SITE_PATH . "/log/Swoole/".$Dir, 0777);
    }
    $dir_date = SITE_PATH . "/log/Swoole/".$Dir."/" . date("Y-m-d", $today_post);
    $file_date = date("H", $today_post);
    if (!is_dir($dir_date)) {
        mkdir($dir_date, 0777);
    }
    $file_log = fopen($dir_date . "/" . $name . "_" . $file_date . ".log", "a");
    fwrite($file_log, $wstr . "\n");
    fclose($file_log);
}
