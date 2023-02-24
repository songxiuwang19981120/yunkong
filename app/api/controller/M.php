<?php

namespace app\api\controller;


class M
{



    function MemberList()
    {
        $res = \app\api\model\Member::limit(20)->select();
        return json_encode($res);
    }
}