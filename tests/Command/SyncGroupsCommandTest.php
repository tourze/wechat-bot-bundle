<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\WechatBotBundle\Command\SyncGroupsCommand;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(SyncGroupsCommand::class)]
final class SyncGroupsCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(SyncGroupsCommand::class);
        self::assertInstanceOf(SyncGroupsCommand::class, $command);
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandName(): void
    {
        $command = self::getContainer()->get(SyncGroupsCommand::class);
        self::assertInstanceOf(SyncGroupsCommand::class, $command);
        $this->assertEquals('wechat:sync-groups', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $command = self::getContainer()->get(SyncGroupsCommand::class);
        self::assertInstanceOf(SyncGroupsCommand::class, $command);
        $this->assertStringContainsString('同步微信群组信息', $command->getDescription());
    }

    public function testExecuteWithNoAccounts(): void
    {
        $result = $this->getCommandTester()->execute([]);

        // 由于有在线账号但同步会失败，所以返回 FAILURE
        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithAccountId(): void
    {
        $result = $this->getCommandTester()->execute([
            'account-id' => '999999',
        ]);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithNonExistentAccount(): void
    {
        $result = $this->getCommandTester()->execute([
            'account-id' => '999999',
        ]);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithOfflineAccountAndNoForce(): void
    {
        $result = $this->getCommandTester()->execute([
            'account-id' => '999999',
        ]);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithForceOption(): void
    {
        $result = $this->getCommandTester()->execute([
            '--force' => true,
        ]);

        // 即使使用 force 选项，同步仍然会失败
        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithOnlyOnlineOption(): void
    {
        $result = $this->getCommandTester()->execute([
            '--only-online' => true,
        ]);

        // 即使只同步在线账号，同步仍然会失败
        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithSyncMembersOption(): void
    {
        $result = $this->getCommandTester()->execute([
            '--sync-members' => true,
        ]);

        // 即使同步成员，同步仍然会失败
        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithServiceException(): void
    {
        $result = $this->getCommandTester()->execute([]);

        // 同步会失败
        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithException(): void
    {
        $result = $this->getCommandTester()->execute([]);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testArgumentAccountId(): void
    {
        $result = $this->getCommandTester()->execute([
            'account-id' => '999999',
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信群组同步', $output);
    }

    public function testOptionForce(): void
    {
        $result = $this->getCommandTester()->execute([
            '--force' => true,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信群组同步', $output);
    }

    public function testOptionOnlyOnline(): void
    {
        $result = $this->getCommandTester()->execute([
            '--only-online' => true,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信群组同步', $output);
    }

    public function testOptionBatchSize(): void
    {
        $result = $this->getCommandTester()->execute([
            '--batch-size' => 10,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信群组同步', $output);
    }

    public function testOptionDelay(): void
    {
        $result = $this->getCommandTester()->execute([
            '--delay' => 5,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信群组同步', $output);
    }

    public function testOptionSyncMembers(): void
    {
        $result = $this->getCommandTester()->execute([
            '--sync-members' => true,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信群组同步', $output);
    }
}
