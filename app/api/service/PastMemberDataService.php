<?php 
/*
 module:		往期账户数据
 create_time:	2023-02-13 13:34:42
 author:		大怪兽
 contact:		
*/

namespace app\api\service;
use app\api\model\PastMemberData;
use think\facade\Log;
use think\exception\ValidateException;
use base\CommonService;

class PastMemberDataService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = PastMemberData::where($where)->field($field)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['list'=>$res['data'],'count'=>$res['total']];
	}




}

