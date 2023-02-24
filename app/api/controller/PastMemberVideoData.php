<?php 
/*
 module:		往期账户视频数据
 create_time:	2023-02-13 12:45:04
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\service\PastMemberVideoDataService;
use app\api\model\PastMemberVideoData as PastMemberVideoDataModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class PastMemberVideoData extends Common {


	/**
	* @api {post} /PastMemberVideoData/index 01、首页数据列表
	* @apiGroup PastMemberVideoData
	* @apiVersion 1.0.0
	* @apiDescription  首页数据列表
	* @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
	* @apiParam (输入参数：) {int}     		[page] 当前页码
	* @apiParam (输入参数：) {string}		[uid] 账户 
	* @apiParam (输入参数：) {string}		[aweme_id] 视频id 
	* @apiParam (输入参数：) {string}		[collect_count] 收藏数 

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.data 返回数据
	* @apiParam (成功返回参数：) {string}     	array.data.list 返回数据列表
	* @apiParam (成功返回参数：) {string}     	array.data.count 返回数据总数
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":""}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"查询失败"}
	*/
	function index(){
		if(!$this->request->isPost()){
			throw new ValidateException('请求错误');
		}
		$limit  = $this->request->post('limit', 20, 'intval');
		$page   = $this->request->post('page', 1, 'intval');

		$where = [];
		$where['uid'] = $this->request->post('uid', '', 'serach_in');
		$where['aweme_id'] = $this->request->post('aweme_id', '', 'serach_in');
		$where['collect_count'] = $this->request->post('collect_count', '', 'serach_in');

		$field = '*';
		$orderby = 'pastmembervideodata_id desc';

        $model =  \app\api\model\PastMemberVideoData::class;
		$res = PastMemberVideoDataService::indexList($this->apiFormatWhere($where, $model),$field,$orderby,$limit,$page);
		return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
	}



}

