<?php 
/*
 module:		往期账户视频数据
 create_time:	2023-02-13 12:44:39
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;
use app\admin\model\PastMemberVideoData;
use think\exception\ValidateException;
use base\CommonService;

class PastMemberVideoDataService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = PastMemberVideoData::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['rows'=>$res['data'],'total'=>$res['total']];
	}




}

