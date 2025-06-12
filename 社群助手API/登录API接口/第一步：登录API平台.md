#### 网关地址

<font style="background:#F8CED3;color:#70000D">POST</font>******<http://网关地址/auth/login>**

<font style="background:#DBF1B7;color:#2A4200">联系客服开通API平台账号密码</font>

#### 请求body<font style="color:#F5222D;">（Content-Type:application/x-www-form-urlencoded 或 form-data 方式提交，下同不再重复）</font>

| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| username | string | 必填 |  | API平台账号 |
| password | string | 必填 |  | API平台密码 |

#### 响应数据<font style="background:#F8CED3;color:#70000D">数据格式：JSON</font>

```json
{
    "message": "成功",
    "code": "1000",
    "data": {
        "authorization": "授权密钥，生成后永久有效，如需变更，使用重置接口调用凭证接口重置",
        "username": "测试用户",
        "balance": 0,
        "callbackUrl": "xxxx",
    }
}
```

#### 响应书数据参数说明

| **名称** | **类型** | **填写** | **默认值** | **说明** |
| --- | --- | --- | --- | --- |
| authorization | string | 必填 |  | 授权密钥，永久 |
| name | string | 必填 |  | 账户名称 |
| balance | <font style="color:#364149;background-color:#FFFFFF;">number</font> | 必填 |  | 账户余额 |
| <font style="color:#364149;background-color:#FFFFFF;">callbackUrl</font> | string | 必填 |  | 消息回调地址 |

#### 备注
