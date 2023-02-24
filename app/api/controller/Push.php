<?php

namespace app\api\controller;

use app\api\job\FollowTask;
use app\api\model\Externalmember as ExternalmemberModel;
use think\exception\ValidateException;
use think\facade\Validate;

/**
 * 发布任务类
 */
class Push extends Common
{

    /**
     * @api {get} /Push/screen_chat 02、获取私信任务筛选后的用户数量
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  获取私信任务筛选后的用户数量
     * @apiParam (输入参数：) {int}              [reset_status] 重置粉丝状态
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数量
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"","data":0}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function screen_chat()
    {
        $params = $this->request->get();
        // var_dump($params['country_list']);die;
        $external_member_num = db('fanslist')->where(function ($query) use ($params) {
            if ($params['reset_status']) {
                $query->where("if_chat", 0);
            }
            $query->whereRaw(in_to_or($params['uid_list'], 'member_uid'));
            if ($params['country_list']) {
                $query->whereRaw(in_to_or($params['country_list'], 'region'));
            }

        })->field("uid,sec_uid")->count();

        return $this->ajaxReturn($this->successCode, '返回成功', $external_member_num);
    }

    /**
     * @api {get} /Push/screen_comment_digg 02、获取评论任务筛选后的评论数量
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  获取评论任务筛选后的评论数量
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [comment_digg_count_lower_limit] 评论获赞小于
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数量
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"","data":0}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function screen_comment_digg()
    {
        $params = $this->request->get();

        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单类型', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }
        $comment_num = db('comment_list')
            ->where(function ($query) use ($params) {
                $query->whereRaw(in_to_or($params['country_list'], 'account_region'));
                $query->whereRaw(in_to_or($params['tasklist_id_list'], 'tasklist_id'));
                if ($params['comment_digg_count_lower_limit']) {
                    $query->where('digg_count', '<', $params['comment_digg_count_lower_limit']);
                }
                $black_list = $params['black_list'];
                if (in_array("no_avatar", $black_list)) {
                    $query->where("has_avatar", 1);
                }
                if (in_array("no_aweme", $black_list)) {
                    $query->where("aweme_count", '>', 0);
                }
                if (in_array("historical_users", $black_list)) {
                    $query->where("is_digg", 1);
                }
                if (in_array("no_nickname", $black_list)) {
                    $query->where("has_nickname", 1);
                }
            })

            ->field("cid,aweme_id")->count();


        return $this->ajaxReturn($this->successCode, '返回成功', $comment_num);
    }

    /**
     * @api {get} /Push/screen_follow 02、获取关注任务筛选后的用户数量
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  获取关注任务筛选后的用户数量
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_follow_upper_limit] 单号关注上限
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数量
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"","data":0}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function screen_video(){
        $params = $this->request->get();
        $num = db('material')->where(['typecontrol_id'=>$params['typecontrol_id'],'status'=>1])->count();
        return $this->ajaxReturn($this->successCode, '返回成功', $num);
    }
    function screen_follow()
    {
        $params = $this->request->get();

        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单类型', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }

        $external_member_num = ExternalmemberModel::where(['secret' => 0])
            ->where(function ($query) use ($params) {
                if ($params['follower_status']) $query->where('follower_count', '<', $params['follower_status']);
                if ($params['following_count']) $query->where('following_count', '<', $params['following_count']);
                if ($params['total_favorited']) $query->where('total_favorited', '<', $params['total_favorited']);
                $black_list = $params['black_list'];
                if (in_array("no_avatar", $black_list)) {
                    $query->where("has_avatar", 1);
                }
                if (in_array("no_aweme", $black_list)) {
                    $query->where("aweme_count", '>', 0);
                }
                if (in_array("historical_users", $black_list)) {
                    $query->where("is_follow", 1);
                }
                if (in_array("no_nickname", $black_list)) {
                    $query->where("has_nickname", 1);
                }
            })
            ->whereRaw(in_to_or($params['country_list'], 'country'))
            ->whereRaw(in_to_or($params['tasklist_id_list'], 'tasklist_id'))
            ->field('external_member_id')->count();


        return $this->ajaxReturn($this->successCode, '返回成功', $external_member_num);
    }

    static function getArr($data, $num)
    {
        $arr = [];
        $frequency = ceil($num / count($data));
        for ($i = 0; $i < $frequency; $i++) {
            $arr = array_merge($arr, $data);
        }
        return $arr;
    }

    public function chat()
    {
        $params = $this->request->post();

        //验证规则
        $rule = [
            'typecontrol_id' => 'require',
            'user_chat_upper_limit' => 'require',
            // 'privateletter_id' => 'require',
            'type_list' => 'require',
            'task_name' => 'require',
            // 'uid_list' => 'require',
        ];

        //错误提示
        $msg = [
            'type_list.require' => 'type_list（私信类型）必传',
            'user_chat_upper_limit.require' => 'user_chat_upper_limit（单号私信上限）必传',
            'task_name.require' => 'task_name（任务名称）必传',
            // 'privateletter_id.require' => 'privateletter_id（私信素材库ID）必传',
            'typecontrol_id.require' => 'typecontrol_id必传',
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }
        $type_list = $params['type_list'];
        if (count($type_list) > 3) {
            throw new ValidateException('私信类型最多选择三种');
        }
        // var_dump($type_list);die;
        if (!in_array("ChatText", array_keys($type_list)) && !in_array("ChatProfile", array_keys($type_list)) && !in_array("ChatAweme", array_keys($type_list)) && !in_array("ChatLink", array_keys($type_list))) {
            throw new ValidateException(['未知的私信类型', ['type_list' => ['ChatText', 'ChatProfile', 'ChatAweme', 'ChatLink']]]);
        }
        $user_chat_upper_limit = $params['user_chat_upper_limit'];

        // if ($privateletters_num < $user_chat_upper_limit) {
        //     throw new ValidateException('私信素材数量不足');
        // }

        $uidlist = $params['uid_list'];
        $typecontrol_id = $params["typecontrol_id"] ?? $params["typecronl_id"];
        if (empty($uidlist) && (empty($typecontrol_id))) {
            throw new ValidateException('uidlist和分组分类二选一必传');
        }
        $wherelist = [];
        if ($uidlist && $typecontrol_id || $uidlist && !$typecontrol_id) {
            $idx = implode(",", $uidlist);
            $wherelist[] = ['uid', 'in', $idx];
        }
        if ($typecontrol_id && !$uidlist) {
            $wherelist['typecontrol_id'] = $typecontrol_id;
        }
        $members = db('member')->where($wherelist)->field('uid,sec_uid,unique_id,token')->select()->toArray();
        $task_num = $user_chat_upper_limit * count($members);
        // var_dump($user_chat_upper_limit); var_dump(count($members));
        // var_dump($task_num);die;
        checkTaskNum($task_num);
        $redis_key = get_task_key('Chat');
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => "Chat",
            "task_num" => $task_num,
            'redis_key' => $redis_key,
            "create_time" => time(),
            "status" => 1,
            "complete_num" => 0,
            'api_user_id' => $this->request->uid,
            'member_num'=>count($members),
            'typecontrol_id'=>$params["typecontrol_id"]

        ];
        $task_id = db("tasklist")->insertGetId($task);
        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        //被操作用户查询
        $external_members = db('fanslist')->where(function ($query) use ($params) {
            if ($params['reset_status']) {
                $query->where("if_chat", 0);
            }
            $query->whereRaw(in_to_or($params['uid_list'], 'member_uid'));
            //$query->where('member_uid', 'in', $params['uid_list']);
            if ($params['country_list']) {
                $query->whereRaw(in_to_or($params['country_list'], 'region'));
                //$query->where('region', 'in', $params['country_list']);
            }
        })->field("uid,sec_uid")->select()->toArray();
        if (count($external_members) < $user_chat_upper_limit) {
            throw new ValidateException('当前条件下可私信博主仅剩' . count($external_members) . '个');
        }
        $ChatTextContents = $ChatProfileContents = $ChatAwemeContents = $ChatLinkContents = array();
        foreach ($type_list as $type => $privateletter_ids) {
            switch ($type) {
                case "ChatText":
                    $ChatTextContents = db('privateletter')->where(['type' => '0'])
                        //->whereIn('privateletter_id', $privateletter_ids)
                        ->whereRaw(in_to_or($privateletter_ids, 'classname_id'))
                        ->field("content,privateletter_id")->order('usage_count asc')->select()->toArray();
                    $ChatTextContents = self::getArr($ChatTextContents, $user_chat_upper_limit);
                    break;
                case "ChatProfile":
                    $ChatProfileContents = db('privateletter')->where(['type' => '2'])
                        //->whereIn('privateletter_id', $privateletter_ids)
                        ->whereRaw(in_to_or($privateletter_ids, 'classname_id'))
                        ->field("content,privateletter_id")->order('usage_count asc')->select()->toArray();
                    $ChatProfileContents = self::getArr($ChatProfileContents, $user_chat_upper_limit);
                    break;
                case "ChatAweme":
                    $ChatAwemeContents = db('privateletter')->where(['type' => '3'])
                        //->whereIn('privateletter_id', $privateletter_ids)
                        ->whereRaw(in_to_or($privateletter_ids, 'classname_id'))
                        ->field("content,privateletter_id")->order('usage_count asc')->select()->toArray();
                    $ChatAwemeContents = self::getArr($ChatAwemeContents, $user_chat_upper_limit);
                    break;
                case "ChatLink":
                    $ChatLinkContents = db('privateletter')->where(['type' => '1'])
                        //->whereIn('privateletter_id', $privateletter_ids)
                        ->whereRaw(in_to_or($privateletter_ids, 'classname_id'))
                        ->field("content,privateletter_id")->order('usage_count asc')->select()->toArray();
                    $ChatLinkContents = self::getArr($ChatLinkContents, $user_chat_upper_limit);
                    break;
            }
        }

//        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
//        flushRequest();
        $redis = connectRedis();
        $task_details = [];
        foreach ($members as $member) {
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $params['user_chat_upper_limit'];
            $task_uid_id = db('task_uid')->insertGetId($uid_task);

            $token = doToken($member['token']);
            //取http代理
            $proxy = getHttpProxy($token['user']['uid']);
            if ($task_num) {
                for ($i = 0; $i < $user_chat_upper_limit; $i++) {
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $external_member_index = array_rand($external_members);
                    $external_member = $external_members[$external_member_index];
                    unset($external_members[$external_member_index]);
                    if (!$external_member) continue;
                    $receiver = $external_member['uid'];
                    $chat_list = [];
                    foreach ($params['type_list'] as $task_type => $privateletter_ids) {
                        // var_dump($task_type);var_dump($i);
                        switch ($task_type) {
                            case "ChatText":
                                if (!$ChatTextContents) break;
                                $content = $ChatTextContents[$i];
                                break;
                            case "ChatProfile":
                                if (!$ChatProfileContents) break;
                                $content = $ChatProfileContents[$i];
                                break;
                            case "ChatAweme":
                                if (!$ChatAwemeContents) break;
                                $content = $ChatAwemeContents[$i];
                                break;
                            case "ChatLink":
                                if (!$ChatLinkContents) break;
                                $content = $ChatLinkContents[$i];
                                break;
                        }
                        if (!isset($content['content'])) continue;
                        // var_dump($content);
                        
                        db('privateletter')->where('privateletter_id', $content['privateletter_id'])->inc('usage_count')->update();

                        db('tasklist')->where('tasklist_id',$task_id)->inc('creation_num')->update();
                        $parameter = [
                            'receiver' => $receiver,
                            'client_id' => create_uuid('client_id_'),
                            'content' => $content['content'],
                            'token' => $token,
                            'proxy' => $proxy,
                            'uid' => $member['uid'],
                        ];
                        
                        if($i==0){ 
                            $task_detail = [
                                'task_uid_id' => $task_uid_id,
                                "tasklist_id" => $task_id,
                                "parameter" => $parameter,
                                "status" => 1,
                                "create_time" => time(),
                                "task_type" => $task_type,
                                "crux" => $member['uid']
                            ];
                            unset($task_detail['tasklistdetail_id']);
                            //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                            $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
    
                            $task_detail['tasklistdetail_id'] = $task_detail_id;
    
                            $chat_list[] = [
                                'type' => $task_type,
                                'client_id' => create_uuid('client_id_'),
                                'content' => $content['content'],
                                'tasklistdetail_id' => $task_detail_id
                            ];

                        }else{
                            $task_detail = [
                                'task_uid_id' => $task_uid_id,
                                "tasklist_id" => $task_id,
                                "parameter" => $parameter,
                                "status" => 5,
                                "create_time" => time(),
                                "task_type" => $task_type,
                                "crux" => $member['uid']
                            ];
                            unset($task_detail['tasklistdetail_id']);
                            //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                            $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);

//                            $task_detail['tasklistdetail_id'] = $task_detail_id;
//
//                            $chat_list[] = [
//                                'type' => $task_type,
//                                'client_id' => create_uuid('client_id_'),
//                                'content' => $content['content'],
//                                'tasklistdetail_id' => $task_detail_id
//                            ];
                        }
                    }
                    if ($chat_list) {
                        $redis_detail = [
                            'receiver' => $receiver,
                            'chat_list' => $chat_list,
                            'token' => $token,
                            'proxy' => $proxy
                        ];
                        $task_details[] = $redis_detail;
                    }
                }
            }
        }
        // var_dump($task_details);die;
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }

    public function chat_old1()
    {
        $params = $this->request->post();

        //验证规则
        $rule = [
//            'typecronl_id' => 'require',
            'user_chat_upper_limit' => 'require',
            'privateletter_id' => 'require',
            'type_list' => 'require',
            'task_name' => 'require',
            'uid_list' => 'require',
        ];

        //错误提示
        $msg = [
            'type_list.require' => 'type_list（私信类型）必传',
            'user_chat_upper_limit.require' => 'user_chat_upper_limit（单号私信上限）必传',
            'task_name.require' => 'task_name（任务名称）必传',
            'privateletter_id.require' => 'privateletter_id（私信素材库ID）必传',
            'uid_list.require' => 'uid_list（账号列表）必传',
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }
        $type_list = $params['type_list'];
        if (count($type_list) > 3) {
            throw new ValidateException('私信类型最多选择三种');
        }
        // var_dump($type_list);die;
        if (!in_array("ChatText", $type_list) && !in_array("ChatProfile", $type_list) && !in_array("ChatAweme", $type_list) && !in_array("ChatLink", $type_list)) {
            throw new ValidateException(['未知的私信类型', ['type_list' => ['ChatText', 'ChatProfile', 'ChatAweme', 'ChatLink']]]);
        }
        $user_chat_upper_limit = $params['user_chat_upper_limit'];
        //私信素材查询
        $privateletters_num = db('privateletter')->where("typecontrol_id", $params['typecontrol_id'])->count();
        // if ($privateletters_num < $user_chat_upper_limit) {
        //     throw new ValidateException('私信素材数量不足');
        // }

        $uidlist = $params['uid_list'];
        $typecontrol_id = $params["typecontrol_id"] ?? $params["typecronl_id"];
        if (empty($uidlist) && (empty($typecontrol_id))) {
            throw new ValidateException('uidlist和分组分类二选一必传');
        }
        $wherelist = [];
        if ($uidlist && $typecontrol_id || $uidlist && !$typecontrol_id) {
            $idx = implode(",", $uidlist);
            $wherelist[] = ['uid', 'in', $idx];
        }
        if ($typecontrol_id && !$uidlist) {
            $wherelist['typecontrol_id'] = $typecontrol_id;
        }
        $members = db('member')->where($wherelist)->field('uid,sec_uid,unique_id,token')->select()->toArray();
        $task_num = $user_chat_upper_limit * count($members);
        // var_dump($user_chat_upper_limit); var_dump(count($members));
        // var_dump($task_num);die;
        checkTaskNum($task_num);
        $redis_key = get_task_key('Chat');
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => "Chat",
            "task_num" => $task_num,
            'redis_key' => $redis_key,
            "create_time" => time(),
            "status" => 1,
            "complete_num" => 0,
            'api_user_id' => $this->request->uid
        ];
        $task_id = db("tasklist")->insertGetId($task);
//        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
//        flushRequest();

        //被操作用户查询
        $external_members = db('fanslist')->where(function ($query) use ($params) {
            if ($params['reset_status']) {
                $query->where("if_chat", 0);
            }
            $query->where('member_uid', 'in', $params['uid_list']);
            if ($params['country_list']) {
                $query->where('region', 'in', $params['country_list']);
            }
        })->field("uid,sec_uid")->select()->toArray();
        if (count($external_members) < $user_chat_upper_limit) {
            throw new ValidateException('当前条件下可私信博主仅剩' . count($external_members) . '个');
        }

        $redis = connectRedis();
        $task_details = [];
        foreach ($members as $member) {
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $params['user_chat_upper_limit'];
            $task_uid_id = db('task_uid')->insertGetId($uid_task);
            if ($task_num) {
                for ($i = 0; $i < $user_chat_upper_limit; $i++) {
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $external_member_index = array_rand($external_members);
                    $external_member = $external_members[$external_member_index];
                    unset($external_members[$external_member_index]);
                    if (!$external_member) continue;

                    foreach ($params['type_list'] as $task_type) {
                        // var_dump($task_type);die;
                        switch ($task_type) {
                            case "ChatText":
                                $content = db('privateletter')->where(["privateletter_id" => $params['privateletter_id'], 'type' => '0'])->field("content,privateletter_id")->order('usage_count asc')->find();
                                break;
                            case "ChatProfile":
                                $content = db('privateletter')->where(["privateletter_id" => $params['privateletter_id'], 'type' => '2'])->field("content,privateletter_id")->order('usage_count asc')->find();
                                break;
                            case "ChatAweme":
                                $content = db('privateletter')->where(["privateletter_id" => $params['privateletter_id'], 'type' => '3'])->field("content,privateletter_id")->order('usage_count asc')->find();
                                break;
                            case "ChatLink":
                                $content = db('privateletter')->where(["privateletter_id" => $params['privateletter_id'], 'type' => '1'])->field("content,privateletter_id")->order('usage_count asc')->find();
                                break;
                        }
                        if (!isset($content['content'])) continue;
                        // var_dump($content);die;
                        db('privateletter')->where('privateletter_id', $content['privateletter_id'])->inc('usage_count')->update();
                        $token = doToken($member['token']);
                        //取http代理
                        $proxy = getHttpProxy($token['user']['uid']);

                        $parameter = [
                            'receiver' => $external_member['uid'],
                            'client_id' => create_uuid('client_id_'),
                            'content' => $content['content'],
                            "token" => $token,
                            "proxy" => $proxy
                        ];
                        $task_detail = [
                            'task_uid_id' => $task_uid_id,
                            "tasklist_id" => $task_id,
                            "parameter" => $parameter,
                            "status" => 1,
                            "create_time" => time(),
                            "task_type" => $task_type,
                            "crux" => $external_member['uid']
                        ];
                        unset($task_detail['tasklistdetail_id']);
                        //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                        $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                        $task_detail['tasklistdetail_id'] = $task_detail_id;
                        $task_details[] = $task_detail;
                    }
                }
            }
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }

    /**
     * @api {post} /Push/chat 02、发布私信任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布评论点赞任务
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_chat_upper_limit] 单号私信上限
     * @apiParam (输入参数：) {int}              [can_fail_num] 连续失败次数
     * @apiParam (输入参数：) {int}              [total_task_num] 总私信上线
     * @apiParam (输入参数：) {int}              [privateletter_id] 私信素材库ID
     * @apiParam (输入参数：) {int}              [type_list] 素材类型
     * @apiParam (输入参数：) {int}              [reset_status] 重置粉丝状态
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    public function chat_old2()
    {
        $params = $this->request->post();

        //验证规则
        $rule = [
            'typecronl_id' => 'require',
            // 'country_list' => 'require',
            'tasklist_id_list' => 'require',
            'user_chat_upper_limit' => 'require',
//            'total_task_num' => '',
            'privateletter_id' => 'require',
            'type_list' => 'require',
//            'reset_status' => '',
            'task_name' => 'require',
        ];

        //错误提示
        $msg = [
            'type_list.require' => 'type_list（私信类型）必传',
            // 'country_list.require' => '国家必传',
            'user_chat_upper_limit.require' => 'user_chat_upper_limit（单号私信上限）必传',
//            'total_task_num.require' => 'total_task_num（总私信上限）必传',
//            'reset_status.require' => 'reset_status（重置粉丝状态）必传',
            'task_name.require' => 'task_name（任务名称）必传',
            'privateletter_id.require' => 'privateletter_id（私信素材库ID）必传',
            'uid_list.require' => 'uid_list（账号列表）必传',
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }
        $type_list = $params['type_list'];
        if (count($type_list) > 3) {
            throw new ValidateException('私信类型最多选择三种');
        }
        // var_dump($type_list);die;
        if (!in_array("ChatText", $type_list) && !in_array("ChatProfile", $type_list) && !in_array("ChatAweme", $type_list) && !in_array("ChatLink", $type_list)) {
            throw new ValidateException(['未知的私信类型', ['type_list' => ['ChatText', 'ChatProfile', 'ChatAweme', 'ChatLink']]]);
        }
        $total_task_num = $params['total_task_num'];
        //私信素材查询
        $privateletters_num = db('privateletter')->where("typecontrol_id", $params['b_typecontrol_id'])->count();
        if ($privateletters_num < $total_task_num) {
            throw new ValidateException('私信素材数量不足');
        }

        $uidlist = $params['uid_list'];
        $typecontrol_id = $params["typecontrol_id"] ?? $params["typecronl_id"];
        if (empty($uidlist) && (empty($typecontrol_id))) {
            throw new ValidateException('uidlist和分组分类二选一必传');
        }
        $wherelist = [];
        if ($uidlist && $typecontrol_id || $uidlist && !$typecontrol_id) {
            $idx = implode(",", $uidlist);
            $wherelist[] = ['member_id', 'in', $idx];
        }
        if ($typecontrol_id && !$uidlist) {
            $wherelist['typecontrol_id'] = $typecontrol_id;
        }

        $members = db('member')->where($wherelist)->field('uid,sec_uid,unique_id,token')->select()->toArray();
        checkTaskNum($total_task_num);
        $redis_key = get_task_key('Chat');
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => "Chat",
            "task_num" => $total_task_num,
            'redis_key' => $redis_key,
            "create_time" => time(),
            "status" => 1,
            "complete_num" => 0,
            'api_user_id' => $this->request->uid
        ];
        $task_id = db("tasklist")->insertGetId($task);
        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        //操作用户查询

        //被操作用户查询
        $external_members = db('external_member')->where(function ($query) use ($params) {
            if ($params['reset_status']) {
                $query->where("if_chat", 0);
            }
            $query->where('country', 'in', $params['country_list']);
        })->field("uid,sec_uid")->select()->toArray();
        $redis = connectRedis();
        $task_details = [];
        foreach ($members as $member) {
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $params['user_chat_upper_limit'];
            $task_uid_id = db('task_uid')->insertGetId($uid_task);
            if ($total_task_num) {
                for ($i = 0; $i < $params['user_chat_upper_limit']; $i++) {
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $external_member_index = array_rand($external_members);
                    $external_member = $external_members[$external_member_index];
                    unset($external_members[$external_member_index]);

                    foreach ($params['type_list'] as $task_type) {
                        switch ($task_type) {
                            case "ChatText":
                                $content = db('privateletter')->where(["typecontrol_id" => $params['b_typecontrol_id'], 'type' => '0'])->value("content");
                                break;
                            case "ChatProfile":
                                $content = db('privateletter')->where(["typecontrol_id" => $params['b_typecontrol_id'], 'type' => '2'])->value("content");
                                break;
                            case "ChatAweme":
                                $content = db('privateletter')->where(["typecontrol_id" => $params['b_typecontrol_id'], 'type' => '3'])->value("content");
                                break;
                            case "ChatLink":
                                $content = db('privateletter')->where(["typecontrol_id" => $params['b_typecontrol_id'], 'type' => '1'])->value("content");
                                break;
                        }
                        if (!isset($content)) continue;
                        $token = doToken($member['token']);
                        //取http代理
                        $proxy = getHttpProxy($token['user']['uid']);

                        $parameter = [
                            'receiver' => $external_member['uid'],
                            'client_id' => create_uuid('client_id_'),
                            'content' => $content,
                            "token" => $token,
                            "proxy" => $proxy
                        ];
                        $task_detail = [
                            'task_uid_id' => $task_uid_id,
                            "tasklist_id" => $task_id,
                            "parameter" => $parameter,
                            "status" => 1,
                            "create_time" => time(),
                            "task_type" => $task_type,
                            "crux" => $external_member['uid']
                        ];
                        unset($task_detail['tasklistdetail_id']);
                        //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                        $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                        $task_detail['tasklistdetail_id'] = $task_detail_id;
                        $task_details[] = $task_detail;
                    }
                    $total_task_num--;
                }
            }
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }

    /**
     * @api {post} /Push/follow_new 02、发布关注任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布关注任务
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_follow_upper_limit] 单号关注上限
     * @apiParam (输入参数：) {int}              [rate_min] 频率最小
     * @apiParam (输入参数：) {int}              [rate_max] 频率最大
     * @apiParam (输入参数：) {int}              [can_fail_num] 连续失败次数
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function follow()
    {
        $params = $this->request->post();
        $task_type = "Follow";

        //验证规则
        $rule = [
//            'grouping_id' => 'require',
//            'typecronl_id' => 'require',
            'country_list' => 'require',
            'tasklist_id_list' => 'require',
            'user_follow_upper_limit' => 'require',
            'rate_min' => 'require',
            'rate_max' => 'require',
            'can_fail_num' => 'require',
            'task_name' => 'require'
        ];

        //错误提示
        $msg = [
//            'grouping_id.require' => '分组ID必传',
//            'typecronl_id.require' => '分类id必传',
            'country_list.require' => '国家必传',
            'tasklist_id_list.require' => 'tasklist_id_list（数据来源（采集任务ID））必传',
            'user_follow_upper_limit.require' => 'user_follow_upper_limit（单号关注上限）必传',
            'rate_min.require' => 'rate_min（关注频率最小值）必传',
            'rate_max.require' => 'rate_max（关注频率最大值）必传',
            'can_fail_num.require' => 'can_fail_num（可失败次数）必传',
            'task_name' => '任务名称必传'
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }

        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单类型', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }
        //操作用户查询
        $uidlist = $params['uid_list'];
        $typecontrol_id = $params["typecontrol_id"] ?? $params["typecronl_id"];
        // var_dump($typecontrol_id);die;
        if (empty($uidlist) && (empty($typecontrol_id))) {
            throw new ValidateException('uidlist和分组分类二选一必传');
        }
        $wherelist = [];
        if ($uidlist && $typecontrol_id || $uidlist && !$typecontrol_id) {
            $idx = implode(",", $uidlist);
            $wherelist[] = ['uid', 'in', $idx];
        }
        if ($typecontrol_id && !$uidlist) {
            $wherelist['typecontrol_id'] = $typecontrol_id;
        }

        $members = db('member')->where($wherelist)->field('uid,sec_uid,unique_id,token')->select();

        $user_follow_upper_limit = $params['user_follow_upper_limit'];
        $total_task_num = 0;
        /*foreach ($members as &$member) {
            // 该账号今日关注次数
            $member_follow_num = db("followuser")->where("uid", $member['uid'])->whereDay("create_time")->count();
            $member_task_num = ($user_follow_upper_limit - $member_follow_num);
            $member['today_follow_num'] = $member_follow_num;
            if (!$member_task_num) {
                continue;
            }
            $total_task_num += $member_task_num;
        }*/
        $total_task_num = $user_follow_upper_limit * count($members);
        $external_members = ExternalmemberModel::where(['secret' => 0])
            ->where(function ($query) use ($params) {
                if ($params['follower_status']) $query->where('follower_count', '<', $params['follower_status']);
                if ($params['following_count']) $query->where('following_count', '<', $params['following_count']);
                if ($params['total_favorited']) $query->where('favoriting_count', '<', $params['total_favorited']);
                $black_list = $params['black_list'];
                if (in_array("no_avatar", $black_list)) {
                    $query->where("has_avatar", 1);
                }
                if (in_array("no_aweme", $black_list)) {
                    $query->where("aweme_count", '>', 0);
                }
                if (in_array("historical_users", $black_list)) {
                    $query->where("is_follow", 1);
                }
                if (in_array("no_nickname", $black_list)) {
                    $query->where("has_nickname", 1);
                }
            })
            ->whereRaw(in_to_or($params['country_list'], 'country'))
            ->whereRaw(in_to_or($params['tasklist_id_list'], 'tasklist_id'))
            ->field("uid,sec_uid")->select()->toArray();

        if (count($external_members) < $total_task_num) {
            throw new ValidateException('当前条件下可关注博主仅剩' . count($external_members) . '个');
        }
        checkTaskNum($total_task_num);
        $redis_key = get_task_key('Follow');
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => $task_type,
            "task_num" => $total_task_num,
            "create_time" => time(),
            'redis_key' => $redis_key,
            "status" => 1,
            "complete_num" => 0,
            'api_user_id' => $this->request->uid,
            'member_num'=>count($members),
            'typecontrol_id'=>$params["typecontrol_id"]
        ];
        $task_id = db("tasklist")->insertGetId($task);
        //往中间表中添加数据

        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        unset($member);
        $redis = connectRedis();
        foreach ($members as $member) {
            //往中间表中添加数据
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $user_follow_upper_limit;
            $task_uid_id = db('task_uid')->insertGetId($uid_task);

            $external_member_uid_list = [];
            $token = doToken($member['token']);
            //取http代理
            $proxy = getHttpProxy($token['user']['uid']);
            $delay = rand($params['rate_min'], $params['rate_max']); //关注频率，延迟多少秒执行
            for ($i = 0; $i < ($user_follow_upper_limit - $member['today_follow_num']); $i++) {
                if ($external_members) {
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $external_member_index = array_rand($external_members);
                    $external_member = $external_members[$external_member_index];
                    unset($external_members[$external_member_index]);
                    if ($external_member) {

                        $parameter = [
                            'member_uid' => $member['uid'],
                            'user_id' => $external_member['uid'],
                            'sec_user_id' => $external_member['sec_uid'],
                            'from' => 19,
                            'from_pre' => 13,
                            'channel_id' => 3,
                            'type' => 1, // # 1 表示关注，0 表示取消关注
                            "token" => $token,
                            "proxy" => $proxy
                        ];
                        $task_detail = [
                            'task_uid_id' => $task_uid_id,
                            "tasklist_id" => $task_id,
                            "parameter" => $parameter,
                            "status" => 1,
                            "create_time" => time(),
                            "task_type" => $task_type,
                            "crux" => $member['uid']
                        ];
                        unset($task_detail['tasklistdetail_id']);
                        //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                        $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                        db('tasklist')->where('tasklist_id',$task_id)->inc('creation_num')->update();
                        $external_member_uid_list[] = ['user_id' => $external_member['uid'], 'sec_user_id' => $external_member['sec_uid'], 'tasklistdetail_id' => $task_detail_id];
                    }
                }
            }
            $detail = [
                'member_uid' => $member['uid'],
                'external_member_uid_list' => $external_member_uid_list,
                'from' => 19,
                'from_pre' => 13,
                'channel_id' => 3,
                'type' => 1, // # 1 表示关注，0 表示取消关注
                "proxy" => $proxy,
                'delay' => $delay,
                'rate_min' => $params['rate_min'],
                'rate_max' => $params['rate_max'],
                "token" => $token,
                "task_type" => $task_type,
            ];
            $redis->lPush($redis_key, json_encode($detail));
        }
    }

    /**
     * @api {post} /Push/follow 02、发布关注任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布关注任务
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_follow_upper_limit] 单号关注上限
     * @apiParam (输入参数：) {int}              [rate_min] 频率最小
     * @apiParam (输入参数：) {int}              [rate_max] 频率最大
     * @apiParam (输入参数：) {int}              [can_fail_num] 连续失败次数
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function follow_old()
    {
        $params = $this->request->post();
        $task_type = "Follow";

        //验证规则
        $rule = [
//            'grouping_id' => 'require',
//            'typecronl_id' => 'require',
            'country_list' => 'require',
            'tasklist_id_list' => 'require',
            'user_follow_upper_limit' => 'require',
            'rate_min' => 'require',
            'rate_max' => 'require',
            'can_fail_num' => 'require',
            'task_name' => 'require'
        ];

        //错误提示
        $msg = [
//            'grouping_id.require' => '分组id必传',
//            'typecronl_id.require' => '分类id必传',
            'country_list.require' => '国家必传',
            'tasklist_id_list.require' => 'tasklist_id_list（数据来源（采集任务ID））必传',
            'user_follow_upper_limit.require' => 'user_follow_upper_limit（单号关注上限）必传',
            'rate_min.require' => 'rate_min（关注频率最小值）必传',
            'rate_max.require' => 'rate_max（关注频率最大值）必传',
            'can_fail_num.require' => 'can_fail_num（可失败次数）必传',
            'task_name' => '任务名称必传'
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }

        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单类型', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }
        //操作用户查询
        $uidlist = $params['uid_list'];
        $typecontrol_id = $params["typecontrol_id"] ?? $params["typecronl_id"];

        if (empty($uidlist) && (empty($typecontrol_id))) {
            throw new ValidateException('uidlist和分组分类二选一必传');
        }
        $wherelist = [];
        if ($uidlist && $typecontrol_id || $uidlist && !$typecontrol_id) {
            $idx = implode(",", $uidlist);
            $wherelist[] = ['uid', 'in', $idx];
        }
        if ($typecontrol_id && !$uidlist) {
            $wherelist['typecontrol_id'] = $typecontrol_id;
        }

        $members = db('member')->where($wherelist)->field('uid,sec_uid,unique_id,token')->select();

        $user_follow_upper_limit = $params['user_follow_upper_limit'];
        $total_task_num = 0;
        foreach ($members as &$member) {
            // 该账号今日关注次数
            $member_follow_num = db("followuser")->where("uid", $member['uid'])->whereDay("create_time")->count();
            $member_task_num = ($user_follow_upper_limit - $member_follow_num);
            $member['today_follow_num'] = $member_follow_num;
            if (!$member_task_num) {
                continue;
            }
            $total_task_num += $member_task_num;
        }
        $external_members = ExternalmemberModel::where(['secret' => 0])
            ->where(function ($query) use ($params) {
                if ($params['follower_status']) $query->where('follower_count', '<', $params['follower_status']);
                if ($params['following_count']) $query->where('following_count', '<', $params['following_count']);
                if ($params['total_favorited']) $query->where('favoriting_count', '<', $params['total_favorited']);
                $black_list = $params['black_list'];
                if (in_array("no_avatar", $black_list)) {
                    $query->where("has_avatar", 0);
                }
                if (in_array("no_aweme", $black_list)) {
                    $query->where("aweme_count", '>', 0);
                }
                if (in_array("historical_users", $black_list)) {
                    $query->where("is_follow", 1);
                }
                if (in_array("no_nickname", $black_list)) {
                    $query->where("has_nickname", 0);
                }
            })
            ->where(['tasklist_id' => ['in', implode($params['tasklist_id_list'], ",")], 'country' => ['in', implode($params['country_list'], ",")]])
            ->field("uid,sec_uid")->select()->toArray();

        if (count($external_members) < $total_task_num) {
            throw new ValidateException('当前条件下可关注博主仅剩' . count($external_members) . '个');
        }
        checkTaskNum($total_task_num);
        $redis_key = get_task_key('follow');
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => $task_type,
            "task_num" => $total_task_num,
            "create_time" => time(),
            'redis_key' => $redis_key,
            "status" => 1,
            "complete_num" => 0,
            'api_user_id' => $this->request->uid
        ];
        $task_id = db("tasklist")->insertGetId($task);
        //往中间表中添加数据

        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        unset($member);
        foreach ($members as $member) {
            //往中间表中添加数据
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $user_follow_upper_limit;
            $task_uid_id = db('task_uid')->insertGetId($uid_task);
            for ($i = 0; $i < ($user_follow_upper_limit - $member['today_follow_num']); $i++) {
                if ($external_members) {
                    $delay = rand($params['rate_min'], $params['rate_max']); //关注频率，延迟多少秒执行
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $external_member_index = array_rand($external_members);
                    $external_member = $external_members[$external_member_index];
                    unset($external_members[$external_member_index]);
                    if ($external_member) {
                        $token = doToken($member['token']);
                        //取http代理
                        $proxy = getHttpProxy($token['user']['uid']);

                        $parameter = [
                            'member_uid' => $member['uid'],
                            'user_id' => $external_member['uid'],
                            'sec_user_id' => $external_member['sec_uid'],
                            'from' => 19,
                            'from_pre' => 13,
                            'channel_id' => 3,
                            'type' => 1, // # 1 表示关注，0 表示取消关注
                            "token" => $token,
                            "proxy" => $proxy
                        ];
                        $task_detail = [
                            'task_uid_id' => $task_uid_id,
                            "tasklist_id" => $task_id,
                            "parameter" => $parameter,
                            "status" => 1,
                            "create_time" => time(),
                            "task_type" => $task_type,
                            "crux" => $member['uid']
                        ];
                        unset($task_detail['tasklistdetail_id']);
                        //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                        $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                        $task_detail['tasklistdetail_id'] = $task_detail_id;
                        queue(FollowTask::class, $task_detail, $delay);
                    }
                }
            }


        }
    }

    /**
     * @api {post} /Push/comment_digg 02、发布评论点赞任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布评论点赞任务
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_digg_upper_limit] 单号关注上限
     * @apiParam (输入参数：) {int}              [can_fail_num] 连续失败次数
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function comment_digg()
    {
        $params = $this->request->post();
        $task_type = "CommentDigg";

        //验证规则
        $rule = [
            'country_list' => 'require',
            'user_digg_upper_limit' => 'require',
            'can_fail_num' => 'require',
            'tasklist_id_list' => 'require',
            'task_name' => 'require'
        ];

        //错误提示
        $msg = [
            'country_list.require' => '国家必传',
            'user_digg_upper_limit.require' => 'user_digg_upper_limit（单号点赞上限）必传',
            'can_fail_num.require' => 'can_fail_num（可失败次数）必传',
            'tasklist_id_list.require' => 'tasklist_id_list（数据来源（采集任务ID））必传',
            'task_name.require' => '任务名称必传',
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }

        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单类型', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }
        //操作用户查询
        $uidlist = $params['uid_list'];
        $typecontrol_id = $params["typecontrol_id"] ?? $params["typecronl_id"];
        if (empty($uidlist) && (empty($typecontrol_id))) {
            throw new ValidateException('uidlist和分组分类二选一必传');
        }
        $wherelist = [];
        if ($uidlist && $typecontrol_id || $uidlist && !$typecontrol_id) {
            $idx = implode(",", $uidlist);
            $wherelist[] = ['uid', 'in', $idx];
        }
        if ($typecontrol_id && !$uidlist) {
            $wherelist['typecontrol_id'] = $typecontrol_id;
        }
        $members = db('member')->where($wherelist)->where('status', 1)->field('uid,sec_uid,unique_id,token')->select();
        $user_digg_upper_limit = $params['user_digg_upper_limit'];
        $task_num = count($members) * $user_digg_upper_limit;
        // $new_task_num = min(count($comment_list), $task_num);
        //  echo '111';
        // var_dump($task_num);
        // var_dump(count($comment_list));die;
        checkTaskNum($new_task_num);
        $redis_key = get_task_key($task_type);
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => $task_type,
            "task_num" => $task_num,
            "create_time" => time(),
            'redis_key' => $redis_key,
            "status" => 1,
            "complete_num" => 0,
            "can_fail_num" => $params['can_fail_num'],
            'api_user_id' => $this->request->uid,
            'member_num'=>count($members),
            'typecontrol_id'=>$params["typecontrol_id"]
        ];
        $task_id = db("tasklist")->insertGetId($task);
        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        $redis = connectRedis();
        //被操作用户查询
        $comment_list = db('comment_list')
            ->where(function ($query) use ($params) {
                $query->whereRaw(in_to_or($params['country_list'], 'account_region'));
                $query->whereRaw(in_to_or($params['tasklist_id_list'], 'tasklist_id'));
                if ($params['comment_digg_count_lower_limit']) {
                    $query->where('digg_count', '<', $params['comment_digg_count_lower_limit']);
                }
                $black_list = $params['black_list'];
                if (in_array("no_avatar", $black_list)) {
                    $query->where("has_avatar", 1);
                }
                if (in_array("no_aweme", $black_list)) {
                    $query->where("aweme_count", '>', 0);
                }
                if (in_array("historical_users", $black_list)) {
                    $query->where("is_digg", 1);
                }
                if (in_array("no_nickname", $black_list)) {
                    $query->where("has_nickname", 1);
                }
            })
            ->field("cid,aweme_id,text")->select()->toArray();
        $task_details = [];
        foreach ($members as $member) {
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $user_digg_upper_limit;
            $task_uid_id = db('task_uid')->insertGetId($uid_task);
            if ($comment_list) {
                for ($i = 0; $i < $user_digg_upper_limit; $i++) {
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $comment_index = array_rand($comment_list);
                    $comment = $comment_list[$comment_index];
                    unset($comment_list[$comment_index]);

                    $token = doToken($member['token']);
                    //取http代理
                    $proxy = getHttpProxy($token['user']['uid']);
                    db('comment_list')->where('cid',$comment['cid'])->update(['is_digg'=>0]);
                    db('tasklist')->where('tasklist_id',$task_id)->inc('creation_num')->update();
                    $parameter = [
                        'aweme_id' => $comment['aweme_id'],
                        'cid' => $comment['cid'],
                        'text'=> $comment['text'],
                        'uid' => $member['uid'],
                        "token" => $token,
                        "proxy" => $proxy
                    ];
                    if($i==0){
                        $task_detail = [
                            'task_uid_id' => $task_uid_id,
                            "tasklist_id" => $task_id,
                            "parameter" => $parameter,
                            "status" => 1,
                            "create_time" => time(),
                            "task_type" => $task_type,
                            "crux" => $member['uid']
                        ];
                        unset($task_detail['tasklistdetail_id']);
                        //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                        $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                        $task_detail['tasklistdetail_id'] = $task_detail_id;
                        $task_details[] = $task_detail;
                    }else{
                        $task_detail = [
                            'task_uid_id' => $task_uid_id,
                            "tasklist_id" => $task_id,
                            "parameter" => $parameter,
                            "status" => 5,
                            "create_time" => time(),
                            "task_type" => $task_type,
                            "crux" => $member['uid']
                        ];
                        unset($task_detail['tasklistdetail_id']);
                        //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                        $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                        // $task_detail['tasklistdetail_id'] = $task_detail_id;
                        // $task_details[] = $task_detail;
                    }
                }
            }
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }


    /**
     * @api {post} /Push/video 02、发布视频任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布视频任务
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {int}              [video_num] 视频数量
     * @apiParam (输入参数：) {int}              [label_num] 标签数量
     * @apiParam (输入参数：) {int}              [user_num] @用户数量
     * @apiParam (输入参数：) {int}              [push_time_start] 发布开始时间
     * @apiParam (输入参数：) {int}              [push_time_end] 发布结束时间
     * @apiParam (输入参数：) {string}           [text] 主题内容
     * @apiParam (输入参数：) {bool}             [text_round] 是否随机主题内容
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function video()
    {
        $params = $this->request->post();
        $task_type = "PushVideo";

        //验证规则
        $rule = [
            'video_num' => 'require',
            'task_name' => 'require'
        ];

        //错误提示
        $msg = [
            'video_num.require' => '视频数量必传',
            'task_name.require' => '任务名称必传',
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }

        // $uid_list = $params["uid_list"];
        $typecontrol_id = $params["typecontrol_id"] ?? $params["typecronl_id"];

        $video_num = $params["video_num"];
        $label_num = $params["label_num"];
        $user_num = $params["user_num"];
        $task_name = $params["task_name"];
        $push_time = $params['push_time'];
        $uidlist = $params['uid_list'];
        $text = $params["text"];
        $text_round = $params["text_round"];
        if (!$text && !$text_round) {
            throw new ValidateException("主题内容与是否随机主题必传其一");
        }
        if (empty($uidlist) && (empty($typecontrol_id))) {
            throw new ValidateException('uidlist和分组分类二选一必传');
        }
        $wherelist = [];
        if ($uidlist && $typecontrol_id || $uidlist && !$typecontrol_id) {
            $idx = implode(",", $uidlist);
            $wherelist[] = ['uid', 'in', $idx];
        }
        if ($typecontrol_id && !$uidlist) {
            $wherelist['typecontrol_id'] = $typecontrol_id;
        }
        $uid_list = db('member')->where($wherelist)->field('token,uid')->select()->toArray();
        // var_dump($uid_list);die;
        $total_need_push_video_num = count($uid_list) * $video_num;
        $can_use_video_num = db("material")->where(['typecontrol_id' => $typecontrol_id, 'status' => 1])->count();
        if ($can_use_video_num < $total_need_push_video_num) {
            throw new ValidateException('视频素材库可用数量不足，剩余' . $can_use_video_num);
        }
        $text_list = [];
        if ($text_round) {
            $total_need_push_text_num = count($uid_list) * $video_num;
            $can_use_text_num = db("subjectcontent")->where(['typecontrol_id' => $typecontrol_id])->count();
            if ($can_use_text_num < $total_need_push_text_num) {
                throw new ValidateException('主题内容素材库可用数量不足，剩余' . $can_use_text_num);
            }
            $text_list = db("subjectcontent")->where(['typecontrol_id' => $typecontrol_id, 'status' => 1])->limit($total_need_push_text_num)->field("subjectcontent_id,content")->orderRaw("rand()")->select();
        }
        // var_dump($text_list);die;
        checkTaskNum($total_need_push_video_num);
        $redis_key = get_task_key($task_type);
        $task = [
            "task_name" => $task_name,
            "task_type" => $task_type,
            "task_num" => $total_need_push_video_num,
            "create_time" => time(),
            "api_user_id" => $this->request->uid,
            'redis_key' => $redis_key,
            'tmp_redis_key' => '',
            "status" => 1,
            "complete_num" => 0,
            'member_num'=>count($uid_list),
            'typecontrol_id'=>$params["typecontrol_id"]
        ];
        $task_id = db("tasklist")->insertGetId($task);
        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();

        $redis = connectRedis();
        $task_details = [];
        $i = 0;
        foreach ($uid_list as $uid) {
            $uid_task['uid'] = $uid['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $video_num;
            $task_uid_id = db('task_uid')->insertGetId($uid_task);
            //取登录后的token
//            $user_info = db('member')->field('token')->where(['uid' => $uid, 'status' => 1])->find();
//            if (empty($user_info)) continue;

            $video_list = db("material")->where(['typecontrol_id' => $typecontrol_id, 'status' => 1])->limit($video_num)->field("video_url,material_id")->orderRaw("rand()")->select();
            foreach ($video_list as $k => $item) {
                db('tasklist')->where('tasklist_id',$task_id)->inc('creation_num')->update();
                $domain = config("my.host_url");
                $video_url = $domain . $item['video_url'];
                db('material')->where('material_id', $item['material_id'])->update(['status' => 0]);
                //获取随机主题内容
                if ($text_round && $text_list) {
                    $text = $text_list[$i]['content'];
                    unset($text_list[$i]);
                }
                $label_str = $user_str = '';
                //组装标签
                if ($label_num) {
                    $label_list = db("label")->where("status", 1)->limit($label_num)->field("label")->orderRaw("rand()")->select();
                    foreach ($label_list as $row) {
                        $label_str = $label_str . " #" . trim($row['label']) . ' ';
                    }
                }
                //组装@用户
                if ($user_num) {
                    $user_list = db("member")->where("status", 1)->limit($user_num)->field("unique_id")->orderRaw("rand()")->select();
                    foreach ($user_list as $row) {
                        $user_str = $user_str . " @" . $row['unique_id'];
                    }
                }
                //组装要发布的主题内容
                $texts = $text . $label_str . $user_str;
                $token = doToken($uid['token']);
                //取http代理
                $proxy = getHttpProxy($uid['uid']);

                $parameter = ["video_url" => $video_url, "text" => $texts, "uid" => $uid['uid'], "token" => $token, "proxy" => $proxy, "material_id" => $item['material_id']];
                unset($task_detail);
                if($k==0){
                    $task_detail = [
                        'task_uid_id' => $task_uid_id,
                        "tasklist_id" => $task_id,
                        "parameter" => $parameter,
                        "api_user_id" => $this->request->uid,
                        "create_time" => time(),
                        "task_type" => $task_type,
                        "status"=> 1,
                        "crux" => $uid['uid']
                    ];
                    unset($task_detail['tasklistdetail_id']);
                    //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                    $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                    $task_detail['tasklistdetail_id'] = $task_detail_id;
                    $task_details[] = $task_detail;
                }else{
                    $task_detail = [
                        'task_uid_id' => $task_uid_id,
                        "tasklist_id" => $task_id,
                        "parameter" => $parameter,
                        "api_user_id" => $this->request->uid,
                        "create_time" => time(),
                        "task_type" => $task_type,
                        "status"=> 5,
                        "crux" => $uid['uid']
                    ];
                    unset($task_detail['tasklistdetail_id']);
                    //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                    $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                }
                $i++;
            }
            //return $this->ajaxReturn($this->successCode, "视频任务发布成功");
        }

        // return json_encode($task_details);
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }
}