<?php 
/*
 module:		往期账户数据
 create_time:	2023-02-13 12:39:19
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;
use app\admin\model\PastMemberData;
use think\exception\ValidateException;
use base\CommonService;

class PastMemberDataService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = PastMemberData::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['rows'=>$res['data'],'total'=>$res['total']];
	}




}

