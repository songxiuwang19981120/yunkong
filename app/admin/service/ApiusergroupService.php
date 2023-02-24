<?php
/*
 module:		api_user_group
 create_time:	2023-01-02 14:51:10
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Apiusergroup;
use base\CommonService;
use think\exception\ValidateException;

class ApiusergroupService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Apiusergroup::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['createtime'] = time();
            $res = Apiusergroup::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            $data['createtime'] = strtotime($data['createtime']);
            $data['updatetime'] = time();
            $res = Apiusergroup::update($data);
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

