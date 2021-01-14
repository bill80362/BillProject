<?php
namespace Service;

use Model\DBconn;
use DateTime;
use Service\PubFunction;
use Service\DeviceDetector;
use Model\Member;
use Exception;

class Middleware
{
    //注入參數
    public $request;
    public $response;
    public $Datetime;
    public $CID;
    public $Tpl;
    //
    public $HeaderAuth;
    public $BodyJson;
    public $Token;
    public $MID;
    public $MemberInfo;

    public $DB_CASINO=[];
    public $DB_CTL=[];

    public function __construct($request=null,$response=null,$Datetime=null,$DB_CASINO=null,$DB_CTL=null){
        $this->Datetime = $Datetime?$Datetime:new DateTime();
        //Tpl
        $oTpl = new \Service\Tpl();
        $this->Tpl = $oTpl->getTpl($_SERVER["HTTP_HOST"]);
        if($request){
            $this->HeaderAuth = $request->header["authorization"];
            $this->BodyJson = $request->rawContent();
            $this->request = $request;
            $this->response = $response;
        }
        //資料庫注入
        $this->DB_CASINO["M"] = $DB_CASINO["M"]?$DB_CASINO["M"]:new DBconn(DB_HOST_M, DB_USER_M, DB_PASSWD_M, DB_NAME);
        $this->DB_CASINO["S"] = $DB_CASINO["S"]?$DB_CASINO["S"]:new DBconn(DB_HOST_S, DB_USER_S, DB_PASSWD_S, DB_NAME);
        $this->DB_CTL["M"] = $DB_CTL["M"]?$DB_CTL["M"]:new DBconn(CTL_DB_HOST_M, CTL_DB_USER_M, CTL_DB_PASSWD_M, CTL_DB_NAME);
        $this->DB_CTL["S"] = $DB_CTL["S"]?$DB_CTL["S"]:new DBconn(CTL_DB_HOST_S, CTL_DB_USER_S, CTL_DB_PASSWD_S, CTL_DB_NAME);
    }

}