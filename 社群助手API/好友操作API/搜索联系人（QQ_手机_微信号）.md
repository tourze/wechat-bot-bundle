#### 网关地址
<font style="background:#F8CED3;color:#70000D">POST</font>** http://网关地址/open/searchUser**

#### 请求header
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| Authorization | string | 是 |  | API平台认证信息 |


#### 请求body
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| <font style="color:#364149;">deviceId</font> | string | 是 |  | 设备标识（创建设备时提供的唯一值） |
| <font style="color:#364149;background-color:#FAFAFA;">wxId</font> | string | 是 |  | 搜索的微信号/手机号（和手机搜索一样的功能，不支持wxid开头的） |


#### 响应数据<font style="background:#F8CED3;color:#70000D">数据格式：JSON</font>
```json
{
    "code": "1000",
    "message": "搜索联系人成功",
    "data": {
        "userName": "wang_xxxxx",
        "nickName": "隔壁老王",
        "sex": -5916789814496961,
        "bigHead": "http://wx.qlogo.cn/mmhead/ver_1/",
        "smallHead": "http://wx.qlogo.cn/mmhead/ver_1//132",
        "v1": "v1_d1xxxxxx@stranger",
        "v2": "v4_0002bxxxxx@stranger",
        "wcId": "wxid__xxxxxxxxx"
    }
}
```

#### 响应书数据参数说明
<font style="color:#F5222D;">如果搜索对象已经是你的好友，那么 v2 不会返回，或者返回 null 或空字符串，而 v1 则返回搜索对象的 wxid。</font>

<font style="color:#F5222D;">（PS：单向好友也是好友）</font>

| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| userName | string | 是 |  | V1或是微信ID |
| nickName | string | 是 |  | 昵称 |
| sex | number | 是 |  | 性别 |
| bigHead | string | 是 |  | 大头像 |
| <font style="color:#364149;background-color:#FFFFFF;">smallHead</font> |  |  |  | 小头像 |
| <font style="color:#364149;background-color:#FAFAFA;">v1</font> |  |  |  | 添加好友所需凭证 |
| <font style="color:#364149;background-color:#FFFFFF;">v2</font> |  |  |  | 添加好友所需凭证 |
| <font style="color:#364149;background-color:#FAFAFA;">wcId</font> |  |  |  | 微信id |


#### 
