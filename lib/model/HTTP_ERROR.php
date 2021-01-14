<?php
namespace Model;

class HTTP_ERROR extends DBModel
{
    protected $PrimaryID = "ID";
//    public $SelectColumn = 'ID,Method,Uri,MID,Msg,BodyJson,ErrorDetail,IP,NewTime';
    public $SelectColumn = '';
}