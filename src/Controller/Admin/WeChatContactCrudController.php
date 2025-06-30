<?php

namespace Tourze\WechatBotBundle\Controller\Admin;

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

class WeChatContactCrudController extends AbstractCrudController
{
    public function __construct() {}

    public static function getEntityFqcn(): string
    {
        return WeChatContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('微信联系人')
            ->setEntityLabelInPlural('微信联系人')
            ->setSearchFields(['nickname', 'wxid', 'remark', 'alias'])
            ->setDefaultSort(['lastContactTime' => 'DESC'])
            ->setPaginatorPageSize(50)
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '微信账号'))
            ->add(TextFilter::new('wxid', '微信ID'))
            ->add(TextFilter::new('nickname', '昵称'))
            ->add(TextFilter::new('remark', '备注'))
            ->add(ChoiceFilter::new('contactType', '联系人类型')->setChoices([
                '普通用户' => 'user',
                '群聊' => 'group',
                '公众号' => 'official',
                '企业微信' => 'enterprise'
            ]))
            ->add(ChoiceFilter::new('gender', '性别')->setChoices([
                '未知' => 0,
                '男' => 1,
                '女' => 2
            ]))
            ->add(BooleanFilter::new('isFriend', '是否为好友'))
            ->add(BooleanFilter::new('isBlocked', '是否被拉黑'))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('lastContactTime', '最后联系时间'))
            ->add(DateTimeFilter::new('createdTime', '创建时间'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('account', '微信账号')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, WeChatContact $entity) {
                return $entity->getAccount()->getNickname() ?? $entity->getAccount()->getWechatId();
            });

        yield TextField::new('wxid', '微信ID')
            ->setColumns(6)
            ->setHelp('联系人的微信ID');

        yield TextField::new('nickname', '昵称')
            ->setColumns(6)
            ->setHelp('联系人的昵称');

        yield TextField::new('remark', '备注')
            ->setColumns(6)
            ->setHelp('我给联系人设置的备注')
            ->hideOnIndex();

        yield TextField::new('alias', '微信号')
            ->setColumns(6)
            ->setHelp('联系人的微信号')
            ->hideOnIndex();

        yield ImageField::new('avatar', '头像')
            ->setBasePath('/uploads/wechat/avatars/')
            ->setUploadDir('public/uploads/wechat/avatars')
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->setColumns(3)
            ->hideOnIndex();

        yield ChoiceField::new('contactType', '联系人类型')
            ->setChoices([
                '普通用户' => 'user',
                '群聊' => 'group',
                '公众号' => 'official',
                '企业微信' => 'enterprise'
            ])
            ->setColumns(3)
            ->renderAsBadges([
                'user' => 'success',
                'group' => 'info',
                'official' => 'warning',
                'enterprise' => 'primary'
            ]);

        yield ChoiceField::new('gender', '性别')
            ->setChoices([
                '未知' => 0,
                '男' => 1,
                '女' => 2
            ])
            ->setColumns(3)
            ->renderAsBadges([
                0 => 'secondary',
                1 => 'primary',
                2 => 'danger'
            ])
            ->hideOnIndex();

        yield TextField::new('country', '国家')
            ->setColumns(4)
            ->hideOnIndex();

        yield TextField::new('province', '省份')
            ->setColumns(4)
            ->hideOnIndex();

        yield TextField::new('city', '城市')
            ->setColumns(4)
            ->hideOnIndex();

        yield TextField::new('signature', '个性签名')
            ->setColumns(12)
            ->hideOnIndex();

        yield BooleanField::new('isFriend', '是否为好友')
            ->setColumns(3)
            ->renderAsSwitch(false);

        yield BooleanField::new('isBlocked', '是否被拉黑')
            ->setColumns(3)
            ->renderAsSwitch(false);

        yield BooleanField::new('valid', '是否有效')
            ->setColumns(3)
            ->renderAsSwitch(false);

        yield DateTimeField::new('lastContactTime', '最后联系时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm();

        yield DateTimeField::new('createdTime', '创建时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm();

        yield DateTimeField::new('updatedTime', '更新时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex();

        yield TextareaField::new('rawData', '原始数据')
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('从微信API获取的原始JSON数据');

        yield TextareaField::new('remark', '备注信息')
            ->setColumns(12)
            ->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncContact = Action::new('syncContact', '同步联系人', 'fas fa-sync')
            ->linkToCrudAction('syncContact')
            ->addCssClass('btn btn-info')
            ->displayAsButton();

        $sendMessage = Action::new('sendMessage', '发送消息', 'fas fa-comment')
            ->linkToCrudAction('sendMessage')
            ->addCssClass('btn btn-success')
            ->displayAsButton();

        $addFriend = Action::new('addFriend', '添加好友', 'fas fa-user-plus')
            ->linkToCrudAction('addFriend')
            ->addCssClass('btn btn-primary')
            ->displayAsButton()
            ->displayIf(function (WeChatContact $contact) {
                return !$contact->isFriend();
            });

        $deleteFriend = Action::new('deleteFriend', '删除好友', 'fas fa-user-minus')
            ->linkToCrudAction('deleteFriend')
            ->addCssClass('btn btn-danger')
            ->displayAsButton()
            ->displayIf(function (WeChatContact $contact) {
                return $contact->isFriend();
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $syncContact)
            ->add(Crud::PAGE_DETAIL, $sendMessage)
            ->add(Crud::PAGE_DETAIL, $addFriend)
            ->add(Crud::PAGE_DETAIL, $deleteFriend)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-plus')->setLabel('添加联系人');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fas fa-edit')->setLabel('编辑');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fas fa-trash')->setLabel('删除');
            });
    }

    /**
     * 同步联系人信息
     */
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
            'crudControllerFqcn' => static::class,
        ]);
    }

    /**
     * 发送消息给联系人
     */
    public function sendMessage(): Response
    {
        // 这里可以实现发送消息的逻辑
        $this->addFlash('info', '发送消息功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => static::class,
        ]);
    }

    /**
     * 添加好友
     */
    public function addFriend(): Response
    {
        // 这里可以实现添加好友的逻辑
        $this->addFlash('info', '添加好友功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => static::class,
        ]);
    }

    /**
     * 删除好友
     */
    public function deleteFriend(): Response
    {
        // 这里可以实现删除好友的逻辑
        $this->addFlash('info', '删除好友功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => static::class,
        ]);
    }
}
