<?php

namespace app\api\job;

use app\api\model\TaskListDetail;
use think\facade\Log;
use think\queue\Job;

class TaskExpire
{
    /**
     * fire是消息队列默认调用的方法
     * @param Job $job 当前的任务对象
     * @param array|mixed $taskdetail_id 发布任务时自定义的数据
     */
    public function fire(Job $job, $taskdetail_id)
    {
        //执行业务处理
        if ($this->doJob($taskdetail_id)) {
            $job->delete();//任务执行成功后删除
        } else {
            //检查任务重试次数
            if ($job->attempts() > 3) $job->delete();
        }
    }

    function doJob($taskdetail_id)
    {
        $detail = TaskListDetail::where('tasklistdetail_id', $taskdetail_id)->find();
        Log::write("detail:" . json_encode($detail));
        if ($detail->status != TaskListDetail::$successCode && $detail->status != TaskListDetail::$failCode) {
            $detail->save(['status' => TaskListDetail::$expireCode]);
            return true;
        }
        return true;
    }
}