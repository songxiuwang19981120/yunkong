<?php 
/*
 module:		往期账户视频数据
 create_time:	2023-02-13 12:44:39
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\PastMemberVideoDataService;
use app\admin\model\PastMemberVideoData as PastMemberVideoDataModel;
use think\facade\Db;

class PastMemberVideoData extends Admin {


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
			$where['aweme_id'] = $this->request->param('aweme_id', '', 'serach_in');
			$where['collect_count'] = $this->request->param('collect_count', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'pastmembervideodata_id,uid,aweme_id,play_num,share_count,collect_count,download_count,updata_time';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'pastmembervideodata_id desc';

			$res = PastMemberVideoDataService::indexList(formatWhere($where, $model),$field,$orderby,$limit,$page);
			return json($res);
		}
	}



}

