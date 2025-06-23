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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\WechatBotBundle\Entity\WeChatMessage;

/**
 * 微信消息管理CRUD控制器
 *
 * 提供微信消息的完整后台管理功能：
 * - 消息列表查看和搜索
 * - 消息详情展示
 * - 消息状态管理
 * - 消息回复功能
 * - 消息统计分析
 *
 * @author AI Assistant
 */
#[AdminCrud(routePath: '/wechat-bot/message', routeName: 'wechat_bot_message')]
class WeChatMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WeChatMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('微信消息')
            ->setEntityLabelInPlural('微信消息')
            ->setPageTitle('index', '微信消息管理')
            ->setPageTitle('detail', '微信消息详情')
            ->setPageTitle('new', '发送消息')
            ->setPageTitle('edit', '编辑消息')
            ->setHelp('index', '管理所有的微信消息记录，查看收发状态、内容等')
            ->setDefaultSort(['messageTime' => 'DESC'])
            ->setSearchFields(['content', 'senderId', 'senderName', 'receiverId', 'receiverName', 'groupId', 'groupName'])
            ->setEntityPermission('ROLE_ADMIN')
            ->setPaginatorPageSize(50);
    }

    public function configureFields(string $pageName): iterable
    {
        // 基本字段
        yield IdField::new('id', 'ID')->setMaxLength(9999)->hideOnForm();

        yield AssociationField::new('account', '微信账号')
            ->setRequired(true)
            ->setHelp('选择对应的微信账号')
            ->formatValue(function ($value) {
                return $value ? ($value->getNickname() ?: $value->getDeviceId()) : '-';
            });

        yield TextField::new('messageId', '消息ID')
            ->setHelp('微信消息的唯一标识')
            ->hideOnForm();

        yield ChoiceField::new('messageType', '消息类型')
            ->setChoices([
                '文本' => 'text',
                '图片' => 'image',
                '语音' => 'voice',
                '视频' => 'video',
                '文件' => 'file',
                '链接' => 'link',
                '表情' => 'emoji',
                '名片' => 'card',
                '小程序' => 'mini_program',
                'XML' => 'xml',
                '未知' => 'unknown'
            ])
            ->setRequired(true)
            ->formatValue(function ($value) {
                return $this->formatMessageType($value);
            });

        yield ChoiceField::new('direction', '消息方向')
            ->setChoices([
                '接收' => 'inbound',
                '发送' => 'outbound'
            ])
            ->setRequired(true)
            ->formatValue(function ($value) {
                return $this->formatDirection($value);
            });

        // 发送者信息
        yield TextField::new('senderId', '发送者ID')
            ->setHelp('发送者的微信ID')
            ->hideOnIndex();

        yield TextField::new('senderName', '发送者昵称')
            ->setHelp('发送者的昵称');

        // 接收者信息
        yield TextField::new('receiverId', '接收者ID')
            ->setHelp('接收者的微信ID')
            ->hideOnIndex();

        yield TextField::new('receiverName', '接收者昵称')
            ->setHelp('接收者的昵称');

        // 群组信息
        yield TextField::new('groupId', '群组ID')
            ->setHelp('群消息的群组ID')
            ->hideOnIndex();

        yield TextField::new('groupName', '群组名称')
            ->setHelp('群消息的群组名称');

        // 消息内容
        yield TextareaField::new('content', '消息内容')
            ->setHelp('文本消息的具体内容')
            ->formatValue(function ($value) {
                return $value ? mb_substr($value, 0, 200) . (mb_strlen($value) > 200 ? '...' : '') : '-';
            })
            ->hideOnIndex();

        yield TextField::new('displayContent', '内容预览')
            ->hideOnForm()
            ->hideOnDetail()
            ->formatValue(function ($value, $entity) {
                return $entity->getDisplayContent();
            });

        // 媒体文件信息
        yield UrlField::new('mediaUrl', '媒体文件URL')
            ->setHelp('图片、视频、语音等媒体文件的URL')
            ->hideOnIndex()
            ->hideOnForm();

        yield TextField::new('mediaFileName', '文件名')
            ->setHelp('媒体文件的原始文件名')
            ->hideOnIndex();

        yield IntegerField::new('mediaFileSize', '文件大小')
            ->setHelp('媒体文件大小（字节）')
            ->formatValue(function ($value) {
                return $value > 0 ? $this->formatFileSize($value) : '-';
            })
            ->hideOnIndex()
            ->hideOnForm();

        // 时间字段
        yield DateTimeField::new('messageTime', '消息时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setRequired(true)
            ->setHelp('消息发送或接收的时间');

        // 状态字段
        yield BooleanField::new('isRead', '已读')
            ->setHelp('消息是否已读');

        yield BooleanField::new('isReplied', '已回复')
            ->setHelp('消息是否已回复');

        yield BooleanField::new('valid', '是否有效')
            ->setHelp('消息是否有效');

        // 原始数据
        yield TextareaField::new('rawData', '原始数据')
            ->setHelp('消息的原始JSON数据')
            ->hideOnIndex()
            ->hideOnForm();

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

        // 禁用新建操作（消息通过API创建）
        $actions->disable(Action::NEW);

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
            ->add(EntityFilter::new('account', '微信账号'))
            ->add(ChoiceFilter::new('messageType', '消息类型')->setChoices([
                '文本' => 'text',
                '图片' => 'image',
                '语音' => 'voice',
                '视频' => 'video',
                '文件' => 'file',
                '链接' => 'link',
                '表情' => 'emoji',
                '名片' => 'card',
                '小程序' => 'mini_program',
                'XML' => 'xml',
                '未知' => 'unknown'
            ]))
            ->add(ChoiceFilter::new('direction', '消息方向')->setChoices([
                '接收' => 'inbound',
                '发送' => 'outbound'
            ]))
            ->add(TextFilter::new('senderId', '发送者ID'))
            ->add(TextFilter::new('senderName', '发送者昵称'))
            ->add(TextFilter::new('receiverId', '接收者ID'))
            ->add(TextFilter::new('receiverName', '接收者昵称'))
            ->add(TextFilter::new('groupId', '群组ID'))
            ->add(TextFilter::new('groupName', '群组名称'))
            ->add(BooleanFilter::new('isRead', '已读'))
            ->add(BooleanFilter::new('isReplied', '已回复'))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('messageTime', '消息时间'))
            ->add(DateTimeFilter::new('createdTime', '创建时间'));
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->select('entity')
            ->leftJoin('entity.account', 'account')
            ->addSelect('account')
            ->orderBy('entity.messageTime', 'DESC');
    }

    /**
     * 格式化消息类型显示
     */
    private function formatMessageType(?string $type): string
    {
        return match ($type) {
            'text' => '📝 文本',
            'image' => '🖼️ 图片',
            'voice' => '🎤 语音',
            'video' => '🎬 视频',
            'file' => '📁 文件',
            'link' => '🔗 链接',
            'emoji' => '😊 表情',
            'card' => '👤 名片',
            'mini_program' => '📱 小程序',
            'xml' => '📋 XML',
            default => '❓ 未知'
        };
    }

    /**
     * 格式化消息方向显示
     */
    private function formatDirection(?string $direction): string
    {
        return match ($direction) {
            'inbound' => '⬇️ 接收',
            'outbound' => '⬆️ 发送',
            default => '❓ 未知'
        };
    }

    /**
     * 格式化文件大小
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes > 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
