<?php

namespace SmartX\Services;

use SmartX\Models\User as CommonUser;
use SmartX\Models\WX\CommonUserInvite;
use SmartX\Models\WX\ScoreAction;
use SmartX\Models\WX\Order;

class CommonUserService
{

    public static function inviterUser($invitee, $inviter)
    {
        $inviter_user = CommonUser::find($inviter);
        if (empty($inviter_user)) {
            \Log::info('用户邀请信息生成失败, 邀请者 user_id=' . $inviter . ' 的用户id未查询到');
            return ;
        }
        $invitee_user = CommonUser::find($invitee);
        if (empty($invitee_user)) {
            \Log::info('用户邀请信息生成失败, 被邀请者 user_id=' . $invitee . ' 的用户id未查询到');
            return ;
        }

        $invite_history = CommonUserInvite::where('invited', $invitee)->where('inviter', $inviter)->first();
        if (!empty($invite_history)) {
            return ;
        }
        CommonUserInvite::create([
            'invited' => $invitee,
            'inviter' => $inviter
        ]);
    }

    public static function invitedFirstPay($invited_id)
    {
        $invited_user = CommonUser::find($invited_id);
        if (empty($invited_user)) {
            return;
        }
        $inviter_user = CommonUserInvite::where('invited', $invited_id)->where('is_award', 0)->first();
        if (empty($inviter_user)) {
            return ;
        }
        $order_count = Order::where('user_id', $invited_id)
            ->where('status', 1)
            ->where('pay_status', 1)
            ->whereIn('delivery_status', [3, 4])
            ->where('pay_amount', '>', 0)
            ->count();
        if ($order_count != 1) {
            return ;
        }

        $invite_action = ScoreAction::getAction('invite');
        if ($invite_action['code'] == 1) {
            $data = $invite_action['data'];
            $random = ScoreAction::getRandomFloatByProbability(1, 1.5, 3, 0.8);
            $data['score'] = round($data['score'] * $random, 2);
            ScoreAction::updateScore($inviter_user->inviter, $data);
            $inviter_user->is_award = 1;
            $inviter_user->save();
        }
    }

    public static function payAddScore($order_no)
    {
        \Log::info('-------增加积分--------');
        \Log::info($order_no);
        $order = Order::where('no', $order_no)
            ->where('status', 1)
            ->where('pay_status', 1)
            ->where('delivery_status', 4)
            ->where('pay_amount', '>', 0)
            ->first();
        if (empty($order)) {
            return;
        }
        if (empty($order->pay_amount)) {
            return;
        }
        \Log::info($order);
        $invite_action = ScoreAction::getAction('pay');
        \Log::info($invite_action);
        if ($invite_action['code'] == 1) {
            $data = $invite_action['data'];
            $random = ScoreAction::getRandomFloatByProbability(5, 10, 30, 0.8);
            \Log::info('----随机值-----');
            \Log::info($random);
            $random = $random / 100;
            \Log::info($random);
            $data['score'] = round($order->pay_amount * $random, 2);
            \Log::info('----积分值-----');
            \Log::info($data['score']);
            $data['score_value'] = $order->pay_amount;
            ScoreAction::updateScore($order->user_id, $data);
        }

        self::invitedFirstPay($order->user_id);

    }



    public static function registerUser($user_id)
    {
        $register_action = ScoreAction::getAction('register');

        if ($register_action['code'] == 1) {
            ScoreAction::updateScore($user_id, $register_action['data']);
        }
    }

    public static function getInviteInfo($user_id) {
        $data = [];
        $sql1 = CommonUser::selectRaw('smx_common_user.id, smx_common_user.name, smx_common_user.phone, smx_common_user.avatar, smx_common_user_invite.is_award')
            ->leftJoin('common_user_invite', 'common_user_invite.invited' , '=', 'common_user.id')
            ->where('common_user_invite.inviter', $user_id);
        $inviteds = $sql1->get();
        $newinviteds = [];
        foreach ($inviteds as $invited) {
            $newinvited = [];
            $newinvited['name'] = substr_replace($invited['phone'], '****', 3, 4);
            $newinvited['avatar'] = $invited['avatar'];
            $newinvited['award_str'] = '已注册';
            if ($invited['is_award']) {
                $newinvited['award_str'] .= '，已购买';
            }
            $newinviteds[] = $newinvited;
        }
        $data['inviteds'] = $newinviteds;// $sql1->get();
        $data['invited_count'] = $sql1->count();
        $data['is_award_invited_count'] = $sql1->where('common_user_invite.is_award', 1)->count();

        $data['inviter'] = CommonUser::selectRaw('smx_common_user.id, smx_common_user.name, smx_common_user.avatar')
            ->leftJoin('common_user_invite', 'common_user_invite.invited' , '=', 'common_user.id')
            ->where('common_user_invite.invited', $user_id)
            ->first();
        return $data;
    }

}
