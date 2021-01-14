<?php

namespace Service;

use Respect\Validation\Exceptions\ValidationException;
use DateTime;
use Exception;
use Model\HTTP_ERROR;

class ResData
{
    //回應JSON
    static public function successJson($_JSON){
        $_JSON['code']=200;
        $end_memory=memory_get_usage();
        $end_time=microtime(true);
        global $start_memory;
        global $start_time;
        $cost_memory=$end_memory-$start_memory;
        $cost_time=$end_time-$start_time;
        $_JSON['cost memory']=round($cost_memory/1024)."k";
        $_JSON['cost time']=round($cost_time);
        
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($_JSON);
    }
    //回應彈出視窗和跳轉
    static public function alert_redirect($ResTxt,$Path){
        return "<script>alert('".$ResTxt."');location.href='".$Path."';</script>";
    }
    static public function fail($_JSON,$error_code=500,$ErrorDetail=""){
        //ErrorMsg
        $_JSON['code']=$error_code;
        $end_memory=memory_get_usage();
        $end_time=microtime(true);
        global $start_memory;
        global $start_time;
        $cost_memory=$end_memory-$start_memory;
        $cost_time=$end_time-$start_time;
        $_JSON['cost memory']=round($cost_memory/1024)."k";
        $_JSON['cost time']=round($cost_time);
        //Res
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($_JSON);
    }
    static public function failException(Exception $e,$error_code="",$MID=null,$request=null,$response=null,$Datetime=null,$BodyJson=null,$DB_CASINO=null){
        //ErrorMsg
        $_JSON['code']=$error_code==""?$e->getCode():$error_code;
        $_JSON['msg']=$e->getMessage();
        if(DEBUG_SHOW) $_JSON['dev_error_detail']=$e->getTraceAsString();
        //錯誤紀錄寫入DB

        if(HTTP_ERROR_TO_DB){
            //Data
            $Method = $request?$request->server["request_method"]:$_SERVER['REQUEST_METHOD'];
            $URI = $request?$request->server["request_uri"]:$_SERVER['REQUEST_URI'];
            $IP = $request?$request->server["remote_addr"]:PubFunction::getIP()["REMOTE_ADDR"];
            $Datetime = $Datetime?$Datetime:new DateTime();
            //Insert
            $oHTTP_ERROR = new HTTP_ERROR($DB_CASINO);
            $Data = [
                "Method" => $Method,
                "Uri" => $URI,
                "MID" => $MID,
                "Msg" => $e->getMessage(),
                "BodyJson" => $BodyJson,
                "ErrorDetail" => $_JSON['code']=="502"?"":$e->getTraceAsString(),//SQL ERROR不紀錄細項
                "IP" => $IP,
                "NewTime" => $Datetime->format("Y-m-d H:i:s"),
            ];
            $oHTTP_ERROR->create($Data);
        }
        //Res
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($_JSON);
    }
    //資料驗證錯誤
    static public function failValException(ValidationException $e){
        //ErrorMsg
        $_JSON['code']=400;
        $_JSON['msg']=$e->getMessage();
        $_JSON['dev_error_detail']=$e->getTraceAsString();
        //Res
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($_JSON);
    }

}