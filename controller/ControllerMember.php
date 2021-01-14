<?php
namespace Controller;

use Service\Middleware;
use Service\ResData;
use Model\MemberCash;
use Model\Member;
use Model\MemberChip;
use Model\MemberPort;
use Model\GamePortConfig;
use Model\GamePortPlayCode;
use Model\CtlRecord;
use Exception;

class ControllerMember
{
    private $oMiddleware;
    private $CID=0;
    private $DB_CASINO=[];
    private $DB_CTL=[];

    public function __construct(Middleware $Middleware){
        $this->oMiddleware = $Middleware;
        $this->oMiddleware->getReqJsonData();//抓取POST
        $this->oMiddleware->ValBearerToken();//驗證Token
        $this->oMiddleware->getUserInfo();//根據Token抓member info
        //CID
        $this->CID = $this->oMiddleware->MemberInfo["CID"];
        //資料庫注入
        $this->DB_CASINO["M"] = $this->oMiddleware->DB_CASINO["M"];
        $this->DB_CASINO["S"] = $this->oMiddleware->DB_CASINO["S"];
        $this->DB_CTL["M"] = $this->oMiddleware->DB_CTL["M"];
        $this->DB_CTL["S"] = $this->oMiddleware->DB_CTL["S"];
    }
    //登入會員取得資訊
    public function getInfo(){

    }

}