<?php
/*
 module:		评论
 create_time:	2022-11-23 19:43:17
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Commentlist as CommentlistModel;
use app\api\service\CommentlistService;
use think\exception\ValidateException;
use think\facade\Validate;
use SplFileInfo;

class Commentlist extends Common
{
    protected $noNeedLogin = ["Commentlist/getimage"];

    /**
     * @api {post} /Commentlist/index 01、首页数据列表
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [reply_id] reply_id
     * @apiParam (输入参数：) {string}        [membervideo_id] membervideo_id
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
        $where['reply_id'] = $this->request->post('reply_id', '', 'serach_in');
        $where['membervideo_id'] = $this->request->post('membervideo_id', '', 'serach_in');
        $where['aweme_id'] = $this->request->post('aweme_id', '', 'serach_in');
        // $this->Commentview($where['membervideo_id']);
        $field = '*';
        $orderby = 'digg_count desc';
        $res = CommentlistService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {

            $row['create_time'] = date("Y-m-d H:i:s", $row['create_time']);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Commentlist/add 02、添加
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            cid 评论ID
     * @apiParam (输入参数：) {string}            comment_language 评论语言
     * @apiParam (输入参数：) {string}            text 评论内容
     * @apiParam (输入参数：) {string}            create_time 评论时间
     * @apiParam (输入参数：) {string}            digg_count 评论点赞数
     * @apiParam (输入参数：) {string}            aweme_id 作品ID
     * @apiParam (输入参数：) {string}            reply_id reply_id
     * @apiParam (输入参数：) {string}            reply_comment_total 评论回复总数
     * @apiParam (输入参数：) {string}            uid 评论用户ID
     * @apiParam (输入参数：) {string}            sec_uid 评论用户sec_uid
     * @apiParam (输入参数：) {string}            avatar_medium 评论用户头像
     * @apiParam (输入参数：) {string}            nickname 评论人昵称
     * @apiParam (输入参数：) {string}            unique_id 评论人unique_id
     * @apiParam (输入参数：) {string}            aweme_count 评论人作品数量
     * @apiParam (输入参数：) {string}            following_count 评论人关注数量
     * @apiParam (输入参数：) {string}            follower_count 评论人粉丝数量
     * @apiParam (输入参数：) {string}            total_favorited 评论人点赞数量
     * @apiParam (输入参数：) {string}            signature 评论人签名
     * @apiParam (输入参数：) {string}            account_region 评论人国家
     * @apiParam (输入参数：) {string}            membervideo_id membervideo_id
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
        $postField = 'cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = CommentlistService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Commentlist/update 03、修改
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            comment_list_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            cid 评论ID
     * @apiParam (输入参数：) {string}            comment_language 评论语言
     * @apiParam (输入参数：) {string}            text 评论内容
     * @apiParam (输入参数：) {string}            create_time 评论时间
     * @apiParam (输入参数：) {string}            digg_count 评论点赞数
     * @apiParam (输入参数：) {string}            aweme_id 作品ID
     * @apiParam (输入参数：) {string}            reply_id reply_id
     * @apiParam (输入参数：) {string}            reply_comment_total 评论回复总数
     * @apiParam (输入参数：) {string}            uid 评论用户ID
     * @apiParam (输入参数：) {string}            sec_uid 评论用户sec_uid
     * @apiParam (输入参数：) {string}            avatar_medium 评论用户头像
     * @apiParam (输入参数：) {string}            nickname 评论人昵称
     * @apiParam (输入参数：) {string}            unique_id 评论人unique_id
     * @apiParam (输入参数：) {string}            aweme_count 评论人作品数量
     * @apiParam (输入参数：) {string}            following_count 评论人关注数量
     * @apiParam (输入参数：) {string}            follower_count 评论人粉丝数量
     * @apiParam (输入参数：) {string}            total_favorited 评论人点赞数量
     * @apiParam (输入参数：) {string}            signature 评论人签名
     * @apiParam (输入参数：) {string}            account_region 评论人国家
     * @apiParam (输入参数：) {string}            membervideo_id membervideo_id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function update()
    {
        $postField = 'comment_list_id,cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['comment_list_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['comment_list_id'] = $data['comment_list_id'];
        $res = CommentlistService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Commentlist/delete 04、删除
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            comment_list_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('comment_list_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['comment_list_id'] = explode(',', $idx);
        try {
            CommentlistModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


    /**
     * @api {post} /Commentlist/view 05、查看详情
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            comment_list_id 主键ID
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    function view()
    {
        $data['comment_list_id'] = $this->request->post('comment_list_id', '', 'serach_in');
        $field = 'comment_list_id,cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
        $res = checkData(CommentlistModel::field($field)->where($data)->find());
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }
    
    function comment_digg_data_sources(){
        $data = db('comment_list')->alias('a')->join('tasklist b','a.tasklist_id = b.tasklist_id')->where('b.task_type','CollectionUser')->field('b.tasklist_id,b.task_name')->order('a.tasklist_id desc')->group('a.tasklist_id')->select()->toArray();
        
        return $this->ajaxReturn($this->successCode, '返回成功', $data);
    }


    //下载头像
    function getimage()
    {
        $where['ifpic'] = 1;
        // $where['member_id'] = 20;
        $head_img = CommentlistModel::where($where)->field('uid,nickname,comment_list_id,avatar_medium')->limit(30)->select()->toArray();
        // var_dump($head_img);die;
        if ($head_img) {

            foreach ($head_img as $k => $v) {
                $data = [];
                $avatar = $v['avatar_medium'];
                $splFileInfo = new SplFileInfo($avatar);
                $avatar_hash = hash_file("md5", $splFileInfo->getPathname());
                if ($avatar_hash != '6786ffc93d6a02f2b30a98ee94132937') {
                    $path = app()->getRootPath() . "public/uploads/xiazai/" . date("YmdH");
                    $savepath = $path . "/" . $v['uid'] . '.png';
                    $imageurl = config('my.host_url') . "/uploads/xiazai/" . date("YmdH") . "/{$v['uid']}.png";
                    $cand = '/www/wwwroot/main --url="' . $v['avatar_medium'] . '" --spath=' . $savepath;
                    system($cand);
                    $data['ifpic'] = 0;
                    $data['head_image'] = $imageurl;
                    $data['has_avatar'] = 1;
                } else {
                    $data['ifpic'] = 0;
                    $data['head_image'] = config('my.host_url') . "/default/7167048905233662981.png";
                    $data['has_avatar'] = 0;
                }
                if (substr($v['nickname'],0,4) == 'user') {
                    $data['has_nickname'] = 0;
                }
                // var_dump($data);die;
                CommentlistModel::where('comment_list_id',$v['comment_list_id'])->update($data);
            }

        } else {
            echo '没有要下载的头像或者昵称';
        }

    }


}

