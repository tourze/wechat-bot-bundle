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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\WechatBotBundle\Entity\WeChatAccount;

/**
 * 微信账号管理CRUD控制器
 *
 * 提供微信账号的完整后台管理功能：
 * - 账号列表查看和搜索
 * - 账号详情展示
 * - 账号状态管理
 * - 登录信息查看
 * - 设备管理
 *
 * @author AI Assistant
 */
#[AdminCrud(routePath: '/wechat-bot/account', routeName: 'wechat_bot_account')]
class WeChatAccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WeChatAccount::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('微信账号')
            ->setEntityLabelInPlural('微信账号')
            ->setPageTitle('index', '微信账号管理')
            ->setPageTitle('detail', '微信账号详情')
            ->setPageTitle('new', '添加微信账号')
            ->setPageTitle('edit', '编辑微信账号')
            ->setHelp('index', '管理所有的微信机器人账号，查看登录状态、设备信息等')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['deviceId', 'wechatId', 'nickname', 'remark'])
            ->setEntityPermission('ROLE_ADMIN')
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        // 基本字段
        yield IdField::new('id', 'ID')->setMaxLength(9999)->hideOnForm();

        yield AssociationField::new('apiAccount', 'API账号')
            ->setRequired(true)
            ->setHelp('选择对应的API平台账号')
            ->formatValue(function ($value) {
                return $value ? $value->getName() : '-';
            });

        yield TextField::new('deviceId', '设备ID')
            ->setRequired(true)
            ->setHelp('微信API使用的设备标识，创建后不可修改')
            ->setFormTypeOption('attr', ['readonly' => $pageName === Crud::PAGE_EDIT]);

        yield TextField::new('wechatId', '微信号')
            ->setHelp('登录成功后自动获取的微信号')
            ->hideOnForm();

        yield TextField::new('nickname', '昵称')
            ->setHelp('登录成功后自动获取的微信昵称')
            ->hideOnForm();

        yield ImageField::new('avatar', '头像')
            ->setBasePath('/')
            ->setHelp('登录成功后自动获取的头像URL')
            ->hideOnForm()
            ->hideOnIndex();

        // 状态字段
        yield ChoiceField::new('status', '状态')
            ->setChoices([
                '等待登录' => 'pending_login',
                '在线' => 'online',
                '离线' => 'offline',
                '已过期' => 'expired'
            ])
            ->setRequired(true)
            ->formatValue(function ($value) {
                return $this->formatStatus($value);
            })
            ->setCssClass($this->getStatusCssClass($pageName));

        // 登录信息字段
        yield TextareaField::new('qrCode', '二维码数据')
            ->hideOnIndex()
            ->hideOnForm()
            ->setHelp('登录用的二维码原始数据');

        yield UrlField::new('qrCodeUrl', '二维码图片')
            ->hideOnIndex()
            ->hideOnForm()
            ->setHelp('登录用的二维码图片地址');

        // 网络配置
        yield TextField::new('proxy', '代理设置')
            ->setHelp('网络代理配置，格式：host:port')
            ->hideOnIndex();

        // 时间字段
        yield DateTimeField::new('lastLoginTime', '最后登录时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->setHelp('最近一次成功登录的时间');

        yield DateTimeField::new('lastActiveTime', '最后活跃时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->setHelp('最近一次活跃的时间');

        // 其他字段
        yield BooleanField::new('valid', '是否有效')
            ->setHelp('是否启用此账号');

        yield TextareaField::new('remark', '备注')
            ->setHelp('账号的备注信息')
            ->hideOnIndex();

        // 时间戳字段
        yield DateTimeField::new('createdTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex();

        yield DateTimeField::new('updatedTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        // 添加详情操作
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        // 重新排序操作按钮
        $actions->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);

        // 自定义操作按钮样式
        $actions->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
            return $action->setIcon('fa fa-eye')->setLabel('查看');
        });

        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            return $action->setIcon('fa fa-edit')->setLabel('编辑');
        });

        $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
            return $action->setIcon('fa fa-trash')->setLabel('删除');
        });

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('apiAccount', 'API账号'))
            ->add(TextFilter::new('deviceId', '设备ID'))
            ->add(TextFilter::new('wechatId', '微信号'))
            ->add(TextFilter::new('nickname', '昵称'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices([
                '等待登录' => 'pending_login',
                '在线' => 'online',
                '离线' => 'offline',
                '已过期' => 'expired'
            ]))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('lastLoginTime', '最后登录时间'))
            ->add(DateTimeFilter::new('lastActiveTime', '最后活跃时间'))
            ->add(DateTimeFilter::new('createdTime', '创建时间'));
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->select('entity')
            ->leftJoin('entity.apiAccount', 'apiAccount')
            ->addSelect('apiAccount')
            ->orderBy('entity.id', 'DESC');
    }

    /**
     * 格式化状态显示
     */
    private function formatStatus(?string $status): string
    {
        return match ($status) {
            'pending_login' => '⏳ 等待登录',
            'online' => '🟢 在线',
            'offline' => '🔴 离线',
            'expired' => '⚠️ 已过期',
            default => '❓ 未知'
        };
    }

    /**
     * 获取状态CSS类
     */
    private function getStatusCssClass(string $pageName): string
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return 'badge';
        }
        return '';
    }
}
