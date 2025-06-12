#### <font style="color:#F5222D;">上传可能需要较长时间，请注意调整请求超时时间。</font>
#### 网关地址


<font style="background:#F8CED3;color:#70000D">POST</font>** http://网关地址/open/uploadCdnImage**



#### 请求header
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| Authorization | string | 是 |  | API平台认证信息 |


#### 请求body
| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| <font style="color:#364149;">deviceId</font> | string | 是 |  | 设备标识（创建设备时提供的唯一值） |
| <font style="color:#364149;background-color:#FAFAFA;">path</font> | <font style="color:#364149;background-color:#FAFAFA;">string</font> | 是 | <font style="color:#364149;background-color:#FAFAFA;"></font> | 需要上传的图片 |


#### 响应数据<font style="background:#F8CED3;color:#70000D">数据格式：JSON</font>
```json
{
    "message": "上传成功",
    "code": "1000",
    "data": {
        "originUrl" : "上传图片 url",
    		"cdnUrl" : "上传后的 cdn url",
    		"aesKey" : "上传后的 cdn key",
    		"length" : "上传后的图片大小"
    }
}
```



