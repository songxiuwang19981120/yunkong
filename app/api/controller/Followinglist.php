<?php
/*
 module:		关注
 create_time:	2022-11-24 14:11:11
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Followinglist as FollowinglistModel;
use app\api\service\FollowinglistService;
use think\exception\ValidateException;
use SplFileInfo;


class Followinglist extends Common
{
    protected $noNeedLogin = ["Followinglist/getimage"];

    /**
     * @api {post} /Followinglist/index 01、首页数据列表
     * @apiGroup Followinglist
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [member_id] 关注着的用户id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function index()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');

        $where = [];
        $where['member_id'] = $this->request->post('member_id', '', 'serach_in');

        $field = '*';
        $orderby = 'followinglist_id desc';

        $res = FollowinglistService::indexList($this->apiFormatWhere($where,FollowinglistModel::class), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['create_time'] = date("Y-m-d H:i:s", $row['create_time']);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    ///Followinglist/getfollowinglist
    function getfollowinglist()
    {
        $member_id = $this->request->post('member_id', '', 'serach_in');
        if (empty($member_id)) {
            throw new ValidateException('参数错误');
        }
        $arr = db('member')->where('member_id', $member_id)->field('uid,sec_uid,member_id')->find();
        $sjtoken = db('user_token_list')->orderRaw('rand()')->limit(1)->select()->toArray();
        $token = $sjtoken[0]['token'];

        $url = 'http://47.245.30.4:9999/rest/VideoInfo/followingList';
        $data['user_id'] = $arr['uid'];
        $data['token'] = $token;
        $data['sec_user_id'] = $arr['sec_uid'];
        $data['max_time'] = 0;
        $data['has_more'] = 0;
        $userinfo = $this->doHttpPost($url, $data);
        print_r($userinfo);
        die;
        $info = json_decode($userinfo, true);
        if ($info['result'] == 0) {
            $aweme_list = $info['data']['followings']; //视频列表
            if (!empty($aweme_list)) {
                foreach ($aweme_list as $k => $v) {
                    $adddata['nickname'] = $v['nickname'];
                    $adddata['member_id'] = $member_id;
                    $adddata['create_time'] = $v['create_time'];
                    $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
                    $adddata['sec_uid'] = $v['sec_uid'];
                    $adddata['following_count'] = $v['following_count'];
                    $adddata['follower_count'] = $v['follower_count'];
                    $adddata['favoriting_count'] = $v['favoriting_count'];
                    $adddata['unique_id'] = $v['unique_id'];
                    $adddata['uid'] = $v['uid'];
                    $adddata['aweme_count'] = $v['aweme_count'];
                    $adddata['region'] = $this->transCountryCode($v['region']);
                    $where['member_id'] = $member_id;
                    $where['uid'] = $v['uid'];
                    $followinglistinfo = db('followinglist')->where($where)->find();
                    if ($followinglistinfo) {
                        $res = db('followinglist')->where($where)->update($adddata);
                    } else {
                        $res = db('followinglist')->insert($adddata);
                    }
                }
                if ($info['data']['has_more'] == 1) {
                    // $this->getfollowinglistfor($info['data']['has_more'],$info['data']['max_time'],$data['user_id'],$data['sec_user_id'],$member_id);
                }
                return $this->ajaxReturn($this->successCode, '操作成功');
            } else {
                throw new ValidateException('没有关注');
            }
        } else {
            throw new ValidateException($info['message']);
        }
    }

    //递归
    function getfollowinglistfor($has_more, $max_time, $user_id, $sec_user_id, $member_id)
    {
        set_time_limit(0);
        $token = db('tourist')->where('tourist_id', 2)->find();//游客的信息
        $url = 'http://47.245.30.4:9999/rest/VideoInfo/followingList';
        $data['user_id'] = $user_id;
        $data['token'] = $token['token'];
        $data['sec_user_id'] = $sec_user_id;
        $data['max_time'] = $max_time;
        $data['has_more'] = $has_more;
        $userinfo = $this->doHttpPost($url, $data);
        $info = json_decode($userinfo, true);
        if ($info['result'] == 0) {
            $aweme_list = $info['data']['followings']; //视频列表
            if (!empty($aweme_list)) {
                foreach ($aweme_list as $k => $v) {
                    $adddata['nickname'] = $v['nickname'];
                    $adddata['member_id'] = $member_id;
                    $adddata['create_time'] = $v['create_time'];
                    $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
                    $adddata['sec_uid'] = $v['sec_uid'];
                    $adddata['following_count'] = $v['following_count'];
                    $adddata['follower_count'] = $v['follower_count'];
                    $adddata['favoriting_count'] = $v['favoriting_count'];
                    $adddata['unique_id'] = $v['unique_id'];
                    $adddata['uid'] = $v['uid'];
                    $adddata['aweme_count'] = $v['aweme_count'];
                    $adddata['region'] = $this->transCountryCode($v['region']);
                    $where['member_id'] = $member_id;
                    $where['uid'] = $v['uid'];
                    $followinglistinfo = db('followinglist')->where($where)->find();
                    if ($followinglistinfo) {
                        $res = db('followinglist')->where($where)->update($adddata);
                    } else {
                        $res = db('followinglist')->insert($adddata);
                    }
                }
                if ($info['data']['has_more'] == 1) {
                    $this->getfollowinglistfor($info['data']['has_more'], $info['data']['max_time'], $data['user_id'], $data['sec_user_id']);
                }
                return $this->ajaxReturn($this->successCode, '操作成功');
            } else {
                throw new ValidateException('没有关注');
            }
        } else {
            throw new ValidateException($info['message']);
        }
    }

    ///Followinglist/getuserimg
    //下载关注的头像
    function getimage()
    {
        $where['ifpic'] = 1;
        // $where['member_id'] = 20;
        $head_img = db('followinglist')->where($where)->field('uid,followinglist_id,avatar_thumb,head_image')->limit(30)->select()->toArray();
        // var_dump($head_img);die;
        if ($head_img) {

            foreach ($head_img as $k => $v) {
                if(empty($v['head_image'])){
                $data = [];
                $avatar = $v['avatar_thumb'];
                $splFileInfo = new SplFileInfo($avatar);
                $avatar_hash = hash_file("md5", $splFileInfo->getPathname());
                if ($avatar_hash != '6786ffc93d6a02f2b30a98ee94132937') {
                    $path = app()->getRootPath() . "public/uploads/xiazai/followinglist";
                    $savepath = $path . "/" . $v['uid'] . '.png';
                    $imageurl = config('my.host_url') . "/uploads/xiazai/followinglist/{$v['uid']}.png";
                    $cand = '/www/wwwroot/main --url="' . $v['avatar_thumb'] . '" --spath=' . $savepath;
                    system($cand);
                    $data['ifpic'] = 0;
                    $data['head_image'] = $imageurl;
                    $data['has_avatar'] = 1;
                } else {
                    $data['ifpic'] = 0;
                    $data['head_image'] = config('my.host_url') . "/default/7167048905233662981.png";
                    $data['has_avatar'] = 0;
                }
                // var_dump($data);die;
                db('followinglist')->where('followinglist_id',$v['followinglist_id'])->update($data);
            }else{
                db('followinglist')->where('followinglist_id',$v['followinglist_id'])->update(['ifpic'=>0]);
            }
        }

        } else {
            echo '没有要下载的头像或者昵称';
        }

    }


}

