<?php
namespace Model;

use Service\PubFunction;
use DateTime;

abstract class DBModel
{
    public $Datetime;
    protected $TableName = "";
    protected $PrimaryID = "";
    public $_DB ;
    protected $InsertID;
    public $useMaster = false;//Select是否使用Master DB來取值
    public $SelectColumn = '*';//Override來修改DB Select的Column
    public $_SQL;

    function __construct($_DB,$Datetime=null)
    {
        //使用注入的時間
        $this->Datetime = $Datetime?$Datetime:new DateTime();
        //資料庫連線
        if($_DB['M']){
//            $this->_DB['M'] = new DBconn(DB_HOST_M, DB_USER_M, DB_PASSWD_M, DB_NAME);
            $this->_DB['M'] = $_DB["M"];
        }
        if($_DB['S']){
//            $this->_DB['S'] = new DBconn(DB_HOST_S, DB_USER_S, DB_PASSWD_S, DB_NAME);
            $this->_DB['S'] = $_DB["S"];
        }
        //DB資料表名稱與Class一致
        if($this->TableName==""){
//            $this->TableName = get_class($this);
            //要去除namespace
            $this->TableName = substr(strrchr('\\'.get_class($this), '\\'), 1);
        }
        //Primary Key = {TableName}.'ID'
        if($this->PrimaryID==""){
            $this->PrimaryID = $this->TableName."ID";
        }
    }
    public function setSelectColumn($ColumnText){
        $this->SelectColumn = $ColumnText;
    }
    public function getPrimaryID(){
        return $this->PrimaryID;
    }
    //全部列表
    public function all($Sql=""){
        $sql = "SELECT ".$this->SelectColumn." FROM `".$this->TableName."` ".$Sql;
        $this->_SQL = $sql;
        if($this->useMaster){
            $this->_DB['M']->query($sql);
            $List = $this->_DB['M'] -> get_total_data();
        }else{
            $this->_DB['S']->query($sql);
            $List = $this->_DB['S'] -> get_total_data();
        }
        return $List;
    }
    //根據ID找單筆資料
    public function find($_ID){
        $sql = "SELECT ".$this->SelectColumn." FROM ".$this->TableName." WHERE ".$this->PrimaryID."='".$_ID."'";//ID前面都要加上Table名稱
        $this->_SQL = $sql;
        if($this->useMaster){
            $this->_DB['M']->query($sql,true);
            $Data = $this->_DB['M']->record;
        }else{
            $this->_DB['S']->query($sql,true);
            $Data = $this->_DB['S']->record;
        }
        return $Data;
    }
    //新增
    public function create($_Data){
        $sql = PubFunction::sql_insert($this->TableName, $_Data);
        $this->_SQL = $sql;
        if($this->_DB['M']->query($sql)){
            $this->InsertID = $this->_DB['M']->insert_id;
            return true;
        }else{
            return false;
        }
    }
    //新增多筆
    public function createMultiple($_Array){
        $sql_array = [];
        foreach ($_Array as $key=>$_Data){
            $sql = PubFunction::sql_insert($this->TableName, $_Data);
            $sql_array[] = $sql;
            $this->_SQL = $sql;
        }
        if(count($sql_array)>0){
            if ($this->_DB['M']->TransationQuery($sql_array)) {
                $this->InsertID = $this->_DB['M']->insert_id;
                return true;
            } else {
                return false;
            }
        }
    }
    //取得剛剛新增的ID
    public function getInsertID(){
        return $this->InsertID;
    }
    //更新
    public function update($_Data,$_WHERE){
        $sql = PubFunction::sql_update($this->TableName, $_Data,$_WHERE);
        $this->_SQL = $sql;
        return $this->_DB['M']->query($sql);
    }
    //條件搜尋列表
    public function getList($_WHERE){
        $sql = "SELECT ".$this->SelectColumn." FROM ".$this->TableName." WHERE ".$_WHERE;
        $this->_SQL = $sql;
        if($this->useMaster){
            $this->_DB['M']->query($sql);
            $List = $this->_DB['M'] -> get_total_data();
        }else{
            $this->_DB['S']->query($sql);
            $List = $this->_DB['S'] -> get_total_data();
        }
        return $List;
    }
    //條件搜尋單一檔案
    public function getData($_WHERE){
        $sql = "SELECT ".$this->SelectColumn." FROM ".$this->TableName." WHERE ".$_WHERE;
        $this->_SQL = $sql;
        if($this->useMaster){
            $this->_DB['M']->query($sql,true);
            return $this->_DB['M']->record;
        }else{
            $this->_DB['S']->query($sql,true);
            return $this->_DB['S']->record;
        }
    }
    //條件搜尋列表 透過ID陣列
    public function getListbyID($_IDArray,$_field="",$_WHERE=""){
        if($_field=="")
            $_field = $this->PrimaryID;
        if($_WHERE!="")
            $_WHERE = " AND ".$_WHERE;
        if(count((array)$_IDArray)==0)
            return false;
        $sql = "SELECT ".$this->SelectColumn." FROM ".$this->TableName." WHERE ".$_field." IN ('".join("','",$_IDArray)."') ".$_WHERE;
        $this->_SQL = $sql;
        if($this->useMaster){
            $this->_DB['M']->query($sql);
            $List = $this->_DB['M'] -> get_total_data();
        }else{
            $this->_DB['S']->query($sql);
            $List = $this->_DB['S'] -> get_total_data();
        }
        return $List;
    }
    //條件搜尋列表 透過ID陣列
    public function delData($_WHERE){
        $sql = "DELETE FROM ".$this->TableName." WHERE ".$_WHERE;
        $this->_SQL = $sql;
        return $this->_DB['M']->query($sql);
    }
    //計算數量
    public function getCount($_WHERE){
        $sql = "SELECT COUNT(*) AS C FROM ".$this->TableName." WHERE ".$_WHERE;
        $this->_SQL = $sql;
        if($this->useMaster){
            $this->_DB['M']->query($sql,true);
            return $this->_DB['M']->record["C"];
        }else{
            $this->_DB['S']->query($sql,true);
            return $this->_DB['S']->record["C"];
        }

    }


}