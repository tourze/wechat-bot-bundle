#### <font style="color:#F5222D;">下载可能需要较长时间，请注意调整请求超时时间。</font>
#### 网关地址


<font style="background:#F8CED3;color:#70000D">POST</font>** http://网关地址/open/getCDNFile**



#### 请求header
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| Authorization | string | 是 |  | API平台认证信息 |


#### 请求body
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| <font style="color:#364149;">deviceId</font> | string | 是 |  | 设备标识（创建设备时提供的唯一值） |
| <font style="color:#364149;background-color:#FAFAFA;">cdnUrl</font> | <font style="color:#364149;background-color:#FAFAFA;">string</font> | 是 | <font style="color:#364149;background-color:#FAFAFA;"></font> | xml 中的 cdnurl 或 cdnthumburl |
| <font style="color:#364149;background-color:#FFFFFF;">aesKey</font> | <font style="color:#364149;background-color:#FFFFFF;">string</font> | 是 |  | xml 中的 aeskey 或 thumbaeskey |
| fileType | number |  是 | | 资源类型：<br/>1：高清图<br/>2：普通图<br/>3：缩略图<br/>4：视频<br/>注意：并非所有图片都有高清图，如遇高清图无返回时，请使用普通图或缩略图重试 |
| length | number | 否 | | 非必填，当下载文件出现链接不可访问或下载失败时，可传入 xml 内的 length 以提高成功率 |


#### 响应数据<font style="background:#F8CED3;color:#70000D">数据格式：JSON</font>
```json
{
    "message": "成功",
    "code": "1000",
    "data": {
        "url": "www.xxx.txt"
    }
}
```

#### 相应数据格式说明
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| <font style="color:#364149;background-color:#FFFFFF;">url</font> | string | 是 |  | 下载链接（此链接保存3天） |




