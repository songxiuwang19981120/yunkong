<?php 
/*
 module:		doem
 create_time:	2023-01-02 22:16:06
 author:		大怪兽
 contact:		
*/

namespace app\api\service;
use app\api\model\Demo;
use think\facade\Log;
use think\exception\ValidateException;
use base\CommonService;

class DemoService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = Demo::where($where)->field($field)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
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
			$res = Demo::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res->doem_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($where,$data){
		try{
			$res = Demo::where($where)->update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}




}

