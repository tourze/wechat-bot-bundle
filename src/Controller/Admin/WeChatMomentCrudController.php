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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\Response;
use Tourze\WechatBotBundle\Entity\WeChatMoment;

class WeChatMomentCrudController extends AbstractCrudController
{
    public function __construct() {}

    public static function getEntityFqcn(): string
    {
        return WeChatMoment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('朋友圈动态')
            ->setEntityLabelInPlural('朋友圈动态')
            ->setSearchFields(['textContent', 'authorNickname', 'authorWxid'])
            ->setDefaultSort(['publishTime' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '微信账号'))
            ->add(TextFilter::new('momentId', '动态ID'))
            ->add(TextFilter::new('authorWxid', '发布者微信ID'))
            ->add(TextFilter::new('authorNickname', '发布者昵称'))
            ->add(ChoiceFilter::new('momentType', '动态类型')->setChoices([
                '文本' => 'text',
                '图片' => 'image',
                '视频' => 'video',
                '链接' => 'link'
            ]))
            ->add(BooleanFilter::new('isLiked', '是否已点赞'))
            ->add(NumericFilter::new('likeCount', '点赞数'))
            ->add(NumericFilter::new('commentCount', '评论数'))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('publishTime', '发布时间'))
            ->add(DateTimeFilter::new('createdTime', '创建时间'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('account', '微信账号')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, WeChatMoment $entity) {
                return $entity->getAccount()->getNickname() ?: $entity->getAccount()->getWechatId();
            });

        yield TextField::new('momentId', '动态ID')
            ->setColumns(6)
            ->setHelp('朋友圈动态的唯一标识');

        yield TextField::new('authorWxid', '发布者微信ID')
            ->setColumns(6)
            ->setHelp('发布者的微信ID');

        yield TextField::new('authorNickname', '发布者昵称')
            ->setColumns(6)
            ->setHelp('发布者的昵称');

        yield TextField::new('authorAvatar', '发布者头像')
            ->setColumns(6)
            ->setHelp('发布者头像URL')
            ->hideOnIndex();

        yield ChoiceField::new('momentType', '动态类型')
            ->setChoices([
                '文本' => 'text',
                '图片' => 'image',
                '视频' => 'video',
                '链接' => 'link'
            ])
            ->setColumns(3)
            ->renderAsBadges([
                'text' => 'primary',
                'image' => 'success',
                'video' => 'info',
                'link' => 'warning'
            ]);

        yield TextareaField::new('textContent', '文本内容')
            ->setColumns(12)
            ->setMaxLength(500)
            ->setHelp('朋友圈的文本内容')
            ->formatValue(function ($value) {
                return $value ? mb_substr($value, 0, 100) . (mb_strlen($value) > 100 ? '...' : '') : '';
            });

        yield TextareaField::new('images', '图片列表')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('图片URL列表的JSON数据')
            ->formatValue(function ($value) {
                if (is_array($value)) {
                    return '共 ' . count($value) . ' 张图片';
                }
                return $value;
            });

        yield TextareaField::new('video', '视频信息')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('视频信息的JSON数据')
            ->formatValue(function ($value) {
                if (is_array($value)) {
                    return '视频: ' . ($value['title'] ?? '无标题');
                }
                return $value;
            });

        yield TextareaField::new('link', '链接信息')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('链接信息的JSON数据')
            ->formatValue(function ($value) {
                if (is_array($value)) {
                    return '链接: ' . ($value['title'] ?? '无标题');
                }
                return $value;
            });

        yield TextField::new('location', '位置信息')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('发布位置信息');

        yield IntegerField::new('likeCount', '点赞数')
            ->setColumns(3);

        yield IntegerField::new('commentCount', '评论数')
            ->setColumns(3);

        yield BooleanField::new('isLiked', '是否已点赞')
            ->setColumns(3)
            ->renderAsSwitch(false);

        yield BooleanField::new('valid', '是否有效')
            ->setColumns(3)
            ->renderAsSwitch(false);

        yield DateTimeField::new('publishTime', '发布时间')
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

        yield TextareaField::new('likeUsers', '点赞用户')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('点赞用户列表的JSON数据')
            ->formatValue(function ($value) {
                if (is_array($value)) {
                    return '共 ' . count($value) . ' 个用户点赞';
                }
                return $value;
            });

        yield TextareaField::new('comments', '评论列表')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('评论列表的JSON数据')
            ->formatValue(function ($value) {
                if (is_array($value)) {
                    return '共 ' . count($value) . ' 条评论';
                }
                return $value;
            });

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
        $likeMoment = Action::new('likeMoment', '点赞', 'fas fa-heart')
            ->linkToCrudAction('likeMoment')
            ->addCssClass('btn btn-success')
            ->displayAsButton()
            ->displayIf(function (WeChatMoment $moment) {
                return !$moment->isLiked();
            });

        $unlikeMoment = Action::new('unlikeMoment', '取消点赞', 'fas fa-heart-broken')
            ->linkToCrudAction('unlikeMoment')
            ->addCssClass('btn btn-secondary')
            ->displayAsButton()
            ->displayIf(function (WeChatMoment $moment) {
                return $moment->isLiked();
            });

        $commentMoment = Action::new('commentMoment', '评论', 'fas fa-comment')
            ->linkToCrudAction('commentMoment')
            ->addCssClass('btn btn-primary')
            ->displayAsButton();

        $refreshMoment = Action::new('refreshMoment', '刷新动态', 'fas fa-sync')
            ->linkToCrudAction('refreshMoment')
            ->addCssClass('btn btn-info')
            ->displayAsButton();

        return $actions
            ->add(Crud::PAGE_DETAIL, $likeMoment)
            ->add(Crud::PAGE_DETAIL, $unlikeMoment)
            ->add(Crud::PAGE_DETAIL, $commentMoment)
            ->add(Crud::PAGE_DETAIL, $refreshMoment)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-plus')->setLabel('添加动态');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fas fa-edit')->setLabel('编辑');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fas fa-trash')->setLabel('删除');
            });
    }

    /**
     * 点赞朋友圈
     */
    public function likeMoment(): Response
    {
        // 这里可以实现点赞逻辑
        $this->addFlash('info', '朋友圈点赞功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => static::class,
        ]);
    }

    /**
     * 取消点赞朋友圈
     */
    public function unlikeMoment(): Response
    {
        // 这里可以实现取消点赞逻辑
        $this->addFlash('info', '取消点赞功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => static::class,
        ]);
    }

    /**
     * 评论朋友圈
     */
    public function commentMoment(): Response
    {
        // 这里可以实现评论逻辑
        $this->addFlash('info', '朋友圈评论功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => static::class,
        ]);
    }

    /**
     * 刷新朋友圈动态
     */
    public function refreshMoment(): Response
    {
        // 这里可以实现刷新动态逻辑
        $this->addFlash('info', '刷新朋友圈动态功能开发中');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => static::class,
        ]);
    }
}
