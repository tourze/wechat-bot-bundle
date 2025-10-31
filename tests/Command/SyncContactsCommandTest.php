<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\WechatBotBundle\Command\SyncContactsCommand;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(SyncContactsCommand::class)]
final class SyncContactsCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(SyncContactsCommand::class);
        self::assertInstanceOf(SyncContactsCommand::class, $command);
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandName(): void
    {
        $command = self::getContainer()->get(SyncContactsCommand::class);
        self::assertInstanceOf(SyncContactsCommand::class, $command);
        $this->assertEquals('wechat:sync-contacts', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $command = self::getContainer()->get(SyncContactsCommand::class);
        self::assertInstanceOf(SyncContactsCommand::class, $command);
        $this->assertStringContainsString('同步微信联系人信息', $command->getDescription());
    }

    public function testExecuteWithNoAccounts(): void
    {
        $result = $this->getCommandTester()->execute([]);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithAccountId(): void
    {
        $result = $this->getCommandTester()->execute([
            'account-id' => '999999', // 不存在的账号ID
        ]);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithOnlyOnlineOption(): void
    {
        $result = $this->getCommandTester()->execute([
            '--only-online' => true,
        ]);

        // 由于有在线账号但同步会失败，所以返回 FAILURE
        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithForceOption(): void
    {
        $result = $this->getCommandTester()->execute([
            '--force' => true,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testExecuteWithAllOptions(): void
    {
        $result = $this->getCommandTester()->execute([
            'account-id' => '999999',
            '--only-online' => true,
            '--force' => true,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testArgumentAccountId(): void
    {
        $result = $this->getCommandTester()->execute([
            'account-id' => '999999',
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信联系人同步', $output);
    }

    public function testOptionForce(): void
    {
        $result = $this->getCommandTester()->execute([
            '--force' => true,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信联系人同步', $output);
    }

    public function testOptionOnlyOnline(): void
    {
        $result = $this->getCommandTester()->execute([
            '--only-online' => true,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信联系人同步', $output);
    }

    public function testOptionBatchSize(): void
    {
        $result = $this->getCommandTester()->execute([
            '--batch-size' => 10,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信联系人同步', $output);
    }

    public function testOptionDelay(): void
    {
        $result = $this->getCommandTester()->execute([
            '--delay' => 5,
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信联系人同步', $output);
    }
}
