#### 转发失败排查：
1. 请检查你的 xml 是否符合语法要求，自查工具：[https://tool.oschina.net/codeformat/xml](https://tool.oschina.net/codeformat/xml)
2. 请检查 url 是否正确的 urlencode 了
    1. 正确： [http://xxx.xx/xxx?xx](http://xxx.xx/xxx?xx)<font style="color:#F5222D;">&amp;</font>=xxx
    2. 错误：[http://xxx.xx/xxx?xx](http://xxx.xx/xxx?xx)<font style="color:#F5222D;">&</font>=xx



#### 网关地址


<font style="background:#F8CED3;color:#70000D">POST</font>** http://网关地址/open/****forwardUrl**



#### 请求header
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| Authorization | string | 是 |  | API平台认证信息 |


#### 请求body
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| <font style="color:#364149;">deviceId</font> | string | 是 |  | 设备标识（创建设备时提供的唯一值） |
| wxId | string | 是 | 8888@chatroom | 接收人（微信id <font style="color:#DF2A3F;">通常以 wcid 开头</font>或者群号<font style="color:#DF2A3F;">通常以 @chatroom 结尾</font>） |
| content | string | 是 |  | xml 消息体  |


#### 响应数据<font style="background:#F8CED3;color:#70000D">数据格式：JSON</font>
```json
{
    "message": "发送成功",
    "code": "1000",
    "data": " "
}
```

#### content 示例
```http
<?xml version="1.0"?> <msg> <videomsg aeskey="4f54430bcf53acfe9ef6b5d36d58e9f5" cdnthumbaeskey="4f54430bcf53acfe9ef6b5d36d58e9f5" cdnvideourl="306c020100046530630201000204f032c33602032f55f90204890260b402045e05b42a043e617570766964656f5f666661336336323865323964323566345f313537373433323130345f313533353034323731323139633662336333613434323131350204010400040201000400" cdnthumburl="306c020100046530630201000204f032c33602032f55f90204890260b402045e05b42a043e617570766964656f5f666661336336323865323964323566345f313537373433323130345f313533353034323731323139633662336333613434323131350204010400040201000400" length="7833957" playlength="61" cdnthumblength="12426" cdnthumbwidth="288" cdnthumbheight="512" fromusername="zhongweiyu789" md5="1ed727c57156b5f897e9e05a98912d80" newmd5="d4f771f94ae15c4400b6dccff54068e9" isad="0" /> </msg>
```

