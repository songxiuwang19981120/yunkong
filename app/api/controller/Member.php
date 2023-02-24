<?php
/*
 module:		账户管理
 create_time:	2022-11-19 14:16:56
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Member as MemberModel;
use app\api\service\MemberService;
use think\exception\ValidateException;
use think\facade\Cache;
use SplFileInfo;

class Member extends Common
{
    protected $noNeedLogin = ["Member/MemberList","Member/getimage","Member/add"];

    /**
     * @api {post} /Member/index 01、首页数据列表
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {int}            [max] 粉丝最大值
     * @apiParam (输入参数：) {int}            [min] 粉丝最小值
     * @apiParam (输入参数：) {string}         [order] 需要排序的字段
     * @apiParam (输入参数：) {string}         [sort] 排序方式 desc 从大到小 asc 从小到大
     * @apiParam (输入参数：) {int}            [grouping_id] 分组id
     * @apiParam (输入参数：) {int}            [typecontrol_id] 分类id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":"{
         "lit":[
     * {
     * "member_id": "账户id ，我们平台账户的唯一标识",
     * "phone_number": "",
     * "backups_name": "",
     * "uid": "用户uid，tiktok账户的唯一标识",
     * "token": "",
     * "avatar_thumb": "账户头像地址",
     * "follower_status": "粉丝数量",
     * "following_count": "关注别人数量",
     * "total_favorited": "获得赞数量",
     * "nickname": "昵称",
     * "unique_id": "unique_id ，https://www.tiktok.com/@拼上unique_id可以跳转到tiktok账户的首页",
     * "signature": "个性签名",
     * "status": "	状态1=正常0=封禁2=登出2096私密账号3002290=个人资料查看历史记录不可用	",
     * "country": "国家",
     * "member_type": "账户类型：0: 个人账号，1: 创作者账号，3: 企业账号",
     * "sec_uid": "sec_uid",
     * "max_cursor": "0",
     * "has_more": "0",
     * "aweme_count": "账户作品的数量",
     * "ifup": "0",
     * "updata_time": "账户最新的更新时间",
     * "ifpic": "0",
     * "unread_viewer_count": "账户主页来访人数未读的数量",
     * "grouping_id": "分组id",
     * "typecontrol_id": "分类id",
     * "addtime": "账户首次进入的时间",
     * "no_avatar": "有无头像0=有头像，1=没头像",
     * "del": "是否删除 1正常0删除",
     * "grouping_name": "分组名称",
     * "type_title": "分类名称",
     * "play_num": "1",
     * "pro_type": "项目分类"
     * }
     * ],
     * "count": "总数据"
     * }"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function index()
    {   
        // $uid = 66;
        // $ttl = 1;
        // $visitsRes = $this->api_visits('Member_index',$ttl,$uid);
        // // var_dump($visitsRes);die;
        // if($visitsRes){
        // 	throw new ValidateException('访问过于频繁');
        // }
        // var_dump(111);die;
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $where = [];
        $where['a.uid'] = $this->request->post('uid', '', 'serach_in');
        $where['a.unique_id'] = $this->request->post('unique_id', '', 'serach_in');
        $where['a.typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
        $where['del'] = 1;
        // $where['api_user_id'] = $this->request->uid;
        $max = $this->request->post('max');
        $min = $this->request->post('min');
        if ($min && $max) {
            $where['follower_status'] = ['between', [$min, $max]];
        }
        $field = 'a.member_id,a.phone_number,a.backups_name,a.uid,a.avatar_thumb,a.head_image,a.follower_status,a.following_count,a.total_favorited,a.nickname,a.unique_id,a.signature,a.status,a.country,a.aweme_count,a.updata_time,a.unread_viewer_count,a.typecontrol_id,a.api_user_id,c.type_title,a.no_avatar';

        $order = $this->request->post('order', '', 'serach_in');
        $sort = $this->request->post('sort', '', 'serach_in');
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'status desc';

        $resp = \app\api\model\Member::alias('a')->join('typecontrol c', 'c.typecontrol_id = a.typecontrol_id', 'left')
            ->where($this->apiFormatWhere($where, MemberModel::class))->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        $res = ['list' => $resp['data'], 'count' => $resp['total']];
        foreach ($res['list'] as &$row) {
            $arr = db('membervideo')->where('member_id', $row['member_id'])->field('sum(play_count) as play_num,sum(share_count) as share_count, sum(collect_count) as collect_count,sum(download_count) as download_count')->find();
            $row['play_num'] = $arr['play_num']; //总播放数
            $row['share_count'] = $arr['share_count']; //总分享数
            $row['collect_count'] = $arr['collect_count']; //总收藏数
            $row['download_count'] = $arr['download_count']; //总下载数
            $row['updata_time'] = date("Y-m-d H:i:s", $row['updata_time']);
        }
        if ($where['a.typecontrol_id']) {
            $arrs = db('member')->where(['typecontrol_id' => $where['a.typecontrol_id'], 'status' => 1])->field('sum(follower_status) as total_fans,sum(following_count) as total_follow')->find();
            $res['total_fans'] = $arrs['total_fans'];
            $res['total_follow'] = $arrs['total_follow'];

        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }
    
function api_visits($action,$ttl=500,$uid='')
{   
    $redis = connectRedis();
    $redis->select(7);
    $key = "user_{$uid}_api_{$action}";
    $visits = $redis->get($key);
    // var_dump($key);die;
    if($visits){
        return false;
    }else{
        $redis->setex($key,$ttl,1);
        return true;

    }
}


    function MemberList()
    {
        $limit = $this->request->get('limit', 20, 'intval');
        $page = $this->request->get('page', 1, 'intval');
        $where = [];
        $where['typecontrol_id'] = $this->request->get('typecontrol_id', '', 'serach_in');
        $where['api_user_id'] = $this->request->get('api_user_id', 0, 'intval');
        // $where['status'] = 1;
        $orderby = 'member_id desc';
        $field = "*";
        $res = MemberService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['type_title'] = db('typecontrol')->where('typecontrol_id', $row['typecontrol_id'])->value('type_title');
            // $row['pro_type'] = '项目分类';
            $row['updata_time'] = date("Y-m-d H:i:s", $row['updata_time']);
        }

        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }


    //用户单独上传视频
    function UserPushVideo()
    {
        $uid = $this->request->post('uid');
        $video_url = $this->request->post('video_url');
        $text = $this->request->post('text');
        $label = $this->request->post('label');
        $task_name = $this->request->post('task_name');
        if (empty($uid) && empty($video_url)) {
            throw new ValidateException('参数错误');
        }
        $user = db('member')->where('uid', $uid)->field('uid,sec_uid,token,status')->find();
        if ($user['status'] != 1) {
            throw new ValidateException('账号不正常');
        }
        if ($label) {
            $labels = explode("\n", $label);
            $label_str = "";
            foreach ($labels as $item) {
                $label_str = $label_str . " #" . trim($item);
            }
        }
        $test = $text . $label_str;
        $redis_key = get_task_key('PushVideo');
        $addtask['task_name'] = $task_name;
        $addtask['task_type'] = 'PushVideo';
        $addtask['task_num'] = 1;
        $addtask['redis_key'] = $redis_key;
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);
        $uid_task['uid'] = $user['uid'];
        $uid_task['tasklist_id'] = $usertask;
        $uid_task['num'] = 1;
        $uid_tasks = db('task_uid')->insertGetId($uid_task);
        $redis = connectRedis();
        // foreach ($user as $k => $v) {
        $v['uid'] = $user['uid'];
        $v['proxy'] = getHttpProxy($user['uid']);
        $v['token'] = json_decode($user['token'], true);
        $v['text'] = $test;
        $v['video_url'] = $video_url;
        // var_dump(json_encode($v));die;
        $adddata['parameter'] = json_encode($v);
        $adddata['create_time'] = time();
        $adddata['task_type'] = 'PushVideo';
        $adddata['tasklist_id'] = $usertask;
        $adddata['crux'] = $user['uid'];
        $adddata['task_uid_id'] = $uid_tasks;
        // unset($adddata['tasklistdetail_id']);
        $arr = db('tasklistdetail')->insertGetId($adddata);
        $adddata['tasklistdetail_id'] = $arr;
        $adddata['parameter'] = json_decode($adddata['parameter'], true);
        $redis->lPush($redis_key, json_encode($adddata));
        // }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


    /**
     * @api {post} /Member/auto_follow_status 01、获取自动回关状态
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  获取自动回关状态
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回状态码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"获取成功","data":true or false}
     */
    function auto_follow_status()
    {
        $key = "auto_follow_" . $this->request->uid;
        return $this->ajaxReturn($this->successCode, '获取成功', Cache::has($key));
    }

    /**
     * @api {post} /Member/auto_follow_on 01、自动回关开启
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  自动回关开启
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回状态码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"自动回关已开启"}
     */
    function auto_follow_on()
    {
        $key = "auto_follow_" . $this->request->uid;
        if (Cache::has($key)) {
            throw new ValidateException('自动回关已开启');
        }
        Cache::set("auto_follow_" . $this->request->uid, ['create_time' => time()]);
        return $this->ajaxReturn($this->successCode, '成功');
    }


    /**
     * @api {post} /Member/auto_follow_off 01、自动回关关闭
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  自动回关关闭
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回状态码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"未开启自动回关"}
     */
    function auto_follow_off()
    {
        $key = "auto_follow_" . $this->request->uid;
        if (!Cache::has($key)) {
            throw new ValidateException('未开启自动回关');
        }
        Cache::delete($key);
        return $this->ajaxReturn($this->successCode, '成功');
    }

    /**
     * @api {post} /Member/check_refresh_update 01、检查是否能够刷新用户信息
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  检查是否能够刷新用户信息
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回状态码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"可以刷新"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"任务正在进行"}
     */
    function check_refresh_update()
    {
        $key = 'last_refresh_update_data_' . $this->request->uid;
        if (Cache::has($key)) {
            $last = Cache::get($key);
            $jg = (time() - $last['create_time']) < 10 * 60;
            $sy = 10 * 60 - (time() - $last['create_time']);
            if ($last['user_id'] == $this->request->uid && $jg) {
                throw new ValidateException("任务正在进行");
            }
        }
        return $this->ajaxReturn($this->successCode, '可以刷新');
    }

    /**
     * @api {post} /Member/refresh_update 01、手动更新分组分类下的账号数据
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  手动更新分组分类下的账号数据
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {int}            [grouping_id] 分组id
     * @apiParam (输入参数：) {int}            [typecontrol_id] 分类id
     *
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回状态码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":"{}"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function refresh_update()
    {
//        var_dump(retry_task(1974));die();

        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $typecontrol_id = $this->request->post('typecontrol_id', '', 'serach_in');
        $uid_list = $this->request->post('uid_list');
        // $parameter = $this->request->post();
         //操作用户查询
    //   var_dump($uid_list);die;
        if (empty($uid_list) && (empty($typecontrol_id))) {
            throw new ValidateException('uidlist和分组分类二选一必传');
        }
        $wherelist = [];
        if ($uid_list && (empty($typecontrol_id))) {
            $idx = implode(",", $uid_list);
            $wherelist[] = ['uid', 'in', $idx];
        }
        if ($typecontrol_id) {
            $wherelist['typecontrol_id'] = $typecontrol_id;
        }
        // var_dump($members);die;
        $key = 'last_refresh_update_data_' . $this->request->uid;
        // Cache::delete($key);
        if (Cache::has($key)) {
            $last = Cache::get($key);
            $jg = (time() - $last['create_time']) < 10 * 60;//1 * 60 * 60
            $sy = 10 * 60 - (time() - $last['create_time']);
            if ($last['user_id'] == $this->request->uid && $jg) {
                throw new ValidateException("手动刷新频繁，间隔时间剩余：" . $sy . " 秒");
            }
        }
        $members = MemberModel::where($wherelist)->field('token,uid,sec_uid')->select()->toArray();
        $user = $this->auth->getUser();
        $task_key = get_task_key('UpdateUserData');
        $task = [
            "task_name" => '管理员' . $user->username . '手动刷新用户数据',
            "task_type" => "UpdateUserData",
            "task_num" => count($members),
            'redis_key' => $task_key,
            "create_time" => time(),
            'api_user_id' => $this->request->uid,
            "status" => 1,
            "complete_num" => 0
        ];
        $task_id = db("tasklist")->insertGetId($task);
        Cache::set($key, ['user_id' => $this->request->uid, 'create_time' => time(), 'task_id' => $task_id], 10 * 60);
        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        $task_details = [];
        $redis = connectRedis();
        foreach ($members as $member) {
            $token = doToken('', 2);
            //取http代理
            $proxy = getHttpProxy($token['user']['uid']);

            $parameter = [
                "uid" => $member['uid'],
                "sec_uid" => $member['sec_uid'],
                "token" => $token,
                "proxy" => $proxy
            ];
            $task_detail = [
                "tasklist_id" => $task_id,
                "parameter" => json_encode($parameter),
                "status" => 1,
                "create_time" => time(),
                "task_type" => 'UpdateUserData',
                "crux" => $member['uid']
            ];
            unset($task_detail['tasklistdetail_id']);
            db('tasklist')->where('tasklist_id',$task_id)->inc('creation_num')->update();
            $task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
            $task_detail['tasklistdetail_id'] = $task_detail_id;
            $task_detail['parameter'] = $parameter;
            $task_details[] = $task_detail;
        }
        foreach ($task_details as $detail) {
            $redis->lPush($task_key, json_encode($detail));
        }
    }


    /**
     * @api {post} /Member/UpdateMemberTypecontrol 04、批量修改账户分类分组
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {int}            [grouping_id] 分组id
     * @apiParam (输入参数：) {int}            [typecontrol_id] 分类id
     *
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":"{}"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */

    function UpdateMemberTypecontrol()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $where['uid'] = $this->request->post('user_id', '', 'serach_in');
        $data = $this->request->post('typecontrol_id');
        if (empty($where['uid'])) {
            throw new ValidateException('参数错误');
        }
        // var_dump($data);
        if($data){
            // var_dump(111);
            $res = MemberModel::where($where)->update($data);
        }
        die;
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }


    function GetUserLists()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $data = $this->request->post();

        if (empty($data)) {
            throw new ValidateException('参数错误');
        }
        if ($data['user_id']) {
            $where['uid'] = $data['user_id'];
            $userss = db('member')->where($where)->value('uid');
            //  print_r($userss);die;
            if ($userss) {
                return $this->ajaxReturn($this->successCode, '重复数据');
            } else {
                $user = $data['note']['user'];
                $updata['country'] = $this->transCountryCode($data['region']);
                $updata['avatar_thumb'] = $data['avatar_pic'];//$this->downloadImage();
                $updata['follower_status'] = $user['follower_count'];
                $updata['following_count'] = $user['following_count'];
                $updata['total_favorited'] = $user['total_favorited'];
                $updata['nickname'] = $user['nickname'];
                $updata['unique_id'] = $user['unique_id'];
                $updata['signature'] = $user['signature'];
                $updata['member_type'] = $user['account_type'];
                $updata['uid'] = $user['uid'];
                $updata['sec_uid'] = $user['sec_uid'];
                $updata['aweme_count'] = $user['aweme_count'];
                $res = MemberModel::insert($updata);
                // var_dump($res);die;
                if ($res) {
                    return $this->ajaxReturn($this->successCode, '操作成功', $res);
                } else {
                    throw new ValidateException('入库失败');
                }
            }

        } else {
            throw new ValidateException('user_id为空');
        }

        // print_r($info);die;
    }


    /**
     * @api {post} /Member/add 02、添加
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            token token
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function add()
    {
//         if(!$this->request->isPost()){
// 			throw new ValidateException('请求错误');
// 		}
        $data = $this->request->post();
        $getdata = $this->request->get();
        return $data['user'];
        $gjtype = explode('=', $data['common-params']);
        $gj = explode('&', $gjtype[1]);
// 		var_dump($gj[0]);die;
        if (empty($data)) {
            throw new ValidateException('参数错误');
        }
        $token_str = str_replace('&quot;', '"', $data);
        $token_str = str_replace('&amp;', '&', $token_str);
        // var_dump($token_str);die;
        $arr['token'] = json_encode($token_str);
        $arr['phone_number'] = $getdata['phone_number'];
        $arr['backups_name'] = $getdata['backups_name'];
        $arr['uid'] = $data['user']['uid'];
        $arr['sec_uid'] = $data['user']['sec_uid'];
        $arr['country'] = $this->transCountryCode($gj[0]);
        $arr['nation'] = $gj[0];
        $memberinfo = MemberModel::where('uid', $data['user']['uid'])->find();
        if ($memberinfo) {
            $res = MemberModel::where('uid', $data['user']['uid'])->update($arr);
            $msg = '更新成功' . $arr['uid'];
        } else {
            $arr['addtime'] = time();
            $arr['typecontrol_id'] = 3;
            $res = MemberService::add($arr);
            $msg = '添加成功';
            // $this->GetMemberInfo($arr['uid']);
        }
        return $this->ajaxReturn($this->successCode, $msg, $res);
    }

    function getuserinfo()
    {
        set_time_limit(0);
        $where['ifup'] = 1;
        $uid = db('member')->where($where)->find();
        if ($uid) {
            $url = 'http://47.245.30.4:9999/rest/index/getuserinfo';
            $data['user_id'] = $uid['uid'];
            $data['sec_user_id'] = $uid['sec_uid'];
            $userinfo = $this->doHttpPost($url, $data);
            print_r($userinfo);
            die;
            $info = json_decode($userinfo, true);
            if ($info['result'] == 0) {
                $user = $info['data']['user'];
                $updata['avatar_thumb'] = $info['data']['avatar_pic'];
                $updata['follower_status'] = $user['follower_count'];
                $updata['following_count'] = $user['following_count'];
                $updata['total_favorited'] = $user['total_favorited'];
                $updata['nickname'] = $user['nickname'];
                $updata['unique_id'] = $user['unique_id'];
                $updata['signature'] = $user['signature'];
                $updata['member_type'] = $user['account_type'];
                $updata['aweme_count'] = $user['aweme_count'];
                $updata['ifup'] = 0;
                $updata['updata_time'] = time();
                $res = db('member')->where('uid', $uid)->update($updata);
                return $this->ajaxReturn($this->successCode, '操作成功', $res);
            } else {
                throw new ValidateException($info['message']);
            }
        } else {
            echo '没有可更新的了';
        }

    }

    function getuserinfotwo()
    {
        set_time_limit(0);
        $where['ifup'] = 1;
        $uid = db('member')->where($where)->value('uid');
        // $user['uid'] = $uid;
        // $updatas['ifup'] = 0;
        // MemberModel::where($user)->update($updatas);
        if ($uid) {
            $url = 'http://47.245.30.4:9999/rest/index/getuserinfo';
            $data['user_id'] = $uid;
            $userinfo = $this->doHttpPost($url, $data);
            // print_r($userinfo);die;
            $info = json_decode($userinfo, true);
            if ($info['result'] == 0) {
                $user = $info['data']['user'];
                $updata['avatar_thumb'] = $info['data']['avatar_pic'];
                $updata['follower_status'] = $user['follower_count'];
                $updata['following_count'] = $user['following_count'];
                $updata['total_favorited'] = $user['total_favorited'];
                $updata['nickname'] = $user['nickname'];
                $updata['unique_id'] = $user['unique_id'];
                $updata['signature'] = $user['signature'];
                $updata['member_type'] = $user['account_type'];
                $updata['aweme_count'] = $user['aweme_count'];
                $updata['ifup'] = 0;
                $updata['ifpic'] = 1;
                $updata['updata_time'] = time();
                $res = db('member')->where('uid', $uid)->update($updata);
                return $this->ajaxReturn($this->successCode, '操作成功', $res);
            } else {
                throw new ValidateException($info['message']);
            }
        } else {
            echo '没有可更新的了';
        }

    }

    /**
     * @api {post} /Member/delete 03、删除
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            member_ids 主键id 注意后面跟了s 多数据删除
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    function delete()
    {
        $idx = $this->request->post('member_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data = explode(',', $idx);
        try {
            foreach ($data as $v){
                // $updata['del'] = 0;
                // var_dump($v);die;
                db('member')->where('member_id',$v)->update(['del'=>0]);
            }
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    //修改用户数据
    function update()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $member_id = $this->request->post('member_id', '', 'serach_in');
        $nickname = $this->request->post('nickname', '', 'serach_in');
        $type = $this->request->post('type', '', 'serach_in');
        $signature = $this->request->post('signature', '', 'serach_in');
        $avatar_thumb = $this->request->post('avatar_thumb', '', 'serach_in');
        $unique_id = $this->request->post('unique_id', '', 'serach_in');
        if (empty($member_id) || empty($type)) {
            throw new ValidateException('参数错误');
        }
        $member_info = MemberModel::where('member_id', $member_id)->value('uid');
        $url = 'http://47.245.30.4:9999/rest/index/index';
        if ($type == 1) {
            $data['nickname'] = $nickname;
        } elseif ($type == 2) {
            $data['signature'] = $signature;
        } elseif ($type == 3) {
            $data['avatar_uri'] = $this->image($member_info, $avatar_thumb);
            //  var_dump($data['avatar_uri']);die;
        } else {
            $data['unique_id'] = $unique_id;
        }
        $data['type'] = $type;
        $data['user_id'] = $member_info;
        $userinfo = $this->doHttpPost($url, $data);
        $info = json_decode($userinfo, true);
        // var_dump($userinfo);die;
        if ($info['result'] == 0) {
            $user = $info['data']['user'];
            $res = MemberModel::where('uid', $user['uid'])->update($data);
            if ($res !== false) {
                return $this->ajaxReturn($this->successCode, '更新成功', $res);
            } else {
                throw new ValidateException('更新失败');
            }
        } else {
            throw new ValidateException($info['message']);
        }
    }

    function image($token, $image)
    {
        $url = 'http://47.245.30.4:9999/rest/index/uploadimg';
        $data['user_id'] = $token;
        $imageurl = file_get_contents('/www/wwwroot/192.168.4.30/admin.com/public' . $image);
        $pic = base64_encode($imageurl);
        // var_dump($imageurl);die;
        $data['image'] = $pic;
        $userinfo = $this->doHttpPost($url, $data);
        $info = json_decode($userinfo, true);
        if ($info['result'] == 0) {
            return $info['data']['uri'];
        } else {
            throw new ValidateException($info['message']);
        }
    }

    //接收用户数据
    function ReceiveUserData()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $data = $this->request->post();
        if (empty($data)) {
            throw new ValidateException('参数错误');
        }
// 		print_r($data['user']);die;
        if ($data['status_code'] == 0) {
            $user = $data['user'];
            $updata['avatar_thumb'] = $user['avatar_medium']['url_list'][0];
            $updata['follower_status'] = $user['follower_count'];
            $updata['following_count'] = $user['following_count'];
            $updata['total_favorited'] = $user['total_favorited'];
            $updata['nickname'] = $user['nickname'];
            $updata['unique_id'] = $user['unique_id'];
            $updata['signature'] = $user['signature'];
            $updata['member_type'] = $user['account_type'];
            $updata['aweme_count'] = $user['aweme_count'];
            $updata['updata_time'] = time();
            $updata['ifpic'] = 1;
            $res = db('member')->where('uid', $user['uid'])->update($updata);
            if ($res !== false) {
                return $this->ajaxReturn($this->successCode, '更新成功' . $user['uid']);
            } else {
                throw new ValidateException('更新失败');
            }
        } else {
            throw new ValidateException('数据错误');
        }

    }

    function getuserinfos()
    {
        $where['ifup'] = 1;//获取对象的uid
        $uid = db('member')->where($where)->find();
        //获取授权信息
        $access = db('information')->where('information_id', 1)->value('access_token');
        //随机游客token
        $sjtoken = db('user_token_list')->orderRaw('rand()')->limit(1)->select()->toArray();
        $token = $sjtoken[0]['token'];
        $jstoken = json_decode($token, true);
        //代理
        // $proxy = db('user_token_log')->where('user_id',$jstoken['user']['uid'])->order('id desc')->value('user_proxy');
        $proxy = $this->CurlRequest(config('my.TT_PRO_HTTP'));
        //参数
        $params = [
            'user_id' => $uid['uid'],//被看的人信息
            'sec_user_id' => $uid['sec_uid'] //被看得人
        ];
        $data = [
            'token' => $jstoken,//游客
            'proxy' => $proxy,
            'params' => $params
        ];
        // print_r(json_encode($data));die;
        $url = config('my.main_link') . 'api/v1/ttapi/profile_other?impl=0';
        $arr = $this->doHttpPosts($url, json_encode($data), $access);
        print_r($arr);
        die;
        $respone_arr = json_decode($arr, true);

        if ($respone_arr['response_status'] == 0) {
            $tt_respone_json = $this->getResToTT($respone_arr, $proxy);
            // $tt_respone_arr = json_decode($tt_respone_json,true);

            var_dump($tt_respone_json);
            die;
            // print_r($tt_respone_arr);die;
            if (!isset($tt_respone_arr) || !is_array($tt_respone_arr)) throw new ValidateException('tt接口异常');
            if ($tt_respone_arr['status_code'] == 0) {
                print_r($tt_respone_arr);
                die;
            } else {
                throw new ValidateException($tt_respone_arr['status_msg']);
            }
        } else {
            echo '请求tt失败';
        }
    }

    function UpMemberTime()
    {
        $where['status'] = 1;
        $arr = MemberModel::where($where)->whereDay('updata_time', 'yesterday')->select()->toArray();
        foreach ($arr as &$row) {
            db('member')->where('member_id', $row['member_id'])->update(['ifup' => 1]);
        }
    }



    /**
     * @api {post} /Member/MemberSaveNew 03、查询最新批量修改记录  head 传token
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  批量修改
     */

    function MemberSaveNew()
    {
        $api_user_id['api_user_id'] = $this->request->uid;
        $api_user_id['task_type'] = "UpdateUserData";
        // var_dump($api_user_id);die;
        $tasklist = db('tasklist')->where($api_user_id)->order('tasklist_id desc')->find();
        // var_dump($tasklist);die;
        return $this->ajaxReturn($this->successCode, '操作成功', $tasklist);
    }

    /**
     * @api {post} /Member/BatchUpdateUserDatas 03、批量修改
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  批量修改
     * @apiParam (输入参数：) {array}            uid_list 要修改的uid列表
     * @apiParam (输入参数：) {int}              old_typecontrol_id  老的分类id
     * @apiParam (输入参数：) {int}              old_grouping_id  老的分组id   要么修改之前的分组id和分类传，要么uid_list传，二选一
     * @apiParam (输入参数：) {int}              typecontrol_id  要修改的分类id
     * @apiParam (输入参数：) {int}              grouping_id  要修改的分组id
     * @apiParam (输入参数：) {int}              nickname  昵称 不修改为空，要修改为1
     * @apiParam (输入参数：) {int}              avatar_thumb  头像 不修改为空，要修改为1
     * @apiParam (输入参数：) {int}              signature  签名 不修改为空，要修改为1
     * @apiParam (输入参数：) {object}
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */

    function BatchUpdateUserDatas()
    {
        $params = $this->request->post();
        // var_dump($params);die;
        $task_type = "BatchUpdateUserData";
        $uid_list = $params['uid_list'];//要修改的uid
        $old_typecontrol_id = $params['old_typecontrol_id'];//要修改的老分类
        $type_list = $params['type_list'];
        if (!count($type_list)) {
            throw new ValidateException('要修改项不能低于一种');
        }
        if ((!array_key_exists("nickname", $type_list) || !$type_list['nickname'])
            && (!array_key_exists("avatar_thumb", $type_list) || !$type_list['avatar_thumb'])
            && (!array_key_exists("signature", $type_list) || !$type_list['signature'])
            && (!array_key_exists("typecontrol_id", $type_list) || !$type_list['typecontrol_id'])) {
            throw new ValidateException(['不明确的修改类型', ['type_list' => ['nickname', 'avatar_thumb', 'signature', 'typecontrol_id']]]);
        }
        $type_title = implode(',',$type_list);
        // var_dump($type_title);die;
        $typecontrol_id = $type_list['typecontrol_id']; //需要修改的新分类
        $nickname = $type_list['nickname'];
        $avatar_thumb = $type_list['avatar_thumb'];
        $signature = $type_list['signature'];
        if (empty($uid_list) && (empty($old_typecontrol_id))) {
            throw new ValidateException('uidlist和分类二选一必传');
        }
        if ($uid_list && (empty($old_typecontrol_id))) {
            $idx = implode(",", $uid_list);
            $where[] = ['uid', 'in', $idx];
        }
        if ($old_typecontrol_id) {
            $where['typecontrol_id'] = $old_typecontrol_id;
        }
        $members = db('member')->where($where)->field('uid')->select()->toArray();
        // var_dump($members);die;
        if (empty($members)) {
            throw new ValidateException('没有账户');
        }
        foreach ($members as $member) {
            if ($typecontrol_id) {
                db('member')->where('uid', $member['uid'])->update(['typecontrol_id' => $typecontrol_id]);
            }

        }
        echo json_encode(['status' => 200, 'msg' => "操作成功"]);
        flushRequest();
        $members = db('member')->where($where)->field('uid,token,typecontrol_id')->select()->toArray();
        if ((array_key_exists("nickname", $type_list) && $type_list['nickname'])
            || (array_key_exists("avatar_thumb", $type_list) && $type_list['avatar_thumb'])
            || (array_key_exists("signature", $type_list) && $type_list['signature'])) {
            unset($type_list['typecontrol_id']);
            foreach ($type_list as $type => $v) {
                if (!$v) unset($type_list[$type]);
            }
            $redis_key = get_task_key($task_type);
            $addtask['task_name'] = '批量修改账户';
            $addtask['task_type'] = $task_type;
            $addtask['task_num'] = count($members) * count($type_list);
            $addtask['redis_key'] = $redis_key;
            $addtask['create_time'] = time();
            $addtask['api_user_id'] = $this->request->uid;
            $addtask['status'] = 1;
            $addtask['member_num'] = count($members);
            $addtask['typecontrol_id'] = $typecontrol_id;
            $addtask['update_type'] = $type_title;
            $usertask = db('tasklist')->insertGetId($addtask);

            $redis = connectRedis();
            $details = [];
            foreach ($members as &$member) {
                //往中间表中添加数据
                $uid_task['uid'] = $member['uid'];
                $uid_task['tasklist_id'] = $usertask;
                $uid_task['num'] = count($type_list);
                $task_uid_id = db('task_uid')->insert($uid_task);
                foreach ($type_list as $type => $v) {
                    $taskdata = [];
                    if ('nickname' == $type && $v) {
                        $taskdata['type'] = "nickname";
                        $taskdata['nickname'] = $this->suijisucai(1, $old_typecontrol_id ?: $member['typecontrol_id']);
                    } elseif ('signature' == $type && $v) {
                        $taskdata['type'] = "signature";
                        $taskdata['signature'] = $this->suijisucai(2, $old_typecontrol_id ?: $member['typecontrol_id']);
                    } elseif ('avatar_thumb' == $type && $v) {
                        $taskdata['type'] = "avatar_thumb";
                        $taskdata['avatar_thumb'] = $this->suijisucai(3, $old_typecontrol_id ?: $member['typecontrol_id']);
                    } else {
                        continue;
                    }

                    $taskdata['uid'] = $member['uid'];
                    $taskdata['token'] = doToken($member['token']);
                    $start = microtime(true);
                    $taskdata['proxy'] = getHttpProxy($member['uid']);
                    $end = microtime(true);
                    $adddata['proxy_hs'] = $end - $start;
                    $adddata['parameter'] = json_encode($taskdata);
                    $adddata['create_time'] = time();
                    $adddata['task_type'] = $task_type;
                    $adddata['tasklist_id'] = $usertask;
                    $adddata['api_user_id'] = $this->request->uid;
                    $adddata['crux'] = $member['uid'];
                    $adddata['task_uid_id'] = $task_uid_id;
                    unset($adddata['tasklistdetail_id']);
                    db('tasklist')->where('tasklist_id',$usertask)->inc('creation_num')->update();
                    $arr = \app\api\model\TaskListDetail::add($adddata);
                    $adddata['tasklistdetail_id'] = $arr;
                    $adddata['parameter'] = $taskdata;
                    $details[] = $adddata;
                }
            }
            foreach ($details as $detail) {
                $redis->lPush($redis_key, json_encode($detail));
            }
        }
    }

    function BatchUpdateUserData()
    {
        $idx = $this->request->post('member_id');
        $typecontrol_id = $this->request->post('typecontrol_id');
        if (empty($idx) || empty($typecontrol_id)) {
            throw new ValidateException('参数错误');
        }
        $member_id = explode(',', $idx);
        $addtask['task_name'] = '批量修改账户';
        $addtask['task_type'] = 'BatchUpdateUserData';
        $addtask['task_num'] = count($member_id);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);

        $redis = connectRedis();

        foreach ($member_id as $k => $v) {
            $users = db('member')->where('member_id', $v)->field('token,uid')->find();
            $ifuser = db('tasklistdetail')->where(['crux' => $users['uid'], 'task_type' => 'BatchUpdateUserData', 'status' => 1])->find();
            if ($ifuser) {
                continue;
            }
            for ($i = 0; $i < 3; $i++) {
                $data = [];
                $user = db('member')->where('member_id', $v)->field('token,uid')->find();
                $data['proxy'] = getHttpProxy($user['uid']);
                $data['token'] = json_decode($user['token'], true);
                if ($i == 0) {
                    $data['type'] = "avatar_thumb";
                    $data['avatar_thumb'] = $this->suijisucai(3, $typecontrol_id);
                } elseif ($i == 1) {
                    $data['type'] = "nickname";
                    $data['nickname'] = $this->suijisucai(1, $typecontrol_id);
                } else {
                    $data['type'] = "signature";
                    $data['signature'] = $this->suijisucai(2, $typecontrol_id);
                }
                $adddata['parameter'] = $data;
                $adddata['create_time'] = time();
                $adddata['task_type'] = 'BatchUpdateUserData';
                $adddata['tasklist_id'] = $usertask;
                $adddata['crux'] = $user['uid'];
                unset($adddata['tasklistdetail_id']);

                $arr = db('tasklistdetail')->insertGetId($adddata);
                $adddata['tasklistdetail_id'] = $arr;
                $redis->lPush('task', json_encode($adddata));
            }
        }
        return $this->ajaxReturn($this->successCode, '更新成功' . $user['uid']);

    }

    function suijisucai($type, $typecontrol_id)
    {
        $where['typecontrol_id'] = $typecontrol_id;
        if ($type == 1) {
            $res = db('nickname')->where($where)->order('usage_count asc')->find();
            $data = $res['nickname'];
            db('nickname')->where('nickname_id', $res['nickname_id'])->inc('usage_count')->update();
        } elseif ($type == 2) {
            $res = db('autograph')->where($where)->order('usage_count asc')->find();
            $data = $res['autograph'];
            db('autograph')->where('autograph_id', $res['autograph_id'])->inc('usage_count')->update();
        } elseif ($type == 3) {
            $res = db('headimage')->where($where)->order('usage_count asc')->find();
            $data = config('my.host_url') . $res['image'];
            db('headimage')->where('headimage_id', $res['headimage_id'])->inc('usage_count')->update();
        }
        return $data;
    }
    
    function getimage()
    {
        $where['ifpic'] = 1;
        // $where['status'] = 1;
        $head_img = MemberModel::where($where)->field('avatar_thumb,nickname,member_id,uid')->limit(30)->select()->toArray();
        if ($head_img) {
            foreach ($head_img as $k => $v) {
                $data = [];
                $avatar = $v['avatar_thumb'];
                $splFileInfo = new SplFileInfo($avatar);
                $avatar_hash = hash_file("md5", $splFileInfo->getPathname());
                if ($avatar_hash != '6786ffc93d6a02f2b30a98ee94132937') {
                    $path = app()->getRootPath() . "public/uploads/xiazai/hed_pic";
                    $savepath = $path . "/" . $v['uid'] . '.png';
                    $imageurl = config('my.host_url') . "/uploads/xiazai/hed_pic/{$v['uid']}.png";
                    $cand = '/www/wwwroot/main --url="' . $v['avatar_thumb'] . '" --spath=' . $savepath;
                    system($cand);
                    $data['ifpic'] = 0;
                    $data['head_image'] = $imageurl;
                    $data['no_avatar'] = 0;
                } else {
                    $data['ifpic'] = 0;
                    $data['head_image'] = config('my.host_url') . "/default/6826352723044107269.png";
                    $data['no_avatar'] = 1;
                }

                // var_dump($data);die;
                MemberModel::where('member_id',$v['member_id'])->update($data);
            }

        } else {
            echo '没有要下载的头像或者昵称';
        }

    }


    function country()
    {
        $index = array(
            'AA' => '阿鲁巴',
            'AD' => '安道尔',
            'AE' => '阿联酋',
            'AF' => '阿富汗',
            'AG' => '安提瓜和巴布达',
            'AL' => '阿尔巴尼亚',
            'AM' => '亚美尼亚',
            'AN' => '荷属安德列斯',
            'AO' => '安哥拉',
            'AQ' => '南极洲',
            'AR' => '阿根廷',
            'AS' => '东萨摩亚',
            'AT' => '奥地利',
            'AU' => '澳大利亚',
            'AZ' => '阿塞拜疆',
            'Av' => '安圭拉岛',
            'BA' => '波黑',
            'BB' => '巴巴多斯',
            'BD' => '孟加拉',
            'BE' => '比利时',
            'BF' => '巴哈马',
            'BF' => '布基纳法索',
            'BG' => '保加利亚',
            'BH' => '巴林',
            'BI' => '布隆迪',
            'BJ' => '贝宁',
            'BM' => '百慕大',
            'BN' => '文莱布鲁萨兰',
            'BO' => '玻利维亚',
            'BR' => '巴西',
            'BS' => '巴哈马',
            'BT' => '不丹',
            'BV' => '布韦岛',
            'BW' => '博茨瓦纳',
            'BY' => '白俄罗斯',
            'BZ' => '伯里兹',
            'CA' => '加拿大',
            'CB' => '柬埔寨',
            'CC' => '可可斯群岛',
            'CD' => '刚果',
            'CF' => '中非',
            'CG' => '刚果',
            'CH' => '瑞士',
            'CI' => '象牙海岸',
            'CK' => '库克群岛',
            'CL' => '智利',
            'CM' => '喀麦隆',
            'CN' => '中国',
            'CO' => '哥伦比亚',
            'CR' => '哥斯达黎加',
            'CS' => '捷克斯洛伐克',
            'CU' => '古巴',
            'CV' => '佛得角',
            'CX' => '圣诞岛',
            'CY' => '塞普路斯',
            'CZ' => '捷克',
            'DE' => '德国',
            'DJ' => '吉布提',
            'DK' => '丹麦',
            'DM' => '多米尼加共和国',
            'DO' => '多米尼加联邦',
            'DZ' => '阿尔及利亚',
            'EC' => '厄瓜多尔',
            'EE' => '爱沙尼亚',
            'EG' => '埃及',
            'EH' => '西撒哈拉',
            'ER' => '厄立特里亚',
            'ES' => '西班牙',
            'ET' => '埃塞俄比亚',
            'FI' => '芬兰',
            'FJ' => '斐济',
            'FK' => '福兰克群岛',
            'FM' => '米克罗尼西亚',
            'FO' => '法罗群岛',
            'FR' => '法国',
            'FX' => '法国-主教区',
            'GA' => '加蓬',
            'GB' => '英国',
            'GD' => '格林纳达',
            'GE' => '格鲁吉亚',
            'GF' => '法属圭亚那',
            'GH' => '加纳',
            'GI' => '直布罗陀',
            'GL' => '格陵兰岛',
            'GM' => '冈比亚',
            'GN' => '几内亚',
            'GP' => '法属德洛普群岛',
            'GQ' => '赤道几内亚',
            'GR' => '希腊',
            'GT' => '危地马拉',
            'GU' => '关岛',
            'GW' => '几内亚比绍',
            'GY' => '圭亚那',
            'HK' => '中国香港特区',
            'HM' => '赫德和麦克唐纳群岛',
            'HN' => '洪都拉斯',
            'HR' => '克罗地亚',
            'HT' => '海地',
            'HU' => '匈牙利',
            'ID' => '印度尼西亚',
            'IE' => '爱尔兰',
            'IL' => '以色列',
            'IN' => '印度',
            'IO' => '英属印度洋领地',
            'IQ' => '伊拉克',
            'IR' => '伊朗',
            'IS' => '冰岛',
            'IT' => '意大利',
            'JM' => '牙买加',
            'JO' => '约旦',
            'JP' => '日本',
            'KE' => '肯尼亚',
            'KG' => '吉尔吉斯斯坦',
            'KH' => '柬埔寨',
            'KI' => '基里巴斯',
            'KM' => '科摩罗',
            'KN' => '圣基茨和尼维斯',
            'KP' => '韩国',
            'KR' => '朝鲜',
            'KW' => '科威特',
            'KY' => '开曼群岛',
            'KZ' => '哈萨克斯坦',
            'LA' => '老挝',
            'LB' => '黎巴嫩',
            'LC' => '圣卢西亚',
            'LI' => '列支顿士登',
            'LK' => '斯里兰卡',
            'LR' => '利比里亚',
            'LS' => '莱索托',
            'LT' => '立陶宛',
            'LU' => '卢森堡',
            'LV' => '拉托维亚',
            'LY' => '利比亚',
            'MA' => '摩洛哥',
            'MC' => '摩纳哥',
            'MD' => '摩尔多瓦',
            'MG' => '马达加斯加',
            'MH' => '马绍尔群岛',
            'MK' => '马其顿',
            'ML' => '马里',
            'MM' => '缅甸',
            'MN' => '蒙古',
            'MO' => '中国澳门特区',
            'MP' => '北马里亚纳群岛',
            'MQ' => '法属马提尼克群岛',
            'MR' => '毛里塔尼亚',
            'MS' => '蒙塞拉特岛',
            'MT' => '马耳他',
            'MU' => '毛里求斯',
            'MV' => '马尔代夫',
            'MW' => '马拉维',
            'MX' => '墨西哥',
            'MY' => '马来西亚',
            'MZ' => '莫桑比克',
            'NA' => '纳米比亚',
            'NC' => '新卡里多尼亚',
            'NE' => '尼日尔',
            'NF' => '诺福克岛',
            'NG' => '尼日利亚',
            'NI' => '尼加拉瓜',
            'NL' => '荷兰',
            'NO' => '挪威',
            'NP' => '尼泊尔',
            'NR' => '瑙鲁',
            'NT' => '中立区(沙特-伊拉克间)',
            'NU' => '纽爱',
            'NZ' => '新西兰',
            'OM' => '阿曼',
            'PA' => '巴拿马',
            'PE' => '秘鲁',
            'PF' => '法属玻里尼西亚',
            'PG' => '巴布亚新几内亚',
            'PH' => '菲律宾',
            'PK' => '巴基斯坦',
            'PL' => '波兰',
            'PM' => '圣皮艾尔和密克隆群岛',
            'PN' => '皮特克恩岛',
            'PR' => '波多黎各',
            'PT' => '葡萄牙',
            'PW' => '帕劳',
            'PY' => '巴拉圭',
            'QA' => '卡塔尔',
            'RE' => '法属尼留旺岛',
            'RO' => '罗马尼亚',
            'RU' => '俄罗斯',
            'RW' => '卢旺达',
            'SA' => '沙特阿拉伯',
            'SC' => '塞舌尔',
            'SD' => '苏丹',
            'SE' => '瑞典',
            'SG' => '新加坡',
            'SH' => '圣赫勒拿',
            'SI' => '斯罗文尼亚',
            'SJ' => '斯瓦尔巴特和扬马延岛',
            'SK' => '斯洛伐克',
            'SL' => '塞拉利昂',
            'SM' => '圣马力诺',
            'SN' => '塞内加尔',
            'SO' => '索马里',
            'SR' => '苏里南',
            'ST' => '圣多美和普林西比',
            'SU' => '前苏联',
            'SV' => '萨尔瓦多',
            'SY' => '叙利亚',
            'SZ' => '斯威士兰',
            'Sb' => '所罗门群岛',
            'TC' => '特克斯和凯科斯群岛',
            'TD' => '乍得',
            'TF' => '法国南部领地',
            'TG' => '多哥',
            'TH' => '泰国',
            'TJ' => '塔吉克斯坦',
            'TK' => '托克劳群岛',
            'TM' => '土库曼斯坦',
            'TN' => '突尼斯',
            'TO' => '汤加',
            'TP' => '东帝汶',
            'TR' => '土尔其',
            'TT' => '特立尼达和多巴哥',
            'TV' => '图瓦卢',
            'TW' => '中国台湾省',
            'TZ' => '坦桑尼亚',
            'UA' => '乌克兰',
            'UG' => '乌干达',
            'UK' => '英国',
            'UM' => '美国海外领地',
            'US' => '美国',
            'UY' => '乌拉圭',
            'UZ' => '乌兹别克斯坦',
            'VA' => '梵蒂岗',
            'VC' => '圣文森特和格陵纳丁斯',
            'VE' => '委内瑞拉',
            'VG' => '英属维京群岛',
            'VI' => '美属维京群岛',
            'VN' => '越南',
            'VU' => '瓦努阿鲁',
            'WF' => '瓦里斯和福图纳群岛',
            'WS' => '西萨摩亚',
            'YE' => '也门',
            'YT' => '马约特岛',
            'YU' => '南斯拉夫',
            'ZA' => '南非',
            'ZM' => '赞比亚',
            'ZR' => '扎伊尔',
            'ZW' => '津巴布韦'
        );
        // var_dump($index);die;
        // $data['data']=$index;
        return $this->ajaxReturn($this->successCode, '获取成功', array_values($index));
    }


}

