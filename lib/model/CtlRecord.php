<?php
namespace Model;

use Service\PubFunction;

class CtlRecord extends DBModel
{
    protected $PrimaryID = "ID";
//    public $SelectColumn = 'ID,MenuKey,Account,UserID,UserType,CID,RemoteAddr,Content,MID,AID,PName,SqlCommand,HOST,NewTime,UPTime';
    public $SelectColumn = '';
    public $MenyKey = [

    ];
    //操作紀錄
    public function write($_MenuKey, $_Account, $_UserID, $_UserType, $_Content, $_sql_command = "", $_MID = "", $_AID = "", $_CID = "",$_PName="",$_HOST="",$_IP="")
    {
        if (is_array($_sql_command)) {
            $_sql_command = join(";", $_sql_command);
        }
        $_sql_command = str_replace("'", "", $_sql_command);
        $_Content = str_replace("'", "", $_Content);
        $addr = PubFunction::getIP();
        $data = array(
            'MenuKey' => $_MenuKey,
            'Account' => $_Account,
            'UserID' => $_UserID,
            'UserType' => $_UserType,
            'RemoteAddr' => $_IP?$_IP:$addr['REMOTE_ADDR'],
            'Content' => $_Content,
            'MID' => $_MID,
            'AID' => $_AID,
            'CID' => $_CID,
            'PName' => $_PName?$_PName:$_SERVER['REQUEST_URI'],
            'SqlCommand' => $_sql_command,
            'HOST' => $_HOST?$_HOST:$_SERVER['HTTP_HOST'],
        );
        $this->create($data);
    }


}