#### 网关地址


<font style="background:#F8CED3;color:#70000D">POST</font>** **[**<font style="color:#000000;">http://网关地址</font>**](http://域名/member/login)**<font style="color:#000000;">/open/</font>****<font style="color:#000000;background-color:#F8F8F8;">sendEmoji</font>**



#### 请求header
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| Authorization | string | 是 |  | API平台认证信息 |


#### 请求body
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| <font style="color:#364149;">deviceId</font> | string | 是 |  | 设备标识（创建设备时提供的唯一值） |
| wxId | string | 是 | 88@chatroom | 接收人（微信id <font style="color:#DF2A3F;">通常以 wcid 开头</font>或者群号<font style="color:#DF2A3F;">通常以 @chatroom 结尾</font>） |
| <font style="color:#364149;background-color:#FFFFFF;">imageMd5</font> | string | 是 |  | 取回调中xml中md5字段值 |
| <font style="color:#364149;background-color:#FAFAFA;">imgSize</font> | string | 是 |  | 取回调中xml中len字段值 |


#### 响应数据<font style="background:#F8CED3;color:#70000D">数据格式：JSON</font>
```json
{
    "message": "发送成功",
    "code": "1000",
    "data": " "
}
```



