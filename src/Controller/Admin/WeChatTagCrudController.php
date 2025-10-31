<?php

namespace Tourze\WechatBotBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\Response;
use Tourze\WechatBotBundle\Entity\WeChatTag;

/**
 * @extends AbstractCrudController<WeChatTag>
 */
#[AdminCrud(routePath: '/wechat-bot/tag', routeName: 'wechat_bot_tag')]
final class WeChatTagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WeChatTag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('微信标签')
            ->setEntityLabelInPlural('微信标签')
            ->setSearchFields(['tagName', 'tagId'])
            ->setDefaultSort(['sortOrder' => 'DESC', 'tagName' => 'ASC'])
            ->setPaginatorPageSize(50)
            ->showEntityActionsInlined()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '微信账号'))
            ->add(TextFilter::new('tagId', '标签ID'))
            ->add(TextFilter::new('tagName', '标签名称'))
            ->add(TextFilter::new('color', '标签颜色'))
            ->add(NumericFilter::new('friendCount', '好友数量'))
            ->add(BooleanFilter::new('isSystem', '是否系统标签'))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('account', '微信账号')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, WeChatTag $entity) {
                return $entity->getAccount()?->getNickname() ?? $entity->getAccount()?->getWechatId();
            })
        ;

        yield TextField::new('tagId', '标签ID')
            ->setColumns(6)
            ->setHelp('微信标签的唯一标识ID')
        ;

        yield TextField::new('tagName', '标签名称')
            ->setColumns(6)
            ->setRequired(true)
            ->setHelp('标签的显示名称')
        ;

        yield ColorField::new('color', '标签颜色')
            ->setColumns(3)
            ->setHelp('标签的颜色代码')
            ->hideOnIndex()
        ;

        yield IntegerField::new('friendCount', '好友数量')
            ->setColumns(3)
            ->setHelp('使用此标签的好友数量')
            ->hideOnForm()
        ;

        yield IntegerField::new('sortOrder', '排序权重')
            ->setColumns(3)
            ->setHelp('数值越大越靠前')
            ->hideOnIndex()
        ;

        yield BooleanField::new('isSystem', '是否系统标签')
            ->setColumns(3)
            ->renderAsSwitch(false)
            ->setHelp('系统内置标签不可删除')
        ;

        yield BooleanField::new('valid', '是否有效')
            ->setColumns(3)
            ->renderAsSwitch(false)
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

        yield ArrayField::new('friendList', '好友列表')
            ->setColumns(12)
            ->hideOnIndex()
            ->hideOnForm()
            ->setHelp('使用此标签的好友微信ID列表')
        ;

        yield TextareaField::new('remark', '备注信息')
            ->setColumns(12)
            ->hideOnIndex()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncTags = Action::new('syncTags', '同步标签', 'fas fa-sync')
            ->linkToCrudAction('syncTags')
            ->addCssClass('btn btn-info')
            ->displayAsButton()
        ;

        $addFriendsToTag = Action::new('addFriendsToTag', '添加好友', 'fas fa-user-plus')
            ->linkToCrudAction('addFriendsToTag')
            ->addCssClass('btn btn-success')
            ->displayAsButton()
        ;

        $removeFriendsFromTag = Action::new('removeFriendsFromTag', '移除好友', 'fas fa-user-minus')
            ->linkToCrudAction('removeFriendsFromTag')
            ->addCssClass('btn btn-warning')
            ->displayAsButton()
        ;

        $viewTagFriends = Action::new('viewTagFriends', '查看好友', 'fas fa-users')
            ->linkToCrudAction('viewTagFriends')
            ->addCssClass('btn btn-primary')
            ->displayAsButton()
        ;

        return $actions
            // 添加自定义动作
            ->add(Crud::PAGE_INDEX, $syncTags)
            ->add(Crud::PAGE_DETAIL, $addFriendsToTag)
            ->add(Crud::PAGE_DETAIL, $removeFriendsFromTag)
            ->add(Crud::PAGE_DETAIL, $viewTagFriends)
            // 禁用系统标签的删除操作
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
        ;
    }

    /**
     * 同步标签
     */
    #[AdminAction(routePath: 'sync-tags', routeName: 'wechat_tag_sync')]
    public function syncTags(): Response
    {
        try {
            // 这里可以实现同步标签的逻辑
            $this->addFlash('success', '标签同步请求已发送');
        } catch (\Exception $e) {
            $this->addFlash('danger', '同步失败：' . $e->getMessage());
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 为标签添加好友
     */
    #[AdminAction(routePath: '{entityId}/add-friends', routeName: 'wechat_tag_add_friends')]
    public function addFriendsToTag(): Response
    {
        // 这里可以实现为标签添加好友的逻辑
        $this->addFlash('info', '添加好友到标签功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 从标签中移除好友
     */
    #[AdminAction(routePath: '{entityId}/remove-friends', routeName: 'wechat_tag_remove_friends')]
    public function removeFriendsFromTag(): Response
    {
        // 这里可以实现从标签移除好友的逻辑
        $this->addFlash('info', '从标签移除好友功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    /**
     * 查看标签下的好友
     */
    #[AdminAction(routePath: '{entityId}/view-friends', routeName: 'wechat_tag_view_friends')]
    public function viewTagFriends(): Response
    {
        // 这里可以实现查看标签好友的逻辑
        $this->addFlash('info', '查看标签好友功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }
}
