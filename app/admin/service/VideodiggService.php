<?php
/*
 module:		作品点赞
 create_time:	2022-12-05 16:53:07
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Videodigg;
use base\CommonService;

class VideodiggService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Videodigg::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

