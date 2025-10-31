<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;

/**
 * WeChatContact 实体单元测试
 *
 * @internal
 */
#[CoversClass(WeChatContact::class)]
final class WeChatContactTest extends AbstractEntityTestCase
{
    private WeChatAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = new WeChatAccount();
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $contact = new WeChatContact();

        // 验证默认值
        $this->assertNull($contact->getId());
        $this->assertNull($contact->getNickname());
        $this->assertNull($contact->getRemarkName());
        $this->assertNull($contact->getAvatar());
        $this->assertEquals('friend', $contact->getContactType());
        $this->assertNull($contact->getGender());
        $this->assertNull($contact->getRegion());
        $this->assertNull($contact->getSignature());
        $this->assertNull($contact->getTags());
        $this->assertNull($contact->getAddFriendTime());
        $this->assertNull($contact->getLastChatTime());
        $this->assertTrue($contact->isValid());
        $this->assertNull($contact->getRemark());
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $contact = new WeChatContact();

        // 测试账号
        $contact->setAccount($this->account);
        $this->assertSame($this->account, $contact->getAccount());

        // 测试联系人ID
        $contactId = 'test_contact_123';
        $contact->setContactId($contactId);
        $this->assertEquals($contactId, $contact->getContactId());

        // 测试昵称
        $nickname = 'Test User';
        $contact->setNickname($nickname);
        $this->assertEquals($nickname, $contact->getNickname());

        // 测试备注名
        $remarkName = 'Test Remark';
        $contact->setRemarkName($remarkName);
        $this->assertEquals($remarkName, $contact->getRemarkName());

        // 测试头像
        $avatar = 'https://example.com/avatar.jpg';
        $contact->setAvatar($avatar);
        $this->assertEquals($avatar, $contact->getAvatar());

        // 测试联系人类型
        $contactType = 'stranger';
        $contact->setContactType($contactType);
        $this->assertEquals($contactType, $contact->getContactType());

        // 测试性别
        $gender = 'male';
        $contact->setGender($gender);
        $this->assertEquals($gender, $contact->getGender());

        // 测试地区
        $region = 'Beijing, China';
        $contact->setRegion($region);
        $this->assertEquals($region, $contact->getRegion());

        // 测试个性签名
        $signature = 'This is a test signature';
        $contact->setSignature($signature);
        $this->assertEquals($signature, $contact->getSignature());

        // 测试标签
        $tags = 'tag1,tag2,tag3';
        $contact->setTags($tags);
        $this->assertEquals($tags, $contact->getTags());

        // 测试添加好友时间
        $addFriendTime = new \DateTimeImmutable('2024-01-01 12:00:00');
        $contact->setAddFriendTime($addFriendTime);
        $this->assertEquals($addFriendTime, $contact->getAddFriendTime());

        // 测试最后聊天时间
        $lastChatTime = new \DateTimeImmutable('2024-01-01 18:00:00');
        $contact->setLastChatTime($lastChatTime);
        $this->assertEquals($lastChatTime, $contact->getLastChatTime());

        // 测试有效性
        $contact->setValid(false);
        $this->assertFalse($contact->isValid());

        // 测试备注
        $remark = 'Test remark';
        $contact->setRemark($remark);
        $this->assertEquals($remark, $contact->getRemark());
    }

    public function testToStringReturnsDisplayNameAndType(): void
    {
        $contact = new WeChatContact();
        $contact->setContactId('test_contact_123');
        $contact->setNickname('Test User');
        $contact->setContactType('friend');

        $result = (string) $contact;
        $this->assertStringContainsString('Test User', $result);
        $this->assertStringContainsString('friend', $result);
    }

    public function testGetDisplayNameReturnsRemarkNameFirst(): void
    {
        $contact = new WeChatContact();
        $contact->setContactId('test_contact_123');
        $contact->setNickname('Test User');
        $contact->setRemarkName('My Friend');

        $this->assertEquals('My Friend', $contact->getDisplayName());
    }

    public function testGetDisplayNameReturnsNicknameWhenNoRemarkName(): void
    {
        $contact = new WeChatContact();
        $contact->setContactId('test_contact_123');
        $contact->setNickname('Test User');

        $this->assertEquals('Test User', $contact->getDisplayName());
    }

    public function testGetDisplayNameReturnsContactIdWhenNoRemarkNameAndNickname(): void
    {
        $contact = new WeChatContact();
        $contact->setContactId('test_contact_123');

        $this->assertEquals('test_contact_123', $contact->getDisplayName());
    }

    public function testContactTypeCheckers(): void
    {
        $contact = new WeChatContact();

        // 测试默认类型
        $this->assertTrue($contact->isFriend());
        $this->assertFalse($contact->isStranger());
        $this->assertFalse($contact->isBlacklisted());

        // 测试陌生人
        $contact->setContactType('stranger');
        $this->assertFalse($contact->isFriend());
        $this->assertTrue($contact->isStranger());
        $this->assertFalse($contact->isBlacklisted());

        // 测试黑名单
        $contact->setContactType('blacklist');
        $this->assertFalse($contact->isFriend());
        $this->assertFalse($contact->isStranger());
        $this->assertTrue($contact->isBlacklisted());
    }

    public function testTagsArrayHandling(): void
    {
        $contact = new WeChatContact();

        // 测试空标签数组
        $this->assertEquals([], $contact->getTagsArray());

        // 测试设置标签数组
        $tagsArray = ['tag1' => 'tag1', 'tag2' => 'tag2', 'tag3' => 'tag3'];
        $contact->setTagsArray($tagsArray);
        $this->assertEquals(['tag1', 'tag2', 'tag3'], $contact->getTagsArray());
        $this->assertEquals('tag1,tag2,tag3', $contact->getTags());

        // 测试设置空数组
        $contact->setTagsArray([]);
        $this->assertEquals([], $contact->getTagsArray());
        $this->assertNull($contact->getTags());

        // 测试从字符串解析
        $contact->setTags('tag1, tag2 , tag3 ');
        $this->assertEquals(['tag1', 'tag2', 'tag3'], $contact->getTagsArray());
    }

    public function testAddTag(): void
    {
        $contact = new WeChatContact();

        // 添加第一个标签
        $contact->addTag('tag1');
        $this->assertEquals(['tag1'], $contact->getTagsArray());

        // 添加第二个标签
        $contact->addTag('tag2');
        $this->assertEquals(['tag1', 'tag2'], $contact->getTagsArray());

        // 添加重复标签
        $contact->addTag('tag1');
        $this->assertEquals(['tag1', 'tag2'], $contact->getTagsArray());
    }

    public function testRemoveTag(): void
    {
        $contact = new WeChatContact();
        $contact->setTagsArray(['tag1' => 'tag1', 'tag2' => 'tag2', 'tag3' => 'tag3']);

        // 移除存在的标签
        $contact->removeTag('tag2');
        $this->assertEquals(['tag1', 'tag3'], $contact->getTagsArray());

        // 移除不存在的标签
        $contact->removeTag('tag4');
        $this->assertEquals(['tag1', 'tag3'], $contact->getTagsArray());

        // 移除所有标签
        $contact->removeTag('tag1');
        $contact->removeTag('tag3');
        $this->assertEquals([], $contact->getTagsArray());
    }

    public function testUpdateLastChatTimeSetsCurrentTime(): void
    {
        $contact = new WeChatContact();
        $beforeUpdate = new \DateTimeImmutable();

        $contact->updateLastChatTime();

        $afterUpdate = new \DateTimeImmutable();
        $lastChatTime = $contact->getLastChatTime();

        $this->assertNotNull($lastChatTime);
        if ($lastChatTime instanceof \DateTime) {
            $this->assertGreaterThanOrEqual($beforeUpdate, $lastChatTime);
            $this->assertLessThanOrEqual($afterUpdate, $lastChatTime);
        }
    }

    public function testSetterMethods(): void
    {
        $contact = new WeChatContact();

        $contact->setAccount($this->account);
        $contact->setContactId('test_contact');
        $contact->setNickname('Test User');
        $contact->setRemarkName('My Friend');
        $contact->setAvatar('https://example.com/avatar.jpg');
        $contact->setContactType('friend');
        $contact->setGender('male');
        $contact->setRegion('Beijing');
        $contact->setSignature('Test signature');
        $contact->setTags('tag1,tag2');
        $contact->setValid(true);
        $contact->setRemark('Test remark');

        // 验证所有设置的值
        $this->assertSame($this->account, $contact->getAccount());
        $this->assertEquals('test_contact', $contact->getContactId());
        $this->assertEquals('Test User', $contact->getNickname());
        $this->assertEquals('My Friend', $contact->getRemarkName());
        $this->assertEquals('https://example.com/avatar.jpg', $contact->getAvatar());
        $this->assertEquals('friend', $contact->getContactType());
        $this->assertEquals('male', $contact->getGender());
        $this->assertEquals('Beijing', $contact->getRegion());
        $this->assertEquals('Test signature', $contact->getSignature());
        $this->assertEquals('tag1,tag2', $contact->getTags());
        $this->assertTrue($contact->isValid());
        $this->assertEquals('Test remark', $contact->getRemark());
    }

    public function testTagsArrayWithEmptyAndWhitespaceElements(): void
    {
        $contact = new WeChatContact();

        // 测试包含空元素和空白的字符串 - array_filter不重新索引
        $contact->setTags('tag1,  , tag2,   , tag3');
        $result = $contact->getTagsArray();
        $this->assertEquals(['tag1', 'tag2', 'tag3'], array_values($result));

        // 测试只有空白的字符串
        $contact->setTags('  ,  ,  ');
        $this->assertEquals([], $contact->getTagsArray());

        // 测试空字符串
        $contact->setTags('');
        $this->assertEquals([], $contact->getTagsArray());
    }

    public function testGetTagsArrayWithNullTags(): void
    {
        $contact = new WeChatContact();
        $contact->setTags(null);

        $this->assertEquals([], $contact->getTagsArray());
    }

    public function testSetTagsArrayWithEmptyArraySetsNull(): void
    {
        $contact = new WeChatContact();
        $contact->setTagsArray([]);

        $this->assertNull($contact->getTags());
    }

    public function testAddTagMethod(): void
    {
        $contact = new WeChatContact();

        $contact->addTag('tag1');
        $this->assertContains('tag1', $contact->getTagsArray());

        $contact->addTag('tag2');
        $this->assertContains('tag2', $contact->getTagsArray());
    }

    public function testRemoveTagMethod(): void
    {
        $contact = new WeChatContact();
        $contact->setTagsArray(['tag1' => 'tag1', 'tag2' => 'tag2']);

        $contact->removeTag('tag1');
        $this->assertNotContains('tag1', $contact->getTagsArray());
        $this->assertContains('tag2', $contact->getTagsArray());
    }

    public function testUpdateLastChatTimeMethod(): void
    {
        $contact = new WeChatContact();
        $beforeUpdate = new \DateTimeImmutable();

        $contact->updateLastChatTime();
        $afterUpdate = new \DateTimeImmutable();

        $lastChatTime = $contact->getLastChatTime();
        $this->assertNotNull($lastChatTime);
        $this->assertGreaterThanOrEqual($beforeUpdate, $lastChatTime);
        $this->assertLessThanOrEqual($afterUpdate, $lastChatTime);
    }

    protected function createEntity(): WeChatContact
    {
        return new WeChatContact();
    }

    /**
     * 提供 WeChatContact 实体的属性数据进行自动测试。
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'nickname' => ['nickname', 'Test Nickname'];
        yield 'remarkName' => ['remarkName', 'Test Remark'];
        yield 'avatar' => ['avatar', 'https://example.com/avatar.jpg'];
        yield 'contactType' => ['contactType', 'stranger'];
        yield 'gender' => ['gender', 'male'];
        yield 'region' => ['region', 'Beijing, China'];
        yield 'signature' => ['signature', 'Test signature'];
        yield 'tags' => ['tags', 'tag1,tag2,tag3'];
        yield 'addFriendTime' => ['addFriendTime', new \DateTimeImmutable('2024-01-01')];
        yield 'lastChatTime' => ['lastChatTime', new \DateTimeImmutable('2024-01-02')];
        // 注意：valid 属性有特殊的 getter 方法，暂时跳过自动测试
        yield 'remark' => ['remark', 'Test remark'];
    }
}
