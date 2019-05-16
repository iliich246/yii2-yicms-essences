<?php

namespace app\yicms\Essences\Controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\AdminFilter;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Fields\FieldsGroup;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Conditions\ConditionsGroup;
use Iliich246\YicmsEssences\Base\Essences;
use Iliich246\YicmsEssences\Base\EssencesException;
use Iliich246\YicmsEssences\Base\EssencesCategories;
use Iliich246\YicmsEssences\Base\EssencesRepresents;

/**
 * Class AdminController
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->layout = CommonModule::getInstance()->yicmsLocation . '/Common/Views/layouts/admin';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
//            'root' => [
//                'class' => AdminFilter::className(),
//                'except' => ['login-as-root'],
//            ],
        ];
    }

    /**
     * Shows list of categories for essence
     * @param $essenceId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionCategoriesList($essenceId)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($essenceId);
        $essence->offAnnotation();

        if (!$essence) throw new BadRequestHttpException('Wrong essence ID = ' . $essenceId);

        if (!$essence->is_categories)
            throw new NotFoundHttpException();

        if (!CommonModule::isUnderDev() && !$essence->editable)
            throw new NotFoundHttpException();

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/categories-list', [
            'essence' => $essence,
        ]);
    }

    /**
     * Action for create new essence category
     * @param $essenceId
     * @return string|\yii\web\Response
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     */
    public function actionCreateCategory($essenceId)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($essenceId);

        if (!$essence) throw new BadRequestHttpException('Wrong essence ID = ' . $essenceId);

        $essence->offAnnotation();

        if (!CommonModule::isUnderDev() && !$essence->editable)
            throw new NotFoundHttpException();

        if (!CommonModule::isUnderDev() && !$essence->is_categories)
            throw new NotFoundHttpException();

        if (!CommonModule::isUnderDev() && !$essence->canCreateCategoryByAdmin())
            throw new NotFoundHttpException();

        $essenceCategory = new EssencesCategories();
        $essenceCategory->scenario = EssencesCategories::SCENARIO_CREATE;
        $essenceCategory->setEssence($essence);
        $essenceCategory->setFictive();

        if ($essenceCategory->load(Yii::$app->request->post()) && $essenceCategory->validate()) {

            if ($essenceCategory->save())
                return $this->redirect(['update-category', 'categoryId' => $essenceCategory->id]);

            throw new EssencesException('Essence save error');
        }

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/create-update-category', [
            'essenceCategory' => $essenceCategory,
        ]);
    }

    /**
     * Updates existed category
     * @param $categoryId
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionUpdateCategory($categoryId)
    {
        /** @var EssencesCategories $essenceCategory */
        $essenceCategory = EssencesCategories::findOne($categoryId);

        if (!$essenceCategory)
            throw new BadRequestHttpException('Wrong category ID = ' . $categoryId);

        if (!$essenceCategory->essence->is_categories)
            throw new NotFoundHttpException();

        $essenceCategory->offAnnotation();

        $essenceCategory->scenario = EssencesCategories::SCENARIO_UPDATE;

        if ($essenceCategory->load(Yii::$app->request->post()) && $essenceCategory->validate()) {

            if ($essenceCategory->save()) {
                return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/create-update-category', [
                    'essenceCategory' => $essenceCategory,
                    'success'         => true,
                ]);
            }
        }

        $fieldsGroup = new FieldsGroup();
        $fieldsGroup->setFieldsReferenceAble($essenceCategory);
        $fieldsGroup->initialize();

        //try to load validate and save field via pjax
        if ($fieldsGroup->load(Yii::$app->request->post()) && $fieldsGroup->validate()) {

            if (!$fieldsGroup->save()) {
                //TODO: bootbox error
            }

            return $this->render(CommonModule::getInstance()->yicmsLocation  . '/Common/Views/pjax/fields', [
                'fieldsGroup' => $fieldsGroup,
                'fieldTemplateReference' => $essenceCategory->getFieldTemplateReference(),
                'success'                => true,
            ]);
        }

        $conditionsGroup = new ConditionsGroup();
        $conditionsGroup->setConditionsReferenceAble($essenceCategory);
        $conditionsGroup->initialize();

        if ($conditionsGroup->load(Yii::$app->request->post()) && $conditionsGroup->validate()) {
            $conditionsGroup->save();

            return $this->render(CommonModule::getInstance()->yicmsLocation  . '/Common/Views/conditions/conditions', [
                'conditionsGroup'            => $conditionsGroup,
                'conditionTemplateReference' => $essenceCategory->getConditionTemplateReference(),
                'success'                    => true,
            ]);
        }

        /** @var FilesBlock $filesBlocks */
        $filesBlocksQuery = FilesBlock::find()->where([
            'file_template_reference' => $essenceCategory->getFileTemplateReference(),
        ])->orderBy([
            FilesBlock::getOrderFieldName() => SORT_ASC
        ]);

        if (CommonModule::isUnderAdmin())
            $filesBlocksQuery->andWhere([
                'editable' => true,
            ]);

        $filesBlocks = $filesBlocksQuery->all();

        foreach ($filesBlocks as $fileBlock)
            $fileBlock->setFileReference($essenceCategory->getFileReference());

        /** @var ImagesBlock $imagesBlock */
        $imagesBlockQuery = ImagesBlock::find()->where([
            'image_template_reference' => $essenceCategory->getImageTemplateReference()
        ])->orderBy([
            ImagesBlock::getOrderFieldName() => SORT_ASC
        ]);

        if (CommonModule::isUnderAdmin())
            $imagesBlockQuery->andWhere([
                'editable' => true,
            ]);

        $imagesBlocks = $imagesBlockQuery->all();

        foreach ($imagesBlocks as $imagesBlock)
            $imagesBlock->setImageReference($essenceCategory->getImageReference());

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/create-update-category', [
            'essenceCategory' => $essenceCategory,
            'fieldsGroup'     => $fieldsGroup,
            'filesBlocks'     => $filesBlocks,
            'imagesBlocks'    => $imagesBlocks,
            'conditionsGroup' => $conditionsGroup
        ]);
    }

    /**
     * Delete category by id
     * @param $id
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCategoryDelete($id)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesCategories $essenceCategory */
        $essenceCategory = EssencesCategories::findOne($id);

        if (!$essenceCategory) throw new NotFoundHttpException('Wrong category id = ' . $id);

        $essenceCategory->offAnnotation();

        $essence = $essenceCategory->getEssence();

        if ($essenceCategory->delete())
            return $this->redirect(Url::toRoute(['/essences/admin/categories-list', 'essenceId' => $essence->id]));

        throw new EssencesException('Error on category delete');
    }

    /**
     * Action for up essence category order
     * @param $essenceCategoryId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionEssenceCategoryOrderUp($essenceCategoryId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesCategories $essenceCategory */
        $essenceCategory = EssencesCategories::findOne($essenceCategoryId);

        if (!$essenceCategory) throw new NotFoundHttpException('Wrong $essenceCategoryId = ' . $essenceCategoryId);

        $essenceCategory->offAnnotation();

        $essenceCategory->configToChangeOfOrder();
        $essenceCategory->upOrder();

        $essence = $essenceCategory->essence;

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/categories-list', [
            'essence' => $essence
        ]);
    }

    /**
     * Action for down essence category order
     * @param $essenceCategoryId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionEssenceCategoryOrderDown($essenceCategoryId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesCategories $essenceCategory */
        $essenceCategory = EssencesCategories::findOne($essenceCategoryId);

        if (!$essenceCategory) throw new NotFoundHttpException('Wrong $essenceCategoryId = ' . $essenceCategoryId);

        $essenceCategory->offAnnotation();

        $essenceCategory->configToChangeOfOrder();
        $essenceCategory->downOrder();

        $essence = $essenceCategory->essence;

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/categories-list', [
            'essence' => $essence
        ]);
    }

    /**
     * Return list of represents with filtering
     * @param $essenceId
     * @param bool $categoryId
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     */
    public function actionRepresentsList($essenceId, $categoryId = false)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($essenceId);

        if (!$essence) throw new BadRequestHttpException('Wrong essence ID = ' . $essenceId);

        if (!CommonModule::isUnderDev() && !$essence->editable)
            throw new NotFoundHttpException();

        $essence->offAnnotation();

        $isOrder = true;
        $selectedCategory = 0;
        $category = null;

        if (!$categoryId) $representsQuery = $essence
                        ->getAllRepresentsQuery()
                        ->orderBy(['represent_order' => SORT_ASC]);
        else if ($categoryId == -1) {
            $representsQuery = $essence->getRepresentsWithoutCategoriesQuery();
            $isOrder = false;
            $selectedCategory = -1;
        }
        else {
            $category = $essence->getCategoryById($categoryId);

            if (!$category)
                throw new BadRequestHttpException('Wrong category id');

            $representsQuery = $category->getRepresentsQuery();

            $selectedCategory = $categoryId;
        }

        $pagination = new Pagination([
            'totalCount'      => $representsQuery->count(),
            'defaultPageSize' => $essence->represents_pagination_count,
        ]);

        /** @var EssencesRepresents[] $represents */
        $represents = $representsQuery->offset($pagination->offset)
                                      ->limit($pagination->limit)
                                      ->all();

        foreach($represents as $represent)
            $represent->offAnnotation();

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/represents-list', [
            'essence'          => $essence,
            'represents'       => $represents,
            'pagination'       => $pagination,
            'isOrder'          => $isOrder,
            'selectedCategory' => $selectedCategory,
            'category'         => $category
        ]);
    }

    /**
     * Creates represent
     * @param $essenceId
     * @return string|\yii\web\Response
     * @throws EssencesException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionRepresentCreate($essenceId)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($essenceId);

        if (!$essence) throw new NotFoundHttpException('Wrong essenceId = ' . $essenceId);

        if (!CommonModule::isUnderDev() && !$essence->editable)
            throw new NotFoundHttpException();

        $essence->offAnnotation();

        $essenceRepresent = new EssencesRepresents();
        $essenceRepresent->scenario = EssencesRepresents::SCENARIO_CREATE;
        $essenceRepresent->setEssence($essence);
        $essenceRepresent->setFictive();

        if ($essenceRepresent->load(Yii::$app->request->post()) && $essenceRepresent->validate()) {

            if ($essenceRepresent->save())
                return $this->redirect(['represent-update', 'id' => $essenceRepresent->id]);

            throw new EssencesException('Essence save error');
        }

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/create-update-represent', [
            'essence'          => $essence,
            'essenceRepresent' => $essenceRepresent,
        ]);
    }

    /**
     * Update represent
     * @param $id
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionRepresentUpdate($id)
    {
        /** @var EssencesRepresents $essenceRepresent */
        $essenceRepresent = EssencesRepresents::findOne($id);

        if (!$essenceRepresent)
            throw new BadRequestHttpException('Wrong essence represent ID = ' . $id);

        $essenceRepresent->offAnnotation();

        $essenceRepresent->scenario = EssencesRepresents::SCENARIO_UPDATE;
        $essenceRepresent->loadCategoryField();

        if ($essenceRepresent->load(Yii::$app->request->post()) && $essenceRepresent->validate()) {

            if ($essenceRepresent->save()) {
                return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/create-update-represent', [
                    'essenceRepresent' => $essenceRepresent,
                    'success'          => true,
                ]);
            }
        }

        $fieldsGroup = new FieldsGroup();
        $fieldsGroup->setFieldsReferenceAble($essenceRepresent);
        $fieldsGroup->initialize();

        //try to load validate and save field via pjax
        if ($fieldsGroup->load(Yii::$app->request->post()) && $fieldsGroup->validate()) {

            if (!$fieldsGroup->save()) {
                //TODO: bootbox error
            }

            return $this->render(CommonModule::getInstance()->yicmsLocation  . '/Common/Views/pjax/fields', [
                'fieldsGroup' => $fieldsGroup,
                'fieldTemplateReference' => $essenceRepresent->getFieldTemplateReference(),
                'success' => true,
            ]);
        }

        $conditionsGroup = new ConditionsGroup();
        $conditionsGroup->setConditionsReferenceAble($essenceRepresent);
        $conditionsGroup->initialize();

        if ($conditionsGroup->load(Yii::$app->request->post()) && $conditionsGroup->validate()) {
            $conditionsGroup->save();

            return $this->render(CommonModule::getInstance()->yicmsLocation  . '/Common/Views/conditions/conditions', [
                'conditionsGroup'            => $conditionsGroup,
                'conditionTemplateReference' => $essenceRepresent->getConditionTemplateReference(),
                'success'                    => true,
            ]);
        }

        //throw new \Exception(print_r('There', true));

        /** @var FilesBlock $filesBlocks */
        $filesBlocksQuery = FilesBlock::find()->where([
            'file_template_reference' => $essenceRepresent->getFileTemplateReference(),
        ])->orderBy([
            FilesBlock::getOrderFieldName() => SORT_ASC
        ]);

        if (CommonModule::isUnderAdmin())
            $filesBlocksQuery->andWhere([
                'editable' => true,
            ]);

        $filesBlocks = $filesBlocksQuery->all();

        foreach ($filesBlocks as $fileBlock)
            $fileBlock->setFileReference($essenceRepresent->getFileReference());

        /** @var ImagesBlock $imagesBlock */
        $imagesBlockQuery = ImagesBlock::find()->where([
            'image_template_reference' => $essenceRepresent->getImageTemplateReference()
        ])->orderBy([
            ImagesBlock::getOrderFieldName() => SORT_ASC
        ]);

        if (CommonModule::isUnderAdmin())
            $imagesBlockQuery->andWhere([
                'editable' => true,
            ]);

        $imagesBlocks = $imagesBlockQuery->all();

        foreach ($imagesBlocks as $imagesBlock)
            $imagesBlock->setImageReference($essenceRepresent->getImageReference());


        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/create-update-represent', [
            'essenceRepresent' => $essenceRepresent,
            'fieldsGroup'      => $fieldsGroup,
            'filesBlocks'      => $filesBlocks,
            'imagesBlocks'     => $imagesBlocks,
            'conditionsGroup'  => $conditionsGroup
        ]);

    }

    /**
     * Action for delete represent
     * @param $id
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRepresentDelete($id)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesRepresents $essenceRepresent */
        $essenceRepresent = EssencesRepresents::findOne($id);

        if (!$essenceRepresent) throw new NotFoundHttpException('Wrong essence represent id = ' . $id);

        $essenceRepresent->offAnnotation();

        $essence = $essenceRepresent->getEssence();

        if ($essenceRepresent->delete())
            return $this->redirect(Url::toRoute(['/essences/admin/represents-list', 'essenceId' => $essence->id]));

        throw new EssencesException('Error on represent delete');
    }

    /**
     * Action for move represent order up
     * @param $id
     * @param $page
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     */
    public function actionRepresentUpOrder($id, $page = false)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesRepresents $essenceRepresent */
        $essenceRepresent = EssencesRepresents::findOne($id);

        if (!$essenceRepresent) throw new NotFoundHttpException('Wrong essence represent id = ' . $id);

        $essenceRepresent->offAnnotation();

        $essenceRepresent->configToChangeOfOrder();
        $essenceRepresent->upOrder();

        $essence = $essenceRepresent->getEssence();
        $essence->offAnnotation();

        $representsQuery = $essence->getAllRepresentsQuery();

        $isOrder = true;
        $selectedCategory = 0;
        $category = null;

        $pagination = new Pagination([
            'totalCount'      => $representsQuery->count(),
            'defaultPageSize' => $essence->represents_pagination_count,
            'page' => $page - 1,
            'route'           => Url::toRoute([
                    '/essences/admin/represents-list',

                ]),
            'params' => [
                'essenceId' => $essence->id,
                'categoryId' => 0
            ]
        ]);

        $pagination->setPage($page-1);

        /** @var EssencesRepresents[] $represents */
        $represents = $representsQuery->offset($pagination->offset)
            ->orderBy(['represent_order' => SORT_ASC])
            ->limit($pagination->limit)
            ->all();

        foreach ($represents as $represent)
            $represent->offAnnotation();

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/represents-list', [
            'essence'          => $essence,
            'represents'       => $represents,
            'pagination'       => $pagination,
            'isOrder'          => $isOrder,
            'selectedCategory' => $selectedCategory,
            'category'         => $category
        ]);
    }

    /**
     * Action for move represent order down
     * @param $id
     * @param bool $page
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     */
    public function actionRepresentDownOrder($id, $page = false)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesRepresents $essenceRepresent */
        $essenceRepresent = EssencesRepresents::findOne($id);

        if (!$essenceRepresent) throw new NotFoundHttpException('Wrong essence represent id = ' . $id);

        $essenceRepresent->offAnnotation();

        $essenceRepresent->configToChangeOfOrder();
        $essenceRepresent->downOrder();

        $essence = $essenceRepresent->getEssence();
        $essence->offAnnotation();

        $representsQuery = $essence->getAllRepresentsQuery();

        $isOrder = true;
        $selectedCategory = 0;
        $category = null;

        $pagination = new Pagination([
            'totalCount'      => $representsQuery->count(),
            'defaultPageSize' => $essence->represents_pagination_count,
            'page' => $page - 1,
            'route'           => Url::toRoute([
                '/essences/admin/represents-list',

            ]),
            'params' => [
                'essenceId' => $essence->id,
                'categoryId' => 0
            ]
        ]);

        /** @var EssencesRepresents[] $represents */
        $represents = $representsQuery->offset($pagination->offset)
            ->orderBy(['represent_order' => SORT_ASC])
            ->limit($pagination->limit)
            ->all();

        foreach ($represents as $represent)
            $represent->offAnnotation();

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/represents-list', [
            'essence'          => $essence,
            'represents'       => $represents,
            'pagination'       => $pagination,
            'isOrder'          => $isOrder,
            'selectedCategory' => $selectedCategory,
            'category'         => $category
        ]);
    }

    /**
     * Action for move represent order up in category group
     * @param $representId
     * @param $categoryId
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     */
    public function actionRepresentUpOrderInCategory($representId, $categoryId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesRepresents $essenceRepresent */
        $essenceRepresent = EssencesRepresents::findOne($representId);

        if (!$essenceRepresent) throw new NotFoundHttpException('Wrong essence represent id = ' . $representId);

        $essenceRepresent->offAnnotation();

        /** @var EssencesCategories $essenceCategory */
        $essenceCategory = EssencesCategories::findOne($categoryId);

        if (!$essenceCategory) throw new NotFoundHttpException('Wrong essence category id = ' . $categoryId);

        $essenceCategory->offAnnotation();

        $essenceRepresent->configToChangeOfOrder();
        $essenceRepresent->upInCategory($categoryId);

        $essence = $essenceRepresent->getEssence();
        $essence->offAnnotation();

        $representsQuery = $essenceCategory->getRepresentsQuery();

        $pagination = new Pagination([
            'totalCount'      => $representsQuery->count(),
            'defaultPageSize' => $essence->represents_pagination_count,
        ]);

        /** @var EssencesRepresents[] $represents */
        $represents = $representsQuery->offset($pagination->offset)
                                      ->limit($pagination->limit)
                                      ->all();

        foreach ($represents as $represent)
            $represent->offAnnotation();

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/represents-list', [
            'essence'          => $essence,
            'represents'       => $represents,
            'pagination'       => $pagination,
            'isOrder'          => true,
            'selectedCategory' => $categoryId,
            'category'         => $essenceCategory
        ]);
    }

    /**
     * Action for move represent order down in category group
     * @param $representId
     * @param $categoryId
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     */
    public function actionRepresentDownOrderInCategory($representId, $categoryId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesRepresents $essenceRepresent */
        $essenceRepresent = EssencesRepresents::findOne($representId);

        if (!$essenceRepresent) throw new NotFoundHttpException('Wrong essence represent id = ' . $representId);

        $essenceRepresent->offAnnotation();

        /** @var EssencesCategories $essenceCategory */
        $essenceCategory = EssencesCategories::findOne($categoryId);

        if (!$essenceCategory) throw new NotFoundHttpException('Wrong essence category id = ' . $categoryId);

        $essenceCategory->offAnnotation();

        $essenceRepresent->configToChangeOfOrder();
        $essenceRepresent->downInCategory($categoryId);

        $essence = $essenceRepresent->getEssence();
        $essence->offAnnotation();

        $representsQuery = $essenceCategory->getRepresentsQuery();

        $pagination = new Pagination([
            'totalCount'      => $representsQuery->count(),
            'defaultPageSize' => $essence->represents_pagination_count,
        ]);

        /** @var EssencesRepresents[] $represents */
        $represents = $representsQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        foreach ($represents as $represent)
            $represent->offAnnotation();

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/represents-list', [
            'essence'          => $essence,
            'represents'       => $represents,
            'pagination'       => $pagination,
            'isOrder'          => true,
            'selectedCategory' => $categoryId,
            'category'         => $essenceCategory
        ]);
    }


    /**
     * Add category to represent
     * @param $representId
     * @param $categoryId
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     */
    public function actionAddCategoryToRepresent($representId, $categoryId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesRepresents $represent */
        $represent = EssencesRepresents::findOne($representId);

        if (!$represent) throw new BadRequestHttpException('Wrong represent Id');

        $represent->offAnnotation();
        $represent->scenario = EssencesRepresents::SCENARIO_UPDATE;

        /** @var EssencesCategories $category */
        $category = EssencesCategories::findOne($categoryId);

        if (!$category) throw new BadRequestHttpException('Wrong category Id');

        $category->offAnnotation();

        if (!$represent->addCategory($category))
            throw new BadRequestHttpException('Wrong adding category');

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/create-update-represent', [
            'essenceRepresent' => $represent,
            'success'         => true,
        ]);
    }

    /**
     * Action for delete category form represent
     * @param $representId
     * @param $categoryId
     * @return string
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRemoveCategoryFromRepresent($representId, $categoryId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var EssencesRepresents $represent */
        $represent = EssencesRepresents::findOne($representId);

        if (!$represent) throw new BadRequestHttpException('Wrong represent Id');

        $represent->scenario = EssencesRepresents::SCENARIO_UPDATE;

        /** @var EssencesCategories $category */
        $category = EssencesCategories::findOne($categoryId);

        if (!$category) throw new BadRequestHttpException('Wrong category Id');

        if (!CommonModule::isUnderDev() && !$represent->canDeleteCategory($category))
            throw new BadRequestHttpException('Wrong delete category');

        if (!$represent->deleteCategory($category))
            throw new BadRequestHttpException('Wrong delete category');

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Essences/Views/admin/create-update-represent', [
            'essenceRepresent' => $represent,
            'success'         => true,
        ]);
    }
}
