<?php
namespace Model;

use Service\PubFunction;
use Model\MemberPlugin;

class Member extends DBModel
{
    use MemberPlugin;
    protected $PrimaryID = "MID";
//    public $SelectColumn = 'MID,AID,CID,Account,Password,RealName,Remark,Quota,PointMax,DefaultPort,Music,Sound,Video,LiveAudio,Audio,Commission,AutoSubmit,VideoSource,Currency,ValidRate,Status,BetStatus,ParentChangeStatus,ParentChangeBetStatus,Deleted,LoginErrorCount,LoginTime,LockTime,isLock,NewTime,UPTime';
    public $SelectColumn = '';

    public $_Message;
    public $_LoginErrorCode;
    public $_MID=0;
    public $_Token;

    public function login($_obj){
        $isSuccess = true;
        $Account = $_obj->Account;
        $Password = $_obj->Password;
        //帐号
        if (!$this->checkAccountFormat($Account)) {
            $this->_Message = "登入错误!!001";
            $this->_LoginErrorCode = 3;
            $isSuccess = false;
        }
        //密码
        if (!$this->checkPasswordFormat($Password)) {
            $this->_Message = "登入错误!!002";
            $this->_LoginErrorCode = 4;
            $isSuccess = false;
        }
        //驗證帳號
        $EncodePassword = $this->PasswordEncode($Password);
        $this->SelectColumn="MID,Password,Status,LoginErrorCount";
        $LoginData = $this->getData("CID=" . (int)$_obj->CID . " AND AID=".(int)$_obj->AID." AND Account='".$Account."' AND Deleted='N' ");
        $this->_MID = $LoginData["MID"];
        $Status = $LoginData["Status"];
        $Password = $LoginData["Password"];
        $LoginErrorCount = $LoginData["LoginErrorCount"];

        if ((int)$this->_MID == 0) {
            $this->_Message = "帐号错误!!";
            $this->_LoginErrorCode = 3;
            $isSuccess = false;
        } else {
            if ($Status == "N") {
                $this->_Message = "帐号已停用!!";
                $this->_LoginErrorCode = 5;
                $isSuccess = false;
            }
        }

        if ($EncodePassword !== $Password) {
            $this->_Message = "密码错误!!";
            $this->_LoginErrorCode = 4;
            $isSuccess = false;
            if ($LoginErrorCount >= 4) {
                //密碼錯誤5次以上鎖帳號
                $sql = "UPDATE Member SET isLock='Y',LockTime='" . $this->Datetime->format("Y-m-d H:i:s") . "' WHERE MID=" . $this->_MID." AND CID=".(int)$_obj->CID ." AND AID=".(int)$_obj->AID;
                $this->_DB['M']->query($sql);
            }
        }

        if ($isSuccess == true) {
            //避免重複登入
            $this->kill($this->_MID);
            //寫 MemberToken 驗證Token
            $this->_Token = PubFunction::create_uuid();
            unset($data);
            $data['Token'] = $this->_Token;
            $data['Token2'] = substr($this->_Token,0,2);//Token前兩位
            $data['MID'] = $this->_MID;
            $data['CID'] = $_obj->CID;
            $data['Status'] = 1;
            $data['LoginTime'] = $this->Datetime->format("Y-m-d H:i:s");
            $sql = PubFunction::sql_insert("MemberToken", $data);
            $this->_DB['M']->query($sql);
            $sql = "UPDATE Member SET LoginErrorCount=0,isLock='N',LoginTime='" . $this->Datetime->format("Y-m-d H:i:s") . "' WHERE MID=" . $this->_MID." AND CID=".(int)$_obj->CID ." AND AID=".(int)$_obj->AID;
            $this->_DB['M']->query($sql);
            return true;
        } else {
            if ($this->_MID != 0) {
                $sql = "UPDATE Member SET LoginErrorCount=LoginErrorCount+1 WHERE MID=" . $this->_MID." AND CID=".(int)$_obj->CID ." AND AID=".(int)$_obj->AID;
                $this->_DB['M']->query($sql);
            }
            return false;
        }

    }

    //註銷會員的Token
    public function Kill($_MID)
    {
        $data['Status'] = 0;
        $sql = PubFunction::sql_update("MemberToken", $data, " MID=" . $_MID);
        if ($this->_DB['M']->Query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    //0:失敗 1:成功 2:失敗驗證碼錯誤 3:帳號錯誤 4:密碼錯誤 5:帳號停用
    public function addLoginRecord($_Obj,$useragent=null,$ip=null,$HTTP_HOST=null,$LoginTimeObj=null)
    {
        $addr = $ip?$ip:PubFunction::getIP()['REMOTE_ADDR'];
        unset($data);
        $data['CID'] = $_Obj->CID;
        $data['MID'] = $_Obj->MID;
        $data['Account'] = addslashes($_Obj->Account);
        $data['LoginTime'] = $LoginTimeObj?$LoginTimeObj->format("Y-m-d H:i:s"):"now()";
        $data['Password'] = addslashes($_Obj->Password);
        $data['IsSucceed'] = $_Obj->IsSucceed;
        $data['Token'] = $_Obj->Token;
        $data['Message'] = $_Obj->Message;
        $data['RemoteAddr'] = PubFunction::ip2long_v4v6($addr);
        $data['HTTP_HOST'] = $HTTP_HOST?$HTTP_HOST:$_SERVER['HTTP_HOST'];
        $data['Client'] = $this->checkClient($useragent);
        $oMemberLoginRecord = new MemberLoginRecord($this->_DB);
        $oMemberLoginRecord->create($data);
    }

    public function getMIDFromAccount($_Account,$CIDArray)
    {
        $sql = "SELECT MID,CID,AID FROM Member WHERE Account='" . $_Account . "' AND CID IN ('".join("','",$CIDArray)."') ";
        $this->_SQL = $sql;
        $this->_DB['S']->query($sql, true);
        $MID = (int)$this->_DB['S']->f('MID');
        $CID = (int)$this->_DB['S']->f('CID');
        $AID = (int)$this->_DB['S']->f('AID');
        return [$CID,$MID,$AID];
    }

    //判断客户端登录
    function checkClient($useragent=null)
    {
        $useragent=$useragent?$useragent: $_SERVER['HTTP_USER_AGENT'];
        $useragent_commentsblock = preg_match('|\(.*?\)|', $useragent, $matches) > 0 ? $matches[0] : '';

        $mobile_os_list = array('Google Wireless Transcoder', 'Windows CE', 'WindowsCE', 'Symbian', 'Android', 'armv6l', 'armv5', 'Mobile', 'CentOS', 'mowser', 'AvantGo', 'Opera Mobi', 'J2ME/MIDP', 'Smartphone', 'Go.Web', 'Palm', 'iPAQ');
        $mobile_token_list = array('Profile/MIDP', 'Configuration/CLDC-', '160×160', '176×220', '240×240', '240×320', '320×240', 'UP.Browser', 'UP.Link', 'SymbianOS', 'PalmOS', 'PocketPC', 'SonyEricsson', 'Nokia', 'BlackBerry', 'Vodafone', 'BenQ', 'Novarra-Vision', 'Iris', 'NetFront', 'HTC_', 'Xda_', 'SAMSUNG-SGH', 'Wapaka', 'DoCoMo', 'iPhone', 'iPod');

        $found_mobile = $this->CheckSubstrs($mobile_os_list, $useragent_commentsblock) ||
            $this->CheckSubstrs($mobile_token_list, $useragent);

        if ($found_mobile) {
            return 'H5';
        } else {
            return 'PC';
        }
    }
    function CheckSubstrs($substrs, $text)
    {
        foreach ($substrs as $substr)
            if (false !== strpos($text, $substr)) {
                return true;
            }
        return false;
    }

    public function getToken()
    {
        return $this->_Token;
    }

    public $_LockSeconds = 900;//解鎖需要的秒數
    public $_LocksDiffSeconds = 0;
    public function isLock($_Obj)
    {
        $sql = "SELECT isLock,LockTime FROM Member WHERE CID=" . $_Obj->CID . " AND MID='" . $_Obj->MID . "'";
        $this->_SQL = $sql;
        $this->_DB['S']->query($sql, true);
        $isLock = $this->_DB['S']->f('isLock');
        $LockTime = $this->_DB['S']->f('LockTime');
        if ($isLock == 'Y') {
            $this->_LocksDiffSeconds = $this->Datetime->getTimestamp() - strtotime($LockTime);
            if ($this->_LocksDiffSeconds > $this->_LockSeconds) {
                //超過15分 解除封鎖
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public $ActiveTimeGap = 500;//當 上次活動時間 間隔 超過500秒 就更新活動時間。
    public function checkAuth($_Token)
    {
        $_Token = addslashes($_Token);
        $sql = "SELECT MID,ActiveTime FROM MemberToken WHERE Token2='".substr($_Token,0,2)."' AND Status='1' AND Token='" . $_Token . "' ";
        $this->_DB['S']->query($sql, true);
        $MID = $this->_DB['S']->f('MID');
        $ActiveTime = $this->_DB['S']->f('ActiveTime');
        if ($MID=="") {
            $this->_DB['M']->query($sql, true);
            $MID = $this->_DB['M']->f('MID');
            $ActiveTime = $this->_DB['S']->f('ActiveTime');
        }
        if ($MID!="") {
            //如果大於500秒，更新存活時間
            if( ( time() - strtotime($ActiveTime) ) > $this->ActiveTimeGap ){
                $this->updateActiveTime($MID,$_Token);
            }

            return $MID;
        } else {
            return false;
        }
    }
    //更新存活時間
    public function updateActiveTime($_MID,$_Token){
        $Data = ["ActiveTime"=>date('Y-m-d H:i:s'),];
        $sql = PubFunction::sql_update("MemberToken", $Data, " MID=" . $_MID." AND Token2='".substr($_Token,0,2)."' AND Status='1' AND Token='" . $_Token . "' ");
        $this->_DB['M']->Query($sql);
    }
    //修改密碼
    public function changePassword($newPassword,$MID,$AID,$CID){
        if(!$this->checkPasswordFormat($newPassword)){
            return false;
        }
        $Data = ["Password"=>$this->PasswordEncode($newPassword)];
        $rs = $this->update($Data," MID='".(int)$MID."' AND AID='".(int)$AID."' AND CID=".(int)$CID." ");
        return $rs;
    }

}