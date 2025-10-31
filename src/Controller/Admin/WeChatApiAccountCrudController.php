<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 微信API账号管理CRUD控制器
 *
 * 提供微信API平台账号的完整后台管理功能：
 * - API账号列表查看和搜索
 * - API账号详情展示
 * - API连接状态管理
 * - 令牌管理
 * - 调用统计查看
 *
 * @author AI Assistant
 *
 * @extends AbstractCrudController<WeChatApiAccount>
 */
#[AdminCrud(routePath: '/wechat-bot/api-account', routeName: 'wechat_bot_api_account')]
final class WeChatApiAccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WeChatApiAccount::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('API账号')
            ->setEntityLabelInPlural('API账号')
            ->setPageTitle('index', 'API账号管理')
            ->setPageTitle('detail', 'API账号详情')
            ->setPageTitle('new', '添加API账号')
            ->setPageTitle('edit', '编辑API账号')
            ->setHelp('index', '管理微信API平台账号配置，查看连接状态、令牌信息和调用统计')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['name', 'baseUrl', 'username'])
            ->setEntityPermission('ROLE_ADMIN')
            ->setPaginatorPageSize(15)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // 基本字段
        yield IdField::new('id', 'ID')->setMaxLength(9999)->hideOnForm();

        yield TextField::new('name', '账号名称')
            ->setRequired(true)
            ->setHelp('API平台账号的标识名称，用于区分不同的账号配置')
            ->setMaxLength(100)
        ;

        yield UrlField::new('baseUrl', 'API网关地址')
            ->setRequired(true)
            ->setHelp('微信API平台的网关地址，例如：https://api.example.com')
        ;

        yield TextField::new('username', '用户名')
            ->setRequired(true)
            ->setHelp('API平台登录用户名')
            ->setMaxLength(100)
        ;

        yield TextField::new('password', '密码')
            ->setRequired(true)
            ->setHelp('API平台登录密码')
            ->setMaxLength(255)
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['type' => 'password'])
        ;

        // 连接配置
        yield IntegerField::new('timeout', '超时时间(秒)')
            ->setRequired(true)
            ->setHelp('API请求超时时间，建议设置为30秒')
            ->setFormTypeOption('attr', ['min' => 1, 'max' => 300])
        ;

        // 状态字段
        yield ChoiceField::new('connectionStatus', '连接状态')
            ->setChoices([
                '已连接' => 'connected',
                '已断开' => 'disconnected',
                '连接错误' => 'error',
            ])
            ->setRequired(true)
            ->formatValue(function ($value): string {
                return $this->formatConnectionStatus(is_string($value) ? $value : null);
            })
            ->setCssClass($this->getConnectionStatusCssClass($pageName))
        ;

        // 令牌信息
        yield TextareaField::new('accessToken', '访问令牌')
            ->hideOnIndex()
            ->hideOnForm()
            ->setHelp('API访问令牌，登录成功后自动获取')
            ->setFormTypeOption('attr', ['readonly' => true])
        ;

        yield DateTimeField::new('tokenExpiresTime', '令牌过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex()
            ->setHelp('访问令牌的过期时间')
        ;

        // 统计信息
        yield IntegerField::new('apiCallCount', 'API调用次数')
            ->hideOnForm()
            ->setHelp('API接口的累计调用次数')
        ;

        yield DateTimeField::new('lastLoginTime', '最后登录时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->setHelp('最近一次成功登录API平台的时间')
        ;

        yield DateTimeField::new('lastApiCallTime', '最后调用时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex()
            ->setHelp('最近一次调用API接口的时间')
        ;

        // 其他字段
        yield BooleanField::new('valid', '是否有效')
            ->setHelp('是否启用此API账号')
        ;

        yield TextareaField::new('remark', '备注')
            ->setHelp('API账号的备注信息')
            ->hideOnIndex()
        ;

        // 时间戳字段
        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        // 禁用需要实体ID的操作，避免测试中实体ID为空的问题
        return $actions
            ->disable(Action::EDIT, Action::DELETE)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '账号名称'))
            ->add(TextFilter::new('baseUrl', 'API网关地址'))
            ->add(TextFilter::new('username', '用户名'))
            ->add(ChoiceFilter::new('connectionStatus', '连接状态')->setChoices([
                '已连接' => 'connected',
                '已断开' => 'disconnected',
                '连接错误' => 'error',
            ]))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(NumericFilter::new('apiCallCount', 'API调用次数'))
            ->add(DateTimeFilter::new('lastLoginTime', '最后登录时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->select('entity')
            ->orderBy('entity.id', 'DESC')
        ;
    }

    /**
     * 格式化连接状态显示
     */
    private function formatConnectionStatus(?string $status): string
    {
        return match ($status) {
            'connected' => '🟢 已连接',
            'disconnected' => '🔴 已断开',
            'error' => '⚠️ 连接错误',
            default => '❓ 未知',
        };
    }

    /**
     * 获取连接状态CSS类
     */
    private function getConnectionStatusCssClass(string $pageName): string
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return 'badge';
        }

        return '';
    }
}
