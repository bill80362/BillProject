<?php

namespace Service;
//https://packagist.org/packages/matomo/device-detector
class DeviceDetector
{
    public function get($HTTP_USER_AGENT){
        $oMobile_Detect = new \Mobile_Detect(["HTTP_USER_AGENT"=>$HTTP_USER_AGENT]);
        if($oMobile_Detect->isMobile()){
            return "M";
        }elseif($oMobile_Detect->isTablet()){
            return "Tab";
        }else{
            return "PC";
        }
    }
    public function isMobile($HTTP_USER_AGENT){
        $oMobile_Detect = new \Detection\MobileDetect(["HTTP_USER_AGENT"=>$HTTP_USER_AGENT]);
        if( $oMobile_Detect->isMobile() || $oMobile_Detect->isTablet() ){
            return true;
        }else{
            return false;
        }

    }
}