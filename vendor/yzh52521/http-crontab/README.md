# 接口化秒级定时任务

## 概述

基于 **Workerman** + **ThinkPHP6** 的接口化秒级定时任务管理，兼容 Windows 和 Linux 系统。

## 定时器格式说明：

```
0   1   2   3   4   5
|   |   |   |   |   |
|   |   |   |   |   +------ day of week (0 - 6) (Sunday=0)
|   |   |   |   +------ month (1 - 12)
|   |   |   +-------- day of month (1 - 31)
|   |   +---------- hour (0 - 23)
|   +------------ min (0 - 59)
+-------------- sec (0-59)[可省略，如果没有0位,则最小时间粒度是分钟]
```
## 任务分类
* url 任务可以指定一个url地址来请求，没有什么可解释的。

* Class 任务必须指定带有 命名空间的类名，并且实现一个 public 属性的方法：execute 方法返回值为 bool/string类型，支持自定义方法  事例：target='app\\common\\model\\User@check'  @后面是方法 

* Command 任务请先按照 thinkphp 官方文档定义好执行命令，在新增任务，输入定义的 命令 即可 例如：version

* Shell 任务 在新增任务，输入定义的 shell命令 即可 例如：ps -ef | grep php

## 简单使用


**启动服务**

![](https://www.workerman.net/upload/img/20220627/2762b9758c4cf8.jpg)

**效果图**
![](https://www.workerman.net/upload/img/20220412/12625506364e29.png)
![](https://www.workerman.net/upload/img/20220412/12625506890a16.png)




 <h1 class="curproject-name"> 定时器接口说明 </h1> 

## PING

<a id=PING> </a>

### 基本信息

**Path：** /crontab/ping

**Method：** GET

**接口描述：**

```
{
     "code": 200,
     "data": "pong",
     "msg": "信息调用成功！"
}
```

### 请求参数

### 返回数据

<table>
  <thead class="ant-table-thead">
    <tr>
      <th key=name>名称</th><th key=type>类型</th>
<th key=required>是否必须</th>
<th key=default>默认值</th>
<th key=desc>备注</th>
<th key=sub>其他信息</th>
    </tr>
  </thead><tbody className="ant-table-tbody"><tr key=0-0><td key=0><span style="padding-left: 0px"><span style="color: #8c8a8a"></span> code</span></td><td key=1><span>number</span></td><td key=2>非必须</td><td key=3></td><td key=4><span style="white-space: pre-wrap"></span></td><td key=5></td></tr><tr key=0-1><td key=0><span style="padding-left: 0px"><span style="color: #8c8a8a"></span> data</span></td><td key=1><span>string</span></td><td key=2>非必须</td><td key=3></td><td key=4><span style="white-space: pre-wrap"></span></td><td key=5></td></tr><tr key=0-2><td key=0><span style="padding-left: 0px"><span style="color: #8c8a8a"></span> msg</span></td><td key=1><span>string</span></td><td key=2>非必须</td><td key=3></td><td key=4><span style="white-space: pre-wrap"></span></td><td key=5></td></tr>
               </tbody>
              </table>

## 修改

<a id=修改> </a>

### 基本信息

**Path：** /crontab/modify

**Method：** POST

**接口描述：**

```json
{
  "code": 200,
  "data": true,
  "msg": "信息调用成功！"
}
```

### 请求参数

**Headers**

| 参数名称     | 参数值                            | 是否必须 | 示例 | 备注 |
| ------------ | --------------------------------- | -------- | ---- | ---- |
| Content-Type | application/x-www-form-urlencoded | 是       |      |      |

**Body**

| 参数名称 | 参数类型 | 是否必须 | 示例   | 备注                               |
| -------- | -------- | -------- | ------ |----------------------------------|
| id       | text     | 是       | 1      |                                  |
| field    | text     | 是       | status | 字段[status; sort; remark; title,rule] |
| value    | text     | 是       | 1      | 值                                |


## 列表

<a id=列表> </a>

### 基本信息

**Path：** /crontab/index

**Method：** GET

**接口描述：**

<pre><code>{
&nbsp; "code": 200,
&nbsp; "data": {
    "total": 4,
&nbsp;&nbsp;&nbsp; "data": [
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "id": 1,
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "title": "输出 tp 版本",
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "type": 1,
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "rule": "*/3 * * * * *",
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "target": "version",
        "parameter": "",
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "running_times": 3,
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "last_running_time": 1625636646,
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "remark": "每3秒执行",
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "sort": 0,
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "status": 1,
        "singleton": 1,
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "create_time": 1625636609,
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "update_time": 1625636609
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; },
        {
        "id": 2,
        "title": "class任务 每月1号清理所有日志",
        "type": 2,
        "rule": "0 0 1 * *",
        "target": "app\\common\\crontab\\ClearLogCrontab",
        "parameter": "",
        "running_times": 71,
        "last_running_time": 1651121710,
        "remark": "",
        "sort": 0,
        "status": 1,
        "create_time": 1651114277,
        "update_time": 1651114277,
        "singleton": 1
        },
&nbsp;&nbsp;&nbsp; ],
&nbsp; },
&nbsp; "msg": "信息调用成功！"
}
</code></pre>

### 请求参数

**Query**

| 参数名称  | 是否必须 | 示例                    | 备注         |
|-------| -------- |-----------------------| ------------ |
| page  | 是       | 1                     | 页码         |
| limit | 是       | 15                    | 每页条数     |
| filter   | 否       | {"title":"输出 tp 版本"}  | 检索字段值   |
| op       | 否       | {"title":"%*%"}       | 检索字段操作 |


## 删除

<a id=删除> </a>

### 基本信息

**Path：** /crontab/delete

**Method：** POST

**接口描述：**

```json
{
     "code": 200,
     "data": true,
     "msg": "信息调用成功！"
}
```


### 请求参数

**Headers**

| 参数名称     | 参数值                            | 是否必须 | 示例 | 备注 |
| ------------ | --------------------------------- | -------- | ---- | ---- |
| Content-Type | application/x-www-form-urlencoded | 是       |      |      |

**Body**

| 参数名称 | 参数类型 | 是否必须 | 示例 | 备注 |
| -------- | -------- | -------- | ---- | ---- |
| id       | text     | 是       | 1,2  |      |


## 定时器池

<a id=定时器池> </a>

### 基本信息

**Path：** /crontab/pool

**Method：** GET

**接口描述：**
```
"code": 200,
"data": [
 {
   "id": 1,
   "type": 1,
   "target": "version",
   "rule": "*/3 * * * * *",
   "parameter": "",
   "remark": "每3秒执行",
   "singleton":1,
   "create_time": "2021-07-07 13:43:29"
 }
],
 "msg": "信息调用成功！"
}
```

### 请求参数


## 日志

<a id=日志> </a>

### 基本信息

**Path：** /crontab/flow

**Method：** GET

**接口描述：**

```
{
  "code": 200,
  "msg": "ok",
  "data": {
    "total": 97,
    "data": [
      {
        "id": 257,
        "crontab_id": 1,
        "target": "version",
        "parameter": "",
        "exception": "v6.0.12LTS",
        "return_code": 0,
        "running_time": "0.834571",
        "create_time": 1651123800,
        "update_time": 1651123800
      },
      {
        "id": 251,
        "crontab_id": 1,
        "target": "version",
        "parameter": "",
        "exception": "v6.0.12LTS",
        "return_code": 0,
        "running_time": "0.540384",
        "create_time": 1651121700,
        "update_time": 1651121700
      },
      {
        "id": 246,
        "crontab_id": 1,
        "target": "version",
        "parameter": "",
        "exception": "v6.0.12LTS",
        "return_code": 0,
        "running_time": "0.316019",
        "create_time": 1651121640,
        "update_time": 1651121640
      },
      {
        "id": 244,
        "crontab_id": 1,
        "target": "version",
        "parameter": "",
        "exception": "v6.0.12LTS",
        "return_code": 0,
        "running_time": "0.493848",
        "create_time": 1651121580,
        "update_time": 1651121580
      }
    ]
  }
}
```
### 请求参数

**Query**

| 参数名称  | 是否必须 | 示例                 | 备注         |
|-------| -------- |--------------------| ------------ |
| page  | 是       | 1                  | 页码         |
| limit | 是       | 15                 | 每页条数     |
| filter   | 否       | {"crontab_id":"1"} | 检索字段值   |
| op       | 否       | {"crontab_id":"="}        | 检索字段操作 |

## 添加

<a id=添加> </a>

### 基本信息

**Path：** /crontab/add

**Method：** POST

**接口描述：**

```
{
    "code": 200,
    "data": true,
    "msg": "信息调用成功！"
}
```

### 请求参数

**Headers**

| 参数名称     | 参数值                            | 是否必须 | 示例 | 备注 |
| ------------ | --------------------------------- | -------- | ---- | ---- |
| Content-Type | application/x-www-form-urlencoded | 是       |      |      |

**Body**

| 参数名称   | 参数类型 | 是否必须 | 示例          | 备注                                       |
|--------| -------- |-----|-------------|------------------------------------------|
| title  | text     | 是   | 输出 thinkphp 版本 | 任务标题                                     |
| type   | text     | 是   | 1           | 任务类型 (1 command, 2 class, 3 url,4 shell) |
| rule   | text     | 是   | */3 * * * * * | 任务执行表达式                                  |
| target | text     | 是   | version     | 调用任务字符串                                  |
| parameter | text     | 否   |             | 调用任务参数(url和shell无效)                       |
| remark | text     | 是   | 每3秒执行       | 备注                                       |
| sort   | text     | 是   | 0           | 排序                                       |
| status | text     | 是   | 1           | 状态[0禁用; 1启用]                             |
| singleton | text     | 否    | 1           | 是否单次执行 [0 是 1 不是]                        |


## 重启

<a id=重启> </a>

### 基本信息

**Path：** /crontab/reload

**Method：** POST

**接口描述：**

```json
{
  "code": 200,
  "msg": "信息调用成功",
  "data": {
  }
}
```

### 请求参数

**Headers**

| 参数名称     | 参数值                            | 是否必须 | 示例 | 备注 |
| ------------ | --------------------------------- | -------- | ---- | ---- |
| Content-Type | application/x-www-form-urlencoded | 是       |      |      |

**Body**

| 参数名称 | 参数类型 | 是否必须 | 示例 | 备注 |
| -------- | -------- | -------- | ---- | ---- |
| id       | text     | 是       | 1,2  |   计划任务ID 多个逗号隔开   |

### 返回数据

```json
{
  "code": 200,
  "msg": "信息调用成功",
  "data": {
  }
}
```          
