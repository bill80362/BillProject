<?php
namespace Service;

class Tpl
{
    public $List;
    public function __construct(){
        $this->List = require SITE_PATH."/config/config_tpl.php";
    }
    public function getTpl($Domain){
        $SecondLevelDomain = $this->transSecondLevelDomain($Domain);
        return $this->List[$SecondLevelDomain];
    }
    public function transSecondLevelDomain($Domain){
        //referer to domain
        $Domain = str_replace('http://',"",$Domain);
        $Domain = str_replace('https://',"",$Domain);
        $Domain = explode("/",$Domain);
        $DomainArray = explode(".",$Domain[0]);
        $DomainArray = array_reverse($DomainArray);
        $SecondLevelDomain = $DomainArray[1].".".$DomainArray[0];
        return $SecondLevelDomain;
    }

}