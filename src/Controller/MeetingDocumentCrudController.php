<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\MeetingDocument;

/**
 * @extends AbstractCrudController<MeetingDocument>
 */
#[AdminCrud(routePath: '/tencent-meeting/meeting-document', routeName: 'tencent_meeting_meeting_document')]
final class MeetingDocumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MeetingDocument::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议文档')
            ->setEntityLabelInPlural('会议文档管理')
            ->setPageTitle('index', '会议文档列表')
            ->setPageTitle('new', '创建会议文档')
            ->setPageTitle('edit', '编辑会议文档')
            ->setPageTitle('detail', '会议文档详情')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setTimezone('Asia/Shanghai')
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IntegerField::new('id', 'ID')
            ->onlyOnIndex()
            ->setTextAlign('center')
        ;

        $meeting = AssociationField::new('meeting', '所属会议')
            ->setHelp('此文档关联的会议')
            ->setColumns(6)
        ;

        $documentName = TextField::new('documentName', '文档名称')
            ->setHelp('文档的显示名称')
            ->setColumns(6)
        ;

        $documentUrl = UrlField::new('documentUrl', '文档URL')
            ->setHelp('文档的访问链接')
            ->hideOnIndex()
        ;

        $documentType = ChoiceField::new('documentType', '文档类型')
            ->setChoices([
                'PDF文档' => 'pdf',
                'Word文档' => 'doc',
                'Word文档(新版)' => 'docx',
                'PowerPoint' => 'ppt',
                'PowerPoint(新版)' => 'pptx',
                'Excel表格' => 'xls',
                'Excel表格(新版)' => 'xlsx',
                '文本文件' => 'txt',
                '图片文件' => 'image',
                '视频文件' => 'video',
                '其他' => 'other',
            ])
            ->setHelp('文档的类型分类')
            ->setColumns(6)
        ;

        $fileSize = NumberField::new('fileSize', '文件大小')
            ->setHelp('文件大小，单位为字节')
            ->formatValue(function (int $value): string {
                if ($value >= 1073741824) {
                    return round($value / 1073741824, 2) . ' GB';
                }
                if ($value >= 1048576) {
                    return round($value / 1048576, 2) . ' MB';
                }
                if ($value >= 1024) {
                    return round($value / 1024, 2) . ' KB';
                }

                return $value . ' B';
            })
            ->setColumns(6)
        ;

        $mimeType = TextField::new('mimeType', 'MIME类型')
            ->setHelp('文件的MIME类型标识')
            ->hideOnIndex()
            ->setRequired(false)
        ;

        $status = ChoiceField::new('status', '文档状态')
            ->setChoices([
                '上传中' => 'uploading',
                '可用' => 'available',
                '处理中' => 'processing',
                '已删除' => 'deleted',
            ])
            ->setHelp('文档的当前状态')
            ->setColumns(6)
        ;

        $filePath = TextField::new('filePath', '文件路径')
            ->setHelp('文件在服务器上的存储路径')
            ->hideOnIndex()
            ->setRequired(false)
        ;

        $storagePath = TextField::new('storagePath', '存储路径')
            ->setHelp('文件的存储位置标识')
            ->hideOnIndex()
            ->setRequired(false)
        ;

        $thumbnailUrl = UrlField::new('thumbnailUrl', '缩略图URL')
            ->setHelp('文档的缩略图链接')
            ->hideOnIndex()
            ->setRequired(false)
        ;

        $uploaderUserId = TextField::new('uploaderUserId', '上传者用户ID')
            ->setHelp('上传此文档的用户标识')
            ->setColumns(6)
            ->setRequired(false)
        ;

        $downloadCount = IntegerField::new('downloadCount', '下载次数')
            ->setHelp('文档被下载的总次数')
            ->setColumns(6)
        ;

        $viewCount = IntegerField::new('viewCount', '查看次数')
            ->setHelp('文档被查看的总次数')
            ->setColumns(6)
        ;

        $expirationTime = DateTimeField::new('expirationTime', '过期时间')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->setHelp('文档的过期时间，过期后将无法访问')
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $description = TextareaField::new('description', '文档描述')
            ->setHelp('对文档内容的详细描述')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $remark = TextareaField::new('remark', '备注')
            ->setHelp('管理员备注信息')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $config = AssociationField::new('config', '配置')
            ->setHelp('关联的腾讯会议配置')
            ->hideOnIndex()
        ;

        $createdAt = DateTimeField::new('createdAt', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;

        $updatedAt = DateTimeField::new('updatedAt', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            return [
                $id,
                $meeting,
                $documentName,
                $documentType,
                $fileSize,
                $status,
                $downloadCount,
                $viewCount,
            ];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                $id,
                $meeting,
                $documentName,
                $documentUrl,
                $documentType,
                $fileSize,
                $mimeType,
                $status,
                $filePath,
                $storagePath,
                $thumbnailUrl,
                $uploaderUserId,
                $downloadCount,
                $viewCount,
                $expirationTime,
                $description,
                $remark,
                $config,
                $createdAt,
                $updatedAt,
            ];
        }

        return [
            $meeting,
            $documentName,
            $documentUrl,
            $documentType,
            $fileSize,
            $mimeType,
            $status,
            $filePath,
            $storagePath,
            $thumbnailUrl,
            $uploaderUserId,
            $downloadCount,
            $viewCount,
            $expirationTime,
            $description,
            $remark,
            $config,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('meeting', '所属会议'))
            ->add(TextFilter::new('documentName', '文档名称'))
            ->add(ChoiceFilter::new('documentType', '文档类型')->setChoices([
                'PDF文档' => 'pdf',
                'Word文档' => 'doc',
                'Word文档(新版)' => 'docx',
                'PowerPoint' => 'ppt',
                'PowerPoint(新版)' => 'pptx',
                'Excel表格' => 'xls',
                'Excel表格(新版)' => 'xlsx',
                '文本文件' => 'txt',
                '图片文件' => 'image',
                '视频文件' => 'video',
                '其他' => 'other',
            ]))
            ->add(ChoiceFilter::new('status', '文档状态')->setChoices([
                '上传中' => 'uploading',
                '可用' => 'available',
                '处理中' => 'processing',
                '已删除' => 'deleted',
            ]))
            ->add(TextFilter::new('uploaderUserId', '上传者用户ID'))
            ->add(EntityFilter::new('config', '配置'))
        ;
    }
}
