<?php
namespace App\Http\Controllers\Wx;

use Illuminate\Http\Request;
use App\Services\AliyunOssService;
use DateTime;
use SmartX\Controllers\BaseWxController;
use App\Services\CrmService;
use App\Services\Health;

class OssController extends BaseWxController
{
    //OSS签名认证
    public function getSign(Request $request) {
        $member_id=$request->input('member_id');

        if (!isset($member_id) || empty($member_id)) {
            return $this->errorMessage(201, '会员号不能为空');
        }

        $member_result = CrmService::getMember($member_id);
        if (empty($member_result['code']) || $member_result['code'] != 200) {
            return $this->errorMessage(500, '获取会员出错');
        }
        $member = $member_result['data'];
        if (empty($member)) {
            return $this->errorMessage(500, '获取会员信息出错');
        }

        $user_id = $this->auth->user()->id;
        if ($member['user_id'] != $user_id) {
            return $this->errorMessage(201, "请上传自己名下的会员档案照片");
        }
        $suf = $request->input('suf');
        if (empty($suf)) {
            $suf = 'png';
        }
        $record_no = $request->input('record_no');


        $id= config('filesystems.disks.oss.access_id');
        $key= config('filesystems.disks.oss.access_key');
        // $host的格式为 bucketname.endpoint，请替换为您的真实信息。
        $domain = config('filesystems.disks.oss.cdnDomain');
        $bucket = config('filesystems.disks.oss.bucket');

        if (empty($domain)) {
            $domain = $bucket.'.'.config('filesystems.disks.oss.endpoint');
        }

        $host = "https://$domain";
        // $callbackUrl为上传回调服务器的URL，请将下面的IP和Port配置为您自己的真实URL信息。
        $callbackUrl = env('CALL_BACK_URL')."?member_id=" . $member['id']."&user_id=$user_id"."&record_no=$record_no";
        $mydir = 'wx/'.$member['phone'].'/'.date('Ymd').'/';
        $dir = env('FILE_PATH').$mydir;  //上传目录设置
        $callback_param = array(
            'callbackUrl'=>$callbackUrl,
            'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType'=>"application/x-www-form-urlencoded"
            //'callbackBodyType'=>"application/json"

        );
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 600; //设置该policy超时时间是30s. 即这个policy过了这个有效时间，将不能访问
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);  //进行时间格式的转换
        //处理上传限制条件
        //最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
        $conditions[] = $condition; //设定文件大小
        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;  //必须以设定的目录开头,防止上传错误
        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);  //要返回的上传限制参数
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));  //要返回的签名信息

        $video_url = '';
        $filename = rand(1000000, 9999999999).'.'. $suf;
        if (in_array($suf, ['jpg', 'jpeg', 'png', 'bmp', 'gif'])) {
            //图片
            $condition = "x-oss-process=image/resize,m_fixed,h_1600,w_2400/watermark,image_YmFrYW5nd2F0ZXIxNjAwLnBuZz94LW9zcy1wcm9jZXNzPWltYWdlL3Jlc2l6ZSxQXzEwMA,g_center,x_1,y_1";
        } elseif (in_array($suf, ['3gp','mp4','m3u8'])) {
            //视频
            $condition = "?x-oss-process=video/snapshot,t_1000,f_jpg,w_100,h_100";
            $video_url = AliyunOssService::signUrl($dir.$filename, $condition);
            $condition = '';
        } else {
            $condition = '';
        }
        $url = AliyunOssService::shuiyinUrl($mydir.$filename);


        //设置返回信息
        $response = array(
            'accessid' => $id,  //accessid
            'host' => $host,    //上传地址
            'policy' => $base64_policy,  //上传文件限制
            'signature' => $signature,   //签名信息
            'expire' => $end,    //失效时间
            'callback' => $base64_callback_body,  //上传回调参数
            'dir' => $dir,     //上传的目录
            'filename' => $dir.$filename,
            'filepath' => $mydir.$filename,
            'url' => $url,
            'video_pre_url' => $video_url,
        );
        return $this->message($response);

    }

    //格式化时间,格式为2016-07-07T23:48:43Z
    function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }
}
