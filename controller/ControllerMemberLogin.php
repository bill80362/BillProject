<?php
namespace Controller;

use Service\Middleware;
use Service\ResData;
use Service\PubFunction;
use Service\DeviceDetector;
use Model\Member;
use Model\TplGroup;
use Model\CtlRecord;
use Exception;

class ControllerMemberLogin
{
    private $oMiddleware;
    private $CID=0;
    private $DB_CASINO=[];
    private $DB_CTL=[];
    private $Tpl;

    public function __construct(Middleware $Middleware){
        $this->oMiddleware = $Middleware;
        $this->oMiddleware->getReqJsonData();
        //CID
        $this->Tpl = $this->oMiddleware->Tpl;
        //資料庫注入
        $this->DB_CASINO["M"] = $this->oMiddleware->DB_CASINO["M"];
        $this->DB_CASINO["S"] = $this->oMiddleware->DB_CASINO["S"];
        $this->DB_CTL["M"] = $this->oMiddleware->DB_CTL["M"];
        $this->DB_CTL["S"] = $this->oMiddleware->DB_CTL["S"];
    }
    public function login(){
        //template1會使用到變數
        $Tpl = $this->Tpl;
        //載入版型
        $ViewTpl = include VIEW_PATH."/".$this->oMiddleware->Tpl."/login/login.php";
        return $ViewTpl;
    }
    public function loginAction(){

    }
    public function changePassword(){

    }

}