<?php
/*
 module:		来访列表
 create_time:	2022-12-12 12:50:28
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\service\VisitorlistService;
use think\exception\ValidateException;
use SplFileInfo;


class Visitorlist extends Common
{
    protected $noNeedLogin = ["Visitorlist/getimage"];


    /**
     * @api {post} /Visitorlist/index 01、首页数据列表
     * @apiGroup Visitorlist
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [nickname] nickname
     * @apiParam (输入参数：) {string}        [country] 国家
     * @apiParam (输入参数：) {int}            [ifpic] ifpic 未下载|1|success,已下载|0|danger
     * @apiParam (输入参数：) {string}        [member_id] 用户
     * @apiParam (输入参数：) {string}        [uid] uid
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
        $where['nickname'] = $this->request->post('nickname', '', 'serach_in');
        $where['country'] = $this->request->post('country', '', 'serach_in');
// 		$where['ifpic'] = $this->request->post('ifpic', '', 'serach_in');
        $where['member_id'] = $this->request->post('member_id', '', 'serach_in');
        $where['uid'] = $this->request->post('uid', '', 'serach_in');

        $field = '*';
        $orderby = 'visitorlist_id desc';

        $res = VisitorlistService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }
    
    function getimage()
    {
        $where['ifpic'] = 1;
        // $where['status'] = 1;
        $head_img = db('visitorlist')->where($where)->field('avatar_thumb,nickname,visitorlist_id,uid')->limit(30)->select()->toArray();
        if ($head_img) {
            foreach ($head_img as $k => $v) {
                $data = [];
                $avatar = $v['avatar_thumb'];
                $splFileInfo = new SplFileInfo($avatar);
                $avatar_hash = hash_file("md5", $splFileInfo->getPathname());
                if ($avatar_hash != '6786ffc93d6a02f2b30a98ee94132937') {
                    $path = app()->getRootPath() . "public/uploads/xiazai/visitorlist";
                    $savepath = $path . "/" . $v['uid'] . '.png';
                    $imageurl = config('my.host_url') . "/uploads/xiazai/visitorlist/{$v['uid']}.png";
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
                db('visitorlist')->where('visitorlist_id',$v['visitorlist_id'])->update($data);
            }

        } else {
            echo '没有要下载的头像或者昵称';
        }

    }


}

