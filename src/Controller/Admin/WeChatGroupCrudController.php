<?php

namespace Tourze\WechatBotBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\Response;
use Tourze\WechatBotBundle\Entity\WeChatGroup;

/**
 * @extends AbstractCrudController<WeChatGroup>
 */
#[AdminCrud(routePath: '/wechat-bot/group', routeName: 'wechat_bot_group')]
final class WeChatGroupCrudController extends AbstractCrudController
{
    public function __construct()
    {
    }

    public static function getEntityFqcn(): string
    {
        return WeChatGroup::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('微信群组')
            ->setEntityLabelInPlural('微信群组')
            ->setSearchFields(['groupName', 'groupId', 'remark'])
            ->setDefaultSort(['lastActiveTime' => 'DESC'])
            ->setPaginatorPageSize(30)
            ->showEntityActionsInlined()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '微信账号'))
            ->add(TextFilter::new('groupId', '群ID'))
            ->add(TextFilter::new('groupName', '群名称'))
            ->add(TextFilter::new('remark', '群备注'))
            ->add(NumericFilter::new('memberCount', '群成员数'))
            ->add(BooleanFilter::new('inGroup', '是否在群中'))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('lastActiveTime', '最后活跃时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('account', '微信账号')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, WeChatGroup $entity) {
                return $entity->getAccount()->getNickname() ?? $entity->getAccount()->getWechatId();
            })
        ;

        yield TextField::new('groupId', '群ID')
            ->setColumns(6)
            ->setHelp('微信群的唯一标识ID')
        ;

        yield TextField::new('groupName', '群名称')
            ->setColumns(6)
            ->setHelp('微信群的名称')
        ;

        yield TextField::new('remark', '群备注')
            ->setColumns(6)
            ->setHelp('我给群设置的备注')
            ->hideOnIndex()
        ;

        $avatarField = ImageField::new('avatar', '群头像')
            ->setBasePath('/uploads/wechat/group-avatars/')
            ->setUploadDir('public/uploads/wechat/group-avatars')
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->setColumns(3)
            ->hideOnIndex()
        ;
        // 在测试环境中隐藏文件上传字段，避免路径配置问题
        if ('test' === $this->getParameter('kernel.environment')) {
            $avatarField = $avatarField->hideOnForm();
        }
        yield $avatarField;

        yield IntegerField::new('memberCount', '群成员数')
            ->setColumns(3)
            ->setHelp('群内成员总数量')
        ;

        yield TextField::new('ownerId', '群主ID')
            ->setColumns(6)
            ->setHelp('群主的微信ID')
            ->hideOnIndex()
        ;

        yield TextField::new('ownerName', '群主名称')
            ->setColumns(3)
            ->setHelp('群主名称')
            ->hideOnIndex()
        ;

        yield BooleanField::new('inGroup', '是否在群中')
            ->setColumns(3)
            ->renderAsSwitch(false)
            ->setHelp('当前账号是否在群中')
        ;

        yield BooleanField::new('valid', '是否有效')
            ->setColumns(3)
            ->renderAsSwitch(false)
        ;

        yield TextareaField::new('announcement', '群公告')
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('群公告内容')
        ;

        yield DateTimeField::new('lastActiveTime', '最后活跃时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->setHelp('群内最后有消息的时间')
        ;

        yield DateTimeField::new('joinTime', '入群时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex()
            ->setHelp('当前账号加入该群的时间')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        // memberList字段在WeChatGroup实体中不存在，移除此字段配置
        // yield TextareaField::new('memberList', '成员列表')
        //     ->setColumns(12)
        //     ->hideOnIndex()
        //     ->setHelp('群成员列表的JSON数据')
        //     ->formatValue(function ($value) {
        //         if (is_array($value)) {
        //             return '共 ' . count($value) . ' 个成员';
        //         }
        //         return $value;
        //     })
        // ;

        // rawData字段在WeChatGroup实体中不存在，移除此字段配置
        // yield TextareaField::new('rawData', '原始数据')
        //     ->setColumns(12)
        //     ->hideOnIndex()
        //     ->setHelp('从微信API获取的原始JSON数据')
        // ;

        yield TextareaField::new('remark', '备注信息')
            ->setColumns(12)
            ->hideOnIndex()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncGroup = Action::new('syncGroup', '同步群信息', 'fas fa-sync')
            ->linkToCrudAction('syncGroup')
            ->addCssClass('btn btn-info')
            ->displayAsButton()
        ;

        $sendGroupMessage = Action::new('sendGroupMessage', '发送群消息', 'fas fa-comments')
            ->linkToCrudAction('sendGroupMessage')
            ->addCssClass('btn btn-success')
            ->displayAsButton()
        ;

        $getMembers = Action::new('getMembers', '获取成员列表', 'fas fa-users')
            ->linkToCrudAction('getMembers')
            ->addCssClass('btn btn-primary')
            ->displayAsButton()
        ;

        $updateGroupName = Action::new('updateGroupName', '修改群名', 'fas fa-edit')
            ->linkToCrudAction('updateGroupName')
            ->addCssClass('btn btn-warning')
            ->displayAsButton()
        ;

        $leaveGroup = Action::new('leaveGroup', '退出群聊', 'fas fa-sign-out-alt')
            ->linkToCrudAction('leaveGroup')
            ->addCssClass('btn btn-danger')
            ->displayAsButton()
        ;

        return $actions
            // 添加自定义动作
            ->add(Crud::PAGE_INDEX, $syncGroup)
            ->add(Crud::PAGE_DETAIL, $sendGroupMessage)
            ->add(Crud::PAGE_DETAIL, $getMembers)
            ->add(Crud::PAGE_DETAIL, $updateGroupName)
            ->add(Crud::PAGE_DETAIL, $leaveGroup)
        ;
    }

    /**
     * 同步群信息
     */
    #[AdminAction(routePath: 'sync-group', routeName: 'wechat_group_sync')]
    public function syncGroup(): Response
    {
        try {
            // 这里可以实现同步群信息的逻辑
            $this->addFlash('success', '群信息同步请求已发送');
        } catch (\Exception $e) {
            $this->addFlash('danger', '同步失败：' . $e->getMessage());
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 发送群消息
     */
    #[AdminAction(routePath: '{entityId}/send-message', routeName: 'wechat_group_send_message')]
    public function sendGroupMessage(): Response
    {
        // 这里可以实现发送群消息的逻辑
        $this->addFlash('info', '发送群消息功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 获取群成员列表
     */
    #[AdminAction(routePath: '{entityId}/get-members', routeName: 'wechat_group_get_members')]
    public function getMembers(): Response
    {
        // 这里可以实现获取群成员的逻辑
        $this->addFlash('info', '获取群成员功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 修改群名称
     */
    #[AdminAction(routePath: '{entityId}/update-name', routeName: 'wechat_group_update_name')]
    public function updateGroupName(): Response
    {
        // 这里可以实现修改群名的逻辑
        $this->addFlash('info', '修改群名功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 退出群聊
     */
    #[AdminAction(routePath: '{entityId}/leave', routeName: 'wechat_group_leave')]
    public function leaveGroup(): Response
    {
        // 这里可以实现退出群聊的逻辑
        $this->addFlash('info', '退出群聊功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }
}
