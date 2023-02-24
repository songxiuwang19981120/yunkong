<?php
/*
 module:		任务详情
 create_time:	2022-12-07 11:55:39
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\QueueDetail;
use base\CommonService;
use think\exception\ValidateException;

class QueueDetailService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = db('queue_detail')->field($field)->alias('a')->join('queue b', 'a.queue_id=b.queue_id', 'left')->where($where)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


    /*
     * @Description  添加
     */
    public static function add($data)
    {
        try {
            $data['execution_time'] = time();
            $res = QueueDetail::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->detail_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            $data['execution_time'] = strtotime($data['execution_time']);
            $res = QueueDetail::update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res;
    }


}

