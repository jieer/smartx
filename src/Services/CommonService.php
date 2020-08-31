<?php
/**
 * Created by PhpStorm.
 * User: smartwell
 * Date: 2018/10/16
 * Time: 上午11:14
 */

namespace Smartwell\Services;

use DateTime;

class CommonService
{
    //生成一个随机数
    public static function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //格式化时间,格式为2016-07-07T23:48:43Z
    public static function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }

    //手机号验证
    public static function verifPhone($phone)
    {
        $preg_phone = '/^1[345789]\d{9}$/ims';
        if (preg_match($preg_phone, $phone)) {
            return true;
        } else {
            return false;
        }
    }

    //身份证格式校验
    public static function verifyIdCard($id_card)
    {
        //老身份证长度15位，新身份证长度18位
        $length = strlen($id_card);
        if ($length == 15) { //如果是15位身份证
            //15位身份证没有字母
            if (!is_numeric($id_card)) {
                return false;
            }
            // 省市县（6位）
            $areaNum = substr($id_card, 0, 6);
            // 出生年月（6位）
            $dateNum = substr($id_card, 6, 6);

        } else if ($length == 18) { //如果是18位身份证

            //基本格式校验
            if (!preg_match('/^\d{17}[0-9xX]$/', $id_card)) {
                return false;
            }
            // 省市县（6位）
            $areaNum = substr($id_card, 0, 6);
            // 出生年月日（8位）
            $dateNum = substr($id_card, 6, 8);

        } else { //假身份证
            return false;
        }

        //验证地区
        if (!self::isAreaCodeValid($areaNum)) {
            return false;
        }

        //验证日期
        if (!self::isDateValid($dateNum)) {
            return false;
        }

        //验证最后一位
        if (!self::isVerifyCodeValid($id_card)) {
            return false;
        }

        return true;
    }

    /**
     * 省市自治区校验
     * @param string $area 省、直辖市代码
     * @return bool
     */
    private static function isAreaCodeValid(string $area) {
        $provinceCode = substr($area, 0, 2);

        // 根据GB/T2260—999，省市代码11到65
        if (11 <= $provinceCode && $provinceCode <= 65) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证出生日期合法性
     * @param string $date 日期
     * @return bool
     */
    private static function isDateValid(string $date) {
        if (strlen($date) == 6) { //15位身份证号没有年份，这里拼上年份
            $date = '19'.$date;
        }
        $year  = intval(substr($date, 0, 4));
        $month = intval(substr($date, 4, 2));
        $day   = intval(substr($date, 6, 2));

        //日期基本格式校验
        if (!checkdate($month, $day, $year)) {
            return false;
        }

        //日期格式正确，但是逻辑存在问题(如:年份大于当前年)
        $currYear = date('Y');
        if ($year > $currYear) {
            return false;
        }
        return true;
    }

    /**
     * 验证18位身份证最后一位
     * @param string $num 待校验的身份证号
     * @return bool
     */
    private static function isVerifyCodeValid(string $num)
    {
        if (strlen($num) == 18) {
            $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            $tokens = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

            $checkSum = 0;
            for ($i = 0; $i < 17; $i++) {
                $checkSum += intval($num{$i}) * $factor[$i];
            }

            $mod   = $checkSum % 11;
            $token = $tokens[$mod];

            $lastChar = strtoupper($num{17});

            if ($lastChar != $token) {
                return false;
            }
        }
        return true;
    }



    //根据身份证号获取性别
    // 1男 2女
    public static function getSexByIdCard($id_card) {
        return substr($id_card, -2, 1) % 2 == 1 ? '1' : '2';
    }

    //获取身份证中的出生日期
    public static function getBirthByIdCard($id_card, $format = 'Ymd') {
        $birth =  substr($id_card, 6, 8);
        if (strtotime($birth) > 0) {
            return date($format, strtotime($birth));
        }
        return false;
    }


}
