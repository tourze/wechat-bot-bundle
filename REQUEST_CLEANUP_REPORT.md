# Request类清理报告

生成时间：2025-07-03

## 一、清理概述

为保持代码结构清晰，已删除根目录下的重复Request类，保留子目录版本。

## 二、删除的文件清单

### 1. 好友操作相关（6个文件）
- `src/Request/SearchContactRequest.php` → 保留 `src/Request/Friend/SearchContactRequest.php`
- `src/Request/GetContactInfoRequest.php` → 保留 `src/Request/Friend/GetContactInfoRequest.php`
- `src/Request/AddFriendRequest.php` → 保留 `src/Request/Friend/AddFriendRequest.php`
- `src/Request/DeleteFriendRequest.php` → 保留 `src/Request/Friend/DeleteFriendRequest.php`
- `src/Request/UpdateFriendRemarkRequest.php` → 保留 `src/Request/Friend/UpdateFriendRemarkRequest.php`
- `src/Request/AcceptFriendRequest.php` → 保留 `src/Request/Friend/AcceptFriendRequest.php`

### 2. 群操作相关（8个文件）
- `src/Request/AtGroupMemberRequest.php` → 保留 `src/Request/Group/AtGroupMemberRequest.php`
- `src/Request/UpdateGroupNameRequest.php` → 保留 `src/Request/Group/UpdateGroupNameRequest.php`
- `src/Request/LeaveGroupRequest.php` → 保留 `src/Request/Group/LeaveGroupRequest.php`
- `src/Request/AddGroupMemberRequest.php` → 保留 `src/Request/Group/AddGroupMemberRequest.php`
- `src/Request/InviteGroupMemberRequest.php` → 保留 `src/Request/Group/InviteGroupMemberRequest.php`
- `src/Request/GetGroupMembersRequest.php` → 保留 `src/Request/Group/GetGroupMembersRequest.php`
- `src/Request/GetGroupDetailRequest.php` → 保留 `src/Request/Group/GetGroupDetailRequest.php`
- `src/Request/AcceptGroupInviteRequest.php` → 保留 `src/Request/Group/AcceptGroupInviteRequest.php`

### 3. 下载相关（2个文件）
- `src/Request/DownloadFileRequest.php` → 保留 `src/Request/Download/DownloadFileRequest.php`
- `src/Request/DownloadVideoRequest.php` → 保留 `src/Request/Download/DownloadVideoRequest.php`

## 三、更新的引用文件

### 1. Service文件
- `src/Service/WeChatContactService.php` - 更新了AcceptFriendRequest的引用
- `src/Service/WeChatGroupService.php` - 已使用子目录版本，无需更新

### 2. 测试文件
- `tests/Unit/Request/AcceptFriendRequestTest.php` - 更新为Friend子目录版本
- `tests/Unit/Request/AddFriendRequestTest.php` - 更新为Friend子目录版本
- `tests/Unit/Request/AcceptGroupInviteRequestTest.php` - 更新为Group子目录版本
- `tests/Unit/Request/AddGroupMemberRequestTest.php` - 更新为Group子目录版本
- `tests/Unit/Request/AtGroupMemberRequestTest.php` - 更新为Group子目录版本

## 四、清理结果

- **删除文件总数**：16个
- **更新引用文件**：6个
- **代码结构优化**：所有Request类现在按功能分类存放在相应子目录中

## 五、后续建议

1. **命名空间一致性**：确保所有新增的Request类都放在相应的子目录中
2. **测试覆盖**：运行单元测试确保所有功能正常
3. **文档更新**：更新开发文档，说明Request类的组织结构

## 六、验证步骤

建议执行以下命令验证清理结果：

```bash
# 检查是否还有遗漏的引用
grep -r "use.*Request\\\\(SearchContact|GetContactInfo|AddFriend|DeleteFriend|UpdateFriendRemark|AcceptFriend)Request" src/ tests/

# 运行单元测试
vendor/bin/phpunit packages/wechat-bot-bundle/tests/

# 运行代码质量检查
vendor/bin/phpstan analyse packages/wechat-bot-bundle/
```