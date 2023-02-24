<?php 
/*
 module:		私信素材分类
 create_time:	2023-02-10 18:47:29
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\service\ClassnameService;
use app\api\model\Classname as ClassnameModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class Classname extends Common {


	/**
	* @api {post} /Classname/index 01、首页数据列表
	* @apiGroup Classname
	* @apiVersion 1.0.0
	* @apiDescription  首页数据列表
	* @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
	* @apiParam (输入参数：) {int}     		[page] 当前页码

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
        $where['type'] = $this->request->post('type');
		$field = '*';
		$orderby = 'classname_id desc';

        $model =  \app\api\model\Classname::class;
		$res = ClassnameService::indexList($this->apiFormatWhere($where, $model),$field,$orderby,$limit,$page);
		foreach ($res['list'] as &$row) {
		  //  for ($i = 0; $i <= 3; $i++) {
		  //      if($i == 0){
		  //          $type = '文本';
		  //      }elseif ($i == 1) {
		  //          $type = '短链接';
		  //      }elseif($i == 2){
		  //          $type = '好友名片';
		  //      }else{
		  //          $type = '作品转发';
		  //      }
		  //      $listdata[] =[
		  //          'Unused' =>db('privateletter')->where(['classname_id'=>$row['classname_id'],'type'=>$i])->where('usage_count',0)->count(),
		  //          'Used' => db('privateletter')->where(['classname_id'=>$row['classname_id'],'type'=>$i])->where('usage_count','>',0)->count(),
		  //          'type' => $type
		  //          ];
		  //  }
		    
	   //    // $listdata['Unused'] = $Unused;
	   //    // $listdata['Used'] = $Used;
	   //    // $listdata['type'] = $type;
		  //  $row['privateLetterlist'] =  $listdata;
		  if($row['type'] == 0){
	            $type = '文本';
	        }elseif ($row['type'] == 1) {
	            $type = '短链接';
	        }elseif($row['type'] == 2){
	            $type = '好友名片';
	        }else{
	            $type = '作品转发';
	        }
	        $row['type_name'] = $type;
	        $row['Unused'] =db('privateletter')->where(['classname_id'=>$row['classname_id'],'type'=>$row['type']])->where('usage_count',0)->count();
		    $row['Used'] = db('privateletter')->where(['classname_id'=>$row['classname_id'],'type'=>$row['type']])->where('usage_count','>',0)->count();
		}
		return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
	}

	/**
	* @api {post} /Classname/add 02、添加
	* @apiGroup Classname
	* @apiVersion 1.0.0
	* @apiDescription  添加
	* @apiParam (输入参数：) {string}			classname 名称 (必填) 

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码  201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"操作失败"}
	*/
	function add(){
		$postField = 'classname,type';
		$data = $this->request->only(explode(',',$postField),'post',null);
		$data['api_user_id'] = $this->request->uid;
		$res = ClassnameService::add($data);
		return $this->ajaxReturn($this->successCode,'操作成功',$res);
	}

	/**
	* @api {post} /Classname/update 03、修改
	* @apiGroup Classname
	* @apiVersion 1.0.0
	* @apiDescription  修改
	
	* @apiParam (输入参数：) {string}     		classname_id 主键ID (必填)
	* @apiParam (输入参数：) {string}			classname 名称 (必填) 

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码  201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","msg":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"操作失败"}
	*/
	function update(){
		$postField = 'classname_id,classname,type';
		$data = $this->request->only(explode(',',$postField),'post',null);
		$data['api_user_id'] = $this->request->uid;
		if(empty($data['classname_id'])){
			throw new ValidateException('参数错误');
		}
		$where['classname_id'] = $data['classname_id'];
		$res = ClassnameService::update($where,$data);
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {post} /Classname/delete 04、删除
	* @apiGroup Classname
	* @apiVersion 1.0.0
	* @apiDescription  删除
	* @apiParam (输入参数：) {string}     		classname_ids 主键id 注意后面跟了s 多数据删除

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","msg":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":"201","msg":"操作失败"}
	*/
	function delete(){
		$idx =  $this->request->post('classname_ids', '', 'serach_in');
		if(empty($idx)){
			throw new ValidateException('参数错误');
		}
		$data['classname_id'] = explode(',',$idx);
		try{
			ClassnameModel::destroy($data,true);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $this->ajaxReturn($this->successCode,'操作成功');
	}
	
	
	//删除所有数据
	function deletes(){
	    try{
			db('classname')->where('api_user_id',$this->request->uid)->delete();
			db('privateletter')->where('api_user_id',$this->request->uid)->delete();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $this->ajaxReturn($this->successCode,'操作成功');
	    
	}



}

