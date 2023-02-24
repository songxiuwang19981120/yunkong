<?php 
/*
 module:		往期账户数据
 create_time:	2023-02-13 13:34:42
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\service\PastMemberDataService;
use app\api\model\PastMemberData as PastMemberDataModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class PastMemberData extends Common {


	/**
	* @api {post} /PastMemberData/index 01、首页数据列表
	* @apiGroup PastMemberData
	* @apiVersion 1.0.0
	* @apiDescription  首页数据列表
	* @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
	* @apiParam (输入参数：) {int}     		[page] 当前页码
	* @apiParam (输入参数：) {string}		[uid] 账户 
	* @apiParam (输入参数：) {string}		[typecontrol_id] 账户分类 

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
		$where['typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
		$create_time_start = $this->request->post('create_time_start', '', 'serach_in');
        $create_time_end = $this->request->post('create_time_end', '', 'serach_in');

        $where['updata_time'] = ['between', [strtotime($create_time_start), strtotime($create_time_end)]];
		
        $where['api_user_id'] = $this->request->uid;
		$field = '*';
		$orderby = 'pastmemberdata desc';

        $model =  \app\api\model\PastMemberData::class;
		$res = PastMemberDataService::indexList($this->apiFormatWhere($where, $model),$field,$orderby,$limit,$page);
		return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
	}



}

