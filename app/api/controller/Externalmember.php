<?php
/*
 module:		用户采集
 create_time:	2022-12-13 13:15:50
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Externalmember as ExternalmemberModel;
use app\api\service\ExternalmemberService;
use SplFileInfo;
use think\exception\ValidateException;
use think\facade\Validate;

class Externalmember extends Common
{
    protected $noNeedLogin = ["externalmember/getimage"];
    // protected $noNeedLogin = ["externalmember/getimage"];
    /**
     * @api {post} /Externalmember/index 01、首页数据列表
     * @apiGroup Externalmember
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [uid] uid
     * @apiParam (输入参数：) {string}        [nickname] 昵称
     * @apiParam (输入参数：) {string}        [status] 状态1=正常0=封禁2=登出2096私密账号3002290=个人资料查看历史记录不可用
     * @apiParam (输入参数：) {string}        [sources] 数据来源
     * @apiParam (输入参数：) {string}        [label] 数据标签
     * @apiParam (输入参数：) {string}        [if_collection] 1=未用，0=以用
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
        $where['uid'] = $this->request->post('uid', '', 'serach_in');
        $where['nickname'] = ['like', $this->request->post('nickname', '', 'serach_in')];
        $where['status'] = $this->request->post('status', '', 'serach_in');
        $where['sources'] = $this->request->post('sources', '', 'serach_in');
        $where['label'] = $this->request->post('label', '', 'serach_in');
        $where['if_collection'] = $this->request->post('if_collection', '', 'serach_in');
        $where['tasklist_id'] = $this->request->post('tasklist_id', '', 'serach_in');
        $country = $this->request->post('country', '', 'serach_in');
        if($country){
            $country_list = implode(",", $country);
            $where[] = ['country', 'in', $country_list];
        }
        
        
        $field = '*';
        $orderby = 'external_member_id desc';

        $res = ExternalmemberService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['addtime'] = date("Y-m-d H:i:s", $row['addtime']);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }


    //下载头像
    function getimage()
    {
        $where['ifpic'] = 1;
        // $where['member_id'] = 20;
        $head_img = ExternalmemberModel::where($where)->field('avatar_thumb,nickname,external_member_id,uid')->limit(30)->select()->toArray();
        if ($head_img) {
            foreach ($head_img as $k => $v) {
                $data = [];
                $avatar = $v['avatar_thumb'];
                $splFileInfo = new SplFileInfo($avatar);
                $avatar_hash = hash_file("md5", $splFileInfo->getPathname());
                if ($avatar_hash != '6786ffc93d6a02f2b30a98ee94132937') {
                    $path = app()->getRootPath() . "public/uploads/xiazai/" . date("YmdH");
                    $savepath = $path . "/" . $v['uid'] . '.png';
                    $imageurl = config('my.host_url') . "/uploads/xiazai/" . date("YmdH") . "/{$v['uid']}.png";
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
                if (substr($v['nickname'],0,4) == 'user') {
                    $data['has_nickname'] = 0;
                }
                // var_dump($data);die;
                ExternalmemberModel::where('external_member_id',$v['external_member_id'])->update($data);
            }

        } else {
            echo '没有要下载的头像或者昵称';
        }

    }
    
    //查询可用的关注数
    
    function iffollw(){
        $where = [];
    $country = $this->request->post('country');
    if($country){
        $where['country'] = $country;
    }
        $ygz = ExternalmemberModel::where($where)->where('is_follow',0)->count();
        $wgz = ExternalmemberModel::where($where)->where('is_follow',1)->count();
        $data = [];
        $data['ygz'] = $ygz;
        $data['wgz'] = $wgz;
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($data));
    }
    
    //查询可以点赞的评论数
    function if_comment_digg(){
        $where = [];
        $country = $this->request->post('country');
        if($country){
            $where['account_region '] = $country;
        }
        $ydz = db('comment_list')->where($where)->where('tasklist_id ','<>',0)->where('is_digg',0)->count();
        $wdz = db('comment_list')->where($where)->where('tasklist_id ','<>',0)->where('is_digg',1)->count();
        $data = [];
        $data['ygz'] = $ydz;
        $data['wgz'] = $wdz;
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($data));
    }
    
     function screen_follow()
    {
        $params = $this->request->get();
        if(empty($params)){
            throw new ValidateException("参数错误");
        }
        $external_member_num = ExternalmemberModel::where(['secret' => 0])
            ->where(function ($query) use ($params) {
                 $query->where("sources",'in', $params['sources']);
                 if($params['is_follow '] == 1){
                      $query->where("is_follow",1);
                 }else if($params['is_follow '] == 0){
                     $query->where("is_follow",0);
                 }
                 if($params['country_list']){
                     $query->where("country",'in',$params['country_list']);
                 }
            })
            // ->whereIn('country', $params['country_list'])
            ->field('external_member_id')->count();


        return $this->ajaxReturn($this->successCode, '返回成功', $external_member_num);
    }
    
    function ExternalmemberCountry(){
        $arr = ExternalmemberModel::where(['secret'=>0])->whereNotNull('country')->field('count(*) as num,country')->order('num desc')->limit(100)
            ->group('country')->select();
    return $this->ajaxReturn($this->successCode, '返回成功', $arr);
    }
    
    // function task_CollectionUser_num(){
    //     $tasklistid = $this->request->post('tasklist_id');
    //     if(empty($tasklistid)){
    //         throw new ValidateException("参数错误");
    //     }
    //     $where = [];
    //     $where['tasklist_id'] = $tasklistid;
    //     $sources = $this->request->post('sources');
    //     if($sources){
    //         $where['sources'] = $sources;
    //     }
    //     $country_list = $this->request->post('country_list');
    //     if($country_list){
    //         $country = implode(",", $country_list);
    //         // var_dump($country);die;
    //         // $where[] = ['country', 'in', $country];
    //     }
    //     // var_dump($where);die;
        
    //     $data = ExternalmemberModel::where($where)->whereIn('country',$country)->count();
        
    //     return $this->ajaxReturn($this->successCode, '返回成功', $data);
    // }
    


}

