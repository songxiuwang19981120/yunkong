<?php 
/*
 module:		往期账户数据
 create_time:	2023-02-13 12:39:19
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\PastMemberDataService;
use app\admin\model\PastMemberData as PastMemberDataModel;
use think\facade\Db;

class PastMemberData extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['uid'] = $this->request->param('uid', '', 'serach_in');
			$where['typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'pastmemberdata,uid,follower_status,following_count,total_favorited,aweme_count,unread_viewer_count,typecontrol_id,updata_time,play_num,share_count,collect_count,download_count';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'pastmemberdata desc';

			$res = PastMemberDataService::indexList(formatWhere($where, $model),$field,$orderby,$limit,$page);
			return json($res);
		}
	}



}

