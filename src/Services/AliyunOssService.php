<?php
namespace SmartX\Services;

use Storage;
use Intervention\Image\Facades\Image;

class AliyunOssService
{
    public static function signUrl($url, $condition = '',  $bucket_name = '', $cacheTimeout = 1800)
    {
        $ak = config('filesystems.disks.oss.access_id');
        $sk = config('filesystems.disks.oss.access_key');
        $domain = config('filesystems.disks.oss.cdnDomain');//图片域名或bucket域名
        $signTimeout = 60 * 30; // 20min
        $expire = time() + $signTimeout;
        if ($bucket_name == '') {
            $bucketName = config('filesystems.disks.oss.bucket');
        } else {
            $bucketName = $bucket_name;
        }
        if (!$domain) {
            $domain = $bucketName.'.'.config('filesystems.disks.oss.endpoint');//图片域名或bucket域名
        }
        // 或者"mulu/1.jpg@!样式名"  或者 mulu/1.jpg”
        $file = $url;
        if (strpos($file,'http://') !== false) {
            $arr = explode("/", $file);
            //获取最后一个/后边的字符
            $file = $arr[count($arr) - 1];
        }
        $StringToSign = "GET\n\n\n" . $expire."\n/" . $bucketName .'/' . $file;
        if (!empty($condition)) {
            $StringToSign = $StringToSign.'?'.$condition;
        }
        $Sign = base64_encode(hash_hmac("sha1", $StringToSign, $sk,true));
        $signUrl = urlencode($file) . "?OSSAccessKeyId=" . $ak . "&Expires=" . $expire . "&Signature=" . urlencode($Sign);
        if (!empty($condition)) {
            $signUrl = $signUrl.'&'.$condition;
        }
        $signUrl = "https://$domain/$signUrl";
        return $signUrl;
    }

    //原图地址
    public static function formatBaseUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        return "https://" . config('filesystems.disks.oss.cdnDomain') . "/$path";
    }
    //原图地址
    public static function formatUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        return "https://" . config('filesystems.disks.oss.cdnDomain') . "/$path";
//        $info = pathinfo($path);
//        if (in_array($info['extension'], ['svg', 'ico'])) {
//            return "https://" . config('filesystems.disks.oss.cdnDomain') . "/$path";
//        }
//        return "https://" . config('filesystems.disks.oss.cdnDomain') . "/$path" . '?x-oss-process=image/resize,w_960';
    }

    //缩略图地址
    public static function formatThumbUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        $info = pathinfo($path);
        if (in_array($info['extension'], ['svg', 'ico'])) {
            return "https://" . config('filesystems.disks.oss.cdnDomain') . "/$path";
        }
        return "https://" . config('filesystems.disks.oss.cdnDomain') . "/$path" . '?x-oss-process=image/resize,w_200';
    }

    //图标地址
    public static function formatIconUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        $info = pathinfo($path);
        if (in_array($info['extension'], ['svg', 'ico'])) {
            return "https://" . config('filesystems.disks.oss.cdnDomain') . "/$path";
        }
        return "https://" . config('filesystems.disks.oss.cdnDomain') . "/$path" . '?x-oss-process=image/resize,w_50';
    }
}
