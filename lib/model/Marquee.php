<?php
namespace Model;

class Marquee extends DBModel
{
    protected $PrimaryID = "NoticeID";
//    public $SelectColumn = 'NoticeID,CID,Title,Content,ShowAgent,ShowMember,StartTime,EndTime,Status,CreateMan,UpdateMan,NewTime,UpTime,Deleted	';
    public $SelectColumn = '';

    public function getNewest($CID){
        return $this->getData(" CID='".$CID."' AND Status='Y' AND Deleted='N' AND ShowMember='Y' AND StartTime<='".$this->Datetime->format("Y-m-d H:i:s")."' AND EndTime>='".$this->Datetime->format("Y-m-d H:i:s")."' Order BY NoticeID DESC LIMIT 1 ");
    }

}