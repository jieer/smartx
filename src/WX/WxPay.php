<?php

namespace SmartX\WX;

use SmartX\Models\WxApp;
use EasyWeChat\Factory;

class WxPay
{
    protected $ew_app;

    protected $wx_app;

    protected $wx_appid;

    public function __construct(WxApp $wx_app)
    {
        $this->wx_appid = $wx_app->appid;
        $config = [
            'app_id' => $wx_app->appid,
            'mch_id' => $wx_app->mch_id,
            'key' => $wx_app->aes_key,
            'notify_url' => $wx_app->notify,
            'response_type' => 'array',
        ];
        $app = Factory::payment($config);
        $this->ew_app = $app;
        $this->wx_app = $wx_app;
    }

    /*
     * 统一下单
     */
    public function order($order) {
        $result = $this->ew_app->order->unify([
            'body'              => $order['name'],
            'out_trade_no'      => $order['order_no'],
            'total_fee'         => ($order['amount'])*100,
            'trade_type'        => 'JSAPI',
            'openid'            => $order['openid'],
        ]);
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $jssdk = $this->ew_app->jssdk;
            $json = $jssdk->bridgeConfig($result['prepay_id'], false);
            $data = array(
                'prepay_id' => $result['prepay_id'],
                'config'    => $json,
            );
            return [
                'status' => 0,
                'data'   => $data
            ];

        } else {
            return [
                'status' => 1,
                'msg'   => $result['return_msg']
            ];
        }
    }

    /*
     * 根据商户订单或查询订单
     */
    public function queryOrderBuOutNo($order_id) {
        $result = $this->ew_app->order->queryByOutTradeNumber($order_id."");
        return $this->formatOrder($result);
    }

    /*
     * 根据微信订单号查询订单
     */
    public function queryOrderByTransactionId($transaction_id) {
        $result = $this->ew_app->order->queryByTransactionId($transaction_id);
        return $this->formatOrder($result);
    }

    public function formatOrder($result) {
        $ret = array(
            "status" => 0,
            "msg" => "",
            "data" => [],
        );
        if (empty($result)) {
            $ret["msg"] = "未查订单相关数据";
            return $ret;
        }

        if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            if ($result["trade_state"] == "SUCCESS") {
                $data = array(
                    "trade_state_desc" => $result["trade_state_desc"],
                    "total_fee" => $result["total_fee"]/100,
                    "bank_type" => $result["bank_type"],
                );
                $ret["status"] = 1;
                $ret["msg"] = "支付成功";
                $ret["data"] = $data;
                return $ret;
            }else{
                $ret["msg"] = $result["trade_state_desc"];
                return $ret;
            }
        }
        $ret["msg"] = $result["return_msg"];
        return $ret;
    }

    /*
     * 关闭订单，传入商户自己的订单号
     */
    public function closeOrder($order_id) {
        $result = $this->app->order->close($order_id);
        if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return true;
        }
        return false;
    }

}
