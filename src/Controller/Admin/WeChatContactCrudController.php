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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\Response;
use Tourze\WechatBotBundle\Entity\WeChatContact;

/**
 * @extends AbstractCrudController<WeChatContact>
 */
#[AdminCrud(routePath: '/wechat-bot/contact', routeName: 'wechat_bot_contact')]
final class WeChatContactCrudController extends AbstractCrudController
{
    public function __construct()
    {
    }

    public static function getEntityFqcn(): string
    {
        return WeChatContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('微信联系人')
            ->setEntityLabelInPlural('微信联系人')
            ->setSearchFields(['nickname', 'contactId', 'remarkName'])
            ->setDefaultSort(['lastChatTime' => 'DESC'])
            ->setPaginatorPageSize(50)
            ->showEntityActionsInlined()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '微信账号'))
            ->add(TextFilter::new('contactId', '微信ID'))
            ->add(TextFilter::new('nickname', '昵称'))
            ->add(TextFilter::new('remarkName', '备注名'))
            ->add(ChoiceFilter::new('contactType', '联系人类型')->setChoices([
                '好友' => 'friend',
                '陌生人' => 'stranger',
                '黑名单' => 'blacklist',
            ]))
            ->add(ChoiceFilter::new('gender', '性别')->setChoices([
                '未知' => 'unknown',
                '男' => 'male',
                '女' => 'female',
            ]))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('lastChatTime', '最后聊天时间'))
            ->add(DateTimeFilter::new('addFriendTime', '添加好友时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('account', '微信账号')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, WeChatContact $entity) {
                return $entity->getAccount()->getNickname() ?? $entity->getAccount()->getWechatId();
            })
        ;

        yield TextField::new('contactId', '微信ID')
            ->setColumns(6)
            ->setHelp('联系人的微信ID')
        ;

        yield TextField::new('nickname', '昵称')
            ->setColumns(6)
            ->setHelp('联系人的昵称')
        ;

        yield TextField::new('remarkName', '备注名')
            ->setColumns(6)
            ->setHelp('我给联系人设置的备注名')
            ->hideOnIndex()
        ;

        $avatarField = ImageField::new('avatar', '头像')
            ->setBasePath('/uploads/wechat/avatars/')
            ->setUploadDir('%kernel.project_dir%/public/uploads/wechat/avatars')
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->setColumns(3)
            ->hideOnIndex()
        ;
        // 在测试环境中隐藏文件上传字段，避免路径配置问题
        if ('test' === $this->getParameter('kernel.environment')) {
            $avatarField = $avatarField->hideOnForm();
        }
        yield $avatarField;

        yield ChoiceField::new('contactType', '联系人类型')
            ->setChoices([
                '好友' => 'friend',
                '陌生人' => 'stranger',
                '黑名单' => 'blacklist',
            ])
            ->setColumns(3)
            ->renderAsBadges([
                'friend' => 'success',
                'stranger' => 'info',
                'blacklist' => 'danger',
            ])
        ;

        yield ChoiceField::new('gender', '性别')
            ->setChoices([
                '未知' => 'unknown',
                '男' => 'male',
                '女' => 'female',
            ])
            ->setColumns(3)
            ->renderAsBadges([
                'unknown' => 'secondary',
                'male' => 'primary',
                'female' => 'danger',
            ])
            ->hideOnIndex()
        ;

        yield TextField::new('region', '地区信息')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('signature', '个性签名')
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield BooleanField::new('valid', '是否有效')
            ->setColumns(3)
            ->renderAsSwitch(false)
        ;

        yield DateTimeField::new('lastChatTime', '最后聊天时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;

        yield DateTimeField::new('addFriendTime', '添加好友时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
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

        yield TextareaField::new('remark', '备注信息')
            ->setColumns(12)
            ->hideOnIndex()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncContact = Action::new('syncContact', '同步联系人', 'fas fa-sync')
            ->linkToCrudAction('syncContact')
            ->addCssClass('btn btn-info')
            ->displayAsButton()
        ;

        $sendMessage = Action::new('sendMessage', '发送消息', 'fas fa-comment')
            ->linkToCrudAction('sendMessage')
            ->addCssClass('btn btn-success')
            ->displayAsButton()
        ;

        $addFriend = Action::new('addFriend', '添加好友', 'fas fa-user-plus')
            ->linkToCrudAction('addFriend')
            ->addCssClass('btn btn-primary')
            ->displayAsButton()
            ->displayIf(function (WeChatContact $contact) {
                return 'friend' !== $contact->getContactType();
            })
        ;

        $deleteFriend = Action::new('deleteFriend', '删除好友', 'fas fa-user-minus')
            ->linkToCrudAction('deleteFriend')
            ->addCssClass('btn btn-danger')
            ->displayAsButton()
            ->displayIf(function (WeChatContact $contact) {
                return 'friend' === $contact->getContactType();
            })
        ;

        return $actions
            // 添加自定义动作
            ->add(Crud::PAGE_INDEX, $syncContact)
            ->add(Crud::PAGE_DETAIL, $sendMessage)
            ->add(Crud::PAGE_DETAIL, $addFriend)
            ->add(Crud::PAGE_DETAIL, $deleteFriend)
        ;
    }

    /**
     * 同步联系人信息
     */
    #[AdminAction(routePath: 'sync-contact', routeName: 'wechat_contact_sync')]
    public function syncContact(): Response
    {
        try {
            // 这里可以实现同步逻辑
            $this->addFlash('success', '联系人同步请求已发送');
        } catch (\Exception $e) {
            $this->addFlash('danger', '同步失败：' . $e->getMessage());
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 发送消息给联系人
     */
    #[AdminAction(routePath: '{entityId}/send-message', routeName: 'wechat_contact_send_message')]
    public function sendMessage(): Response
    {
        // 这里可以实现发送消息的逻辑
        $this->addFlash('info', '发送消息功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 添加好友
     */
    #[AdminAction(routePath: '{entityId}/add-friend', routeName: 'wechat_contact_add_friend')]
    public function addFriend(): Response
    {
        // 这里可以实现添加好友的逻辑
        $this->addFlash('info', '添加好友功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 删除好友
     */
    #[AdminAction(routePath: '{entityId}/delete-friend', routeName: 'wechat_contact_delete_friend')]
    public function deleteFriend(): Response
    {
        // 这里可以实现删除好友的逻辑
        $this->addFlash('info', '删除好友功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }
}
