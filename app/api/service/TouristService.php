<?php 
/*
 module:		游客
 create_time:	2023-02-18 10:30:21
 author:		大怪兽
 contact:		
*/

namespace app\api\service;
use app\api\model\Tourist;
use think\facade\Log;
use think\exception\ValidateException;
use base\CommonService;

class TouristService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = Tourist::where($where)->field($field)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['list'=>$res['data'],'count'=>$res['total']];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$res = Tourist::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res->tourist_id;
	}




}

