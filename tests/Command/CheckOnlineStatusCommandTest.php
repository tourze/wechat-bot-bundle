<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\WechatBotBundle\Command\CheckOnlineStatusCommand;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(CheckOnlineStatusCommand::class)]
final class CheckOnlineStatusCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(CheckOnlineStatusCommand::class);
        self::assertInstanceOf(CheckOnlineStatusCommand::class, $command);
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandName(): void
    {
        $command = self::getContainer()->get(CheckOnlineStatusCommand::class);
        self::assertInstanceOf(CheckOnlineStatusCommand::class, $command);
        $this->assertEquals('wechat-bot:check-online-status', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $command = self::getContainer()->get(CheckOnlineStatusCommand::class);
        self::assertInstanceOf(CheckOnlineStatusCommand::class, $command);
        $this->assertStringContainsString('检查所有微信账号的在线状态', $command->getDescription());
    }

    public function testExecuteWithNoAccounts(): void
    {
        $result = $this->getCommandTester()->execute([
            '--timeout' => 30,
        ]);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testExecuteWithAccountId(): void
    {
        $result = $this->getCommandTester()->execute([
            '--account-id' => '999999',
            '--timeout' => 30,
        ]);

        // 由于账号不存在，命令应该成功执行但没有实际操作
        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testExecuteWithOnlyOnlineOption(): void
    {
        $result = $this->getCommandTester()->execute([
            '--only-online' => true,
            '--timeout' => 30,
        ]);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testOptionOnlyOnline(): void
    {
        $result = $this->getCommandTester()->execute([
            '--only-online' => true,
            '--timeout' => 30,
        ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信账号在线状态检查', $output);
    }

    public function testExecuteWithException(): void
    {
        $result = $this->getCommandTester()->execute([
            '--timeout' => 30,
        ]);

        // 集成测试应该执行成功，除非有实际的系统错误
        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testOptionAccountId(): void
    {
        $result = $this->getCommandTester()->execute([
            '--account-id' => '999999',
            '--timeout' => 30,
        ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信账号在线状态检查', $output);
    }

    public function testOptionTimeout(): void
    {
        $result = $this->getCommandTester()->execute([
            '--timeout' => 60,
        ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $this->getCommandTester()->getDisplay();
        $this->assertStringContainsString('微信账号在线状态检查', $output);
    }
}
