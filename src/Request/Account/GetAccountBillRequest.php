<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Account;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取账单请求
 *
 * 查询微信API平台账户的账单信息：
 * - 查询消费记录
 * - 查询账单明细
 * - 统计使用情况
 *
 * 接口文档: 社群助手API/账号相关/获取账单.md
 *
 * @author AI Assistant
 */
class GetAccountBillRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly ?string $startDate = null,
        private readonly ?string $endDate = null,
        private readonly int $page = 1,
        private readonly int $limit = 20,
    ) {
    }

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRequestPath(): string
    {
        return 'open/user/getBill';
    }

    public function getRequestOptions(): ?array
    {
        $query = [
            'page' => $this->page,
            'limit' => $this->limit,
        ];

        if (null !== $this->startDate) {
            $query['startDate'] = $this->startDate;
        }

        if (null !== $this->endDate) {
            $query['endDate'] = $this->endDate;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => $query,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
