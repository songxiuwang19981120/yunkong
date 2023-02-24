<?php 
/*
 module:		私信素材分类验证器
 create_time:	2023-02-10 18:47:29
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;
use think\validate;

class Classname extends validate {


	protected $rule = [
		'classname'=>['require'],
	];

	protected $message = [
		'classname.require'=>'名称不能为空',
	];

	protected $scene  = [
		'add'=>['classname'],
		'update'=>['classname'],
	];



}

