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

        /** 不需驗證Token【有會員身分】 ***/
        $r->addRoute('POST', '/login', 'ControllerMemberLogin@loginAction');//登入
        $r->addRoute('POST', '/change_password', 'ControllerMemberLogin@changePassword');//修改密碼
        $r->addRoute('GET', '/avatar/{Image:.*}', 'ControllerAvatar@getAvatar');//頭像

        /** 需要驗證Token【有會員身分】 ***/
        //會員
        $r->addRoute('GET', '/member', 'ControllerMember@getInfo');//取得自己的會員資料
        //跑馬燈
        $r->addRoute('GET', '/marquee', 'ControllerMarquee@get');//
    });


});

//路由套件設定區 START
if(true){

    /**** mem/log/POST 紀錄 START *****/
    $json_arr = json_decode(file_get_contents("php://input"), 1);
    if (is_array($json_arr)) {
        $jsonstr = '';
        $jsonstr .= date("H:i:s", time()) . "\t"
            . "Token:".\Service\PubFunction::getBearerToken() . "\t\n"
            . "IP:".$_SERVER['REMOTE_ADDR']
            . "(" . $_SERVER['HTTP_X_FORWARDED_FOR'] . ")(" . $_SERVER['HTTP_CLIENT_IP'] . ") "
            . "PORT:". $_SERVER["REMOTE_PORT"] . "\t\n"
            . $_SERVER["HTTP_HOST"] . "\n"
            . $_SERVER["SCRIPT_FILENAME"] . "\n"
            . "Cookie:".$_SERVER["HTTP_COOKIE"] . "\n"
            . $_SERVER["REQUEST_METHOD"]." ".$_SERVER["REQUEST_URI"] . "\n"
            . $_SERVER['HTTP_USER_AGENT'] . "\n"
            . "PostData: " ;
        $jsonstr .= print_r($json_arr,true);
        txt_record('JSON', $jsonstr);
        unset($jsonstr);
    }
    /**** mem/log/POST 紀錄 END *****/

    // 從$_SERVER取路徑(URI)和方法
    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    // 去除query string(?foo=bar) and decode URI
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);
    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
    //讓OPTIONS通過
    if($httpMethod=="OPTIONS"){
        header('Content-Type: application/json; charset=utf-8');
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization,Tpl");
        header('Access-Control-Allow-Methods: PUT, PATCH, POST, GET, DELETE, OPTIONS');
        $_JSON['code']=200;
        $_JSON['msg']="OK";
        echo json_encode($_JSON);
        exit();
    }
    //CORS
    header("Access-Control-Allow-Origin:*");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization,Tpl");
    header('Access-Control-Allow-Methods: PUT, PATCH, POST, GET, DELETE, OPTIONS');
    //HTTP Cache
//    header('Cache-Control: max-age=5');

    //分配路由狀態
    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            //當uri路徑找不到
            header('Content-Type: application/json; charset=utf-8');
            header('HTTP/1.1 404 Not Found');
            $_JSON['code']=404;
            $_JSON['msg']="404 Not Found";
            //測試沒有進去程式的初始記憶體使用量 START (看起來fastroute套件大概使用750K)
            $end_memory=memory_get_usage();
            $end_time=microtime(true);
            $cost_memory=$end_memory-$start_memory;
            $cost_time=$end_time-$start_time;
            $_JSON['cost memory']=round($cost_memory/1024)."k";
            $_JSON['cost time']=round($cost_time);
            //測試沒有進去程式的初始記憶體使用量 END
            echo json_encode($_JSON);
            break;
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            // 當uri路徑找到，方法不對(GET POST PUT.....)
            header('Content-Type: application/json; charset=utf-8');
            header('HTTP/1.0 405 Method Not Allowed');
            $_JSON['code']=405;
            $_JSON['msg']="405 Method Not Allowed";
            echo json_encode($_JSON);
            break;
        case FastRoute\Dispatcher::FOUND:
            //路徑、方法都對了，執行Controller
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            //自定義$handler 第一個參數是 string class@method 第二個之後是$vars
            list($class, $method) = explode('@',$handler,2);
            try{
                $oDatetime = new DateTime();
                $oMiddle = new Service\Middleware(null,null,$oDatetime);
                $class = 'Controller\\'.$class;
                $obj = new $class($oMiddle);//類別進行物件化
                echo $obj->{$method}($vars);//傳入參數
            }catch (Exception $e){
                echo Service\ResData::failException($e,"",$oMiddle->MID,null,null,$oDatetime,print_r($oMiddle->ReqData,true),$oMiddle->DB_CASINO);
            }
            break;
    }
}
//路由套件設定區 END

function txt_record($name, $wstr)
{
    $today_post = time();
    if (!is_dir(SITE_PATH . "/log/POST")) {
        mkdir(SITE_PATH . "/log/POST", 0777);
    }
    $dir_date = SITE_PATH . "/log/POST/" . date("Y-m-d", $today_post);
    $file_date = date("H", $today_post);
    if (!is_dir($dir_date)) {
        mkdir($dir_date, 0777);
    }
    $file_log = fopen($dir_date . "/" . $name . "_" . $file_date . ".log", "a");
    fwrite($file_log, $wstr . "\n");
    fclose($file_log);
}





