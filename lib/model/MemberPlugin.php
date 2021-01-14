<?php
namespace Model;

trait MemberPlugin
{
    /**
     * 檢查密碼是否輸入正確
     * 密碼規則：須為6~12碼英數字夾雜且符合0~9及a~z字,特殊字元可包含 _ . ! @ # $ & * + = | 和不可在黑名單密碼內
     */
    public function checkPasswordFormat($_password) {
        if($_password=="") return false;
        $rule="/^(?=.*[a-z|A-Z])(?=.*\d)[A-Za-z\d_.!@#$&*+=|]{6,18}$/";
        if(!preg_match($rule,$_password)){
            $this->_Message="密碼規則:須為6~18碼英數字夾雜且符合0~9及a~z字,特殊字元可包含 _ . ! @ # $ & * + = | ";
            return false;
        }
        return true;
    }

    public function checkAccountFormat($_Account){
        $eregstr = "/^[\d\w]{4,20}$/";
        if (!preg_match($eregstr, $_Account)) {
            $this->_Message = "帳號請輸入4~20位英文數字以上";
            return false;
        }
        return true;
    }

    public function checkRealNameFormat($_RealName){
        $eregstr = "/^[\x{4e00}-\x{9fa5}]+$/u";
        if (!preg_match($eregstr, $_RealName)) {
            $this->_Message = "真實姓名僅能輸入中文";
            return false;
        }
        return true;
    }

    /**
     * 密碼明碼加密
     */
    public function PasswordEncode($_pwd) {
    }

    /**
     * 密碼暗碼解密
     */
    public function PasswordDDecode($_pwd) {
    }
}