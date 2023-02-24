<?php 
/*
 module:		私信素材分类
 create_time:	2023-02-10 18:47:29
 author:		大怪兽
 contact:		
*/

namespace app\api\service;
use app\api\model\Classname;
use think\facade\Log;
use think\exception\ValidateException;
use base\CommonService;

class ClassnameService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = Classname::where($where)->field($field)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
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
			validate(\app\api\validate\Classname::class)->scene('add')->check($data);
			$res = Classname::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res->classname_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($where,$data){
		try{
			validate(\app\api\validate\Classname::class)->scene('update')->check($data);
			$res = Classname::where($where)->update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}




}

