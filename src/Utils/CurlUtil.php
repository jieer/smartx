<?php
/**
 */

namespace Smartwell\Utils;

class CurlUtil
{
    //curl发请求
    public function auth_request($url, $method = 'GET', $params = [], $headers = []){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
            curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
        }
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        $r = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [
            'code' => $httpCode,
            'data' => $r
        ];
    }
}
