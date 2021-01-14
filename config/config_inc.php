<?php
//根目錄路徑位置
define("SITE_PATH",__DIR__."/..");

//載入Composer套件
include SITE_PATH."/vendor/autoload.php";

/***【系統設定】***/
define("SQL_SHOW","N");//每個SQL都顯示
define("DEBUG_SHOW",true);//輸出SQL_ERROR
define("DEBUG_CHECK_TIME",false);//程式中插入時間戳，檢測每個片段的程式執行時間
define("HTTP_ERROR_TO_DB",true);//接口錯誤寫入DB

/***【資料庫 CASINO】***/
include_once __DIR__."/../../config/dbip.php";//載入DB主機位置
define("DB_NAME","");
define("DB_HOST_M","");
define("DB_HOST_S","");
define("DB_USER_M","");
define("DB_USER_S","");
define("DB_PASSWD_M","");
define("DB_PASSWD_S","");
/***【資料庫 VCTL】***/
define("CTL_DB_NAME","");
define("CTL_DB_HOST_M","");
define("CTL_DB_HOST_S","");
define("CTL_DB_USER_M","");
define("CTL_DB_USER_S","");
define("CTL_DB_PASSWD_M","");
define("CTL_DB_PASSWD_S","");

/***【路徑】***/
define("VIEW_PATH",SITE_PATH."/view");//HTML樣板
define("AVATAR_PATH",SITE_PATH."/../vctl/www/upload/AccountImage/");//頭像路徑

?>
