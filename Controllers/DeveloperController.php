<?php

namespace Iliich246\YicmsEssences\Controllers;

use Iliich246\YicmsEssences\Base\EssencesConfigDb;
use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Base\DevFilter;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Base\CommonHashForm;
use Iliich246\YicmsEssences\Base\Essences;
use Iliich246\YicmsEssences\Base\EssencesException;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\DevFieldsGroup;
use Iliich246\YicmsCommon\Fields\FieldsDevModalWidget;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Files\DevFilesGroup;
use Iliich246\YicmsCommon\Files\FilesDevModalWidget;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Images\DevImagesGroup;
use Iliich246\YicmsCommon\Images\ImagesDevModalWidget;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;
use Iliich246\YicmsCommon\Conditions\DevConditionsGroup;
use Iliich246\YicmsCommon\Conditions\ConditionsDevModalWidget;
use Iliich246\YicmsEssences\Base\EssenceDevTranslateForm;

/**
 * Class DeveloperController
 *
 * Controller for developer section in essences module
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DeveloperController extends Controller
{
    /** @inheritdoc */
    public $layout = '@yicms-common/Views/layouts/developer';
    /** @inheritdoc */
    public $defaultAction = 'list';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'dev' => [
                'class' => DevFilter::class,
                'redirect' => function() {
                    return $this->redirect(Url::home());
                }
            ],
        ];
    }

    /**
     * Renders list of all essences
     * @return string
     */
    public function actionList()
    {
        $essences = Essences::find()->orderBy([
            'essence_order' => SORT_ASC
        ])->all();

        return $this->render('@yicms-essences/Views/developer/list', [
            'essences' => $essences,
        ]);
    }

    /**
     * Creates new essence
     * @return string|\yii\web\Response
     * @throws EssencesException
     */
    public function actionCreateEssence()
    {
        $essence = new Essences();
        $essence->scenario = Essences::SCENARIO_CREATE;

        if ($essence->load(Yii::$app->request->post()) && $essence->validate()) {

            if ($essence->create()) {
                return $this->redirect(Url::toRoute(['update-essence', 'id' => $essence->id]));
            } else {
                //TODO: add bootbox error
            }
        }

        return $this->render('@yicms-essences/Views/developer/create_update_essence', [
            'essence' => $essence,
        ]);
    }

    /**
     * Updates essence
     * @param $id
     * @return string
     * @throws EssencesException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \ReflectionException
     */
    public function actionUpdateEssence($id)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($id);

        if (!$essence)
            throw new NotFoundHttpException('Wrong essence ID');

        $essence->scenario = Essences::SCENARIO_UPDATE;
        $essence->annotate();

        if ($essence->load(Yii::$app->request->post()) && $essence->validate()) {

            if ($essence->save()) {
                $success = true;
            } else {
                $success = false;
            }

            return $this->render('@yicms-essences/Views/developer/create_update_essence', [
                'essence' => $essence,
                'success' => $success
            ]);
        }

        return $this->render('@yicms-essences/Views/developer/create_update_essence', [
            'essence' => $essence,
        ]);
    }

    /**
     * Displays page for work with admin translations of essences
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionEssenceTranslates($id)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($id);

        if (!$essence)
            throw new NotFoundHttpException('Wrong essence ID');

        $languages = Language::getInstance()->usedLanguages();

        $translateModels = [];

        foreach($languages as $key => $language) {
            $essenceTranslate = new EssenceDevTranslateForm();
            $essenceTranslate->setLanguage($language);
            $essenceTranslate->setEssence($essence);
            $essenceTranslate->loadFromDb();

            $translateModels[$key] = $essenceTranslate;
        }

        if (Model::loadMultiple($translateModels, Yii::$app->request->post()) &&
            Model::validateMultiple($translateModels)) {

            /** @var EssenceDevTranslateForm $translateModel */
            foreach($translateModels as $key=>$translateModel) {
                $translateModel->save();
            }

            return $this->render('@yicms-essences/Views/developer/essence_translates', [
                'essence'         => $essence,
                'translateModels' => $translateModels,
                'success'         => true,
            ]);
        }

        return $this->render('@yicms-essences/Views/developer/essence_translates', [
            'essence'         => $essence,
            'translateModels' => $translateModels,
        ]);

    }

    /**
     * Action for delete essence
     * @param $id
     * @param bool $deletePass
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws EssencesException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteEssence($id, $deletePass = false)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var Essences $essence */
        $essence = Essences::findOne($id);

        if (!$essence)
            throw new NotFoundHttpException('Wrong essence id');

        if ($essence->isConstraints())
            if (!Yii::$app->security->validatePassword($deletePass, CommonHashForm::DEV_HASH))
                throw new EssencesException('Wrong dev password');

        if ($essence->delete())
            return $this->redirect(Url::toRoute(['list']));

        throw new EssencesException('Delete error');
    }

    /**
     * Action for up essence order
     * @param $essenceId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionEssenceUpOrder($essenceId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var Essences $essence */
        $essence = Essences::findOne($essenceId);

        if (!$essence) throw new NotFoundHttpException('Wrong essenceId = ' . $essenceId);

        $essence->configToChangeOfOrder();
        $essence->upOrder();

        $essences = Essences::find()->orderBy([
            'essence_order' => SORT_ASC
        ])->all();

        return $this->render('@yicms-essences/Views/pjax/update-essences-list-container', [
            'essences' => $essences
        ]);
    }

    /**
     * Action for down essence order
     * @param $essenceId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionEssenceDownOrder($essenceId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var Essences $essence */
        $essence = Essences::findOne($essenceId);

        if (!$essence) throw new NotFoundHttpException('Wrong essenceId = ' . $essenceId);

        $essence->configToChangeOfOrder();
        $essence->downOrder();

        $essences = Essences::find()->orderBy([
            'essence_order' => SORT_ASC
        ])->all();

        return $this->render('@yicms-essences/Views/pjax/update-essences-list-container', [
            'essences' => $essences
        ]);
    }

    /**
     * Renders category templates page
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionEssenceCategoryTemplates($id)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($id);

        if (!$essence) throw new NotFoundHttpException('Wrong essenceId = ' . $id);

        //initialize fields group
        $devFieldGroup = new DevFieldsGroup();
        $devFieldGroup->setFieldTemplateReference($essence->getCategoryFieldTemplateReference());
        $devFieldGroup->initialize(Yii::$app->request->post('_fieldTemplateId'));

        //try to load validate and save field via pjax
        if ($devFieldGroup->load(Yii::$app->request->post()) && $devFieldGroup->validate()) {

            if (!$devFieldGroup->save()) {
                //TODO: bootbox error
            }

            $essence->annotate();

            return FieldsDevModalWidget::widget([
                'devFieldGroup' => $devFieldGroup,
                'dataSaved'     => true,
            ]);
        }

        $devFilesGroup = new DevFilesGroup();
        $devFilesGroup->setFilesTemplateReference($essence->getCategoryFileTemplateReference());
        $devFilesGroup->initialize(Yii::$app->request->post('_fileTemplateId'));

        //try to load validate and save field via pjax
        if ($devFilesGroup->load(Yii::$app->request->post()) && $devFilesGroup->validate()) {

            if (!$devFilesGroup->save()) {
                //TODO: bootbox error
            }

            $essence->annotate();

            return FilesDevModalWidget::widget([
                'devFilesGroup' => $devFilesGroup,
                'dataSaved'     => true,
            ]);
        }

        $devImagesGroup = new DevImagesGroup();
        $devImagesGroup->setImagesTemplateReference($essence->getCategoryImageTemplateReference());
        $devImagesGroup->initialize(Yii::$app->request->post('_imageTemplateId'));

        //try to load validate and save image block via pjax
        if ($devImagesGroup->load(Yii::$app->request->post()) && $devImagesGroup->validate()) {

            if (!$devImagesGroup->save()) {
                //TODO: bootbox error
            }

            $essence->annotate();

            return ImagesDevModalWidget::widget([
                'devImagesGroup' => $devImagesGroup,
                'dataSaved'      => true,
            ]);
        }

        $devConditionsGroup = new DevConditionsGroup();
        $devConditionsGroup->setConditionsTemplateReference($essence->getCategoryConditionTemplateReference());
        $devConditionsGroup->initialize(Yii::$app->request->post('_conditionTemplateId'));

        //try to load validate and save image block via pjax
        if ($devConditionsGroup->load(Yii::$app->request->post()) && $devConditionsGroup->validate()) {

            if (!$devConditionsGroup->save()) {
                //TODO: bootbox error
            }

            $essence->annotate();

            return ConditionsDevModalWidget::widget([
                'devConditionsGroup' => $devConditionsGroup,
                'dataSaved'          => true,
            ]);
        }

        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($essence->getCategoryFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $fieldTemplatesSingle = FieldTemplate::getListQuery($essence->getCategoryFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $filesBlocks = FilesBlock::getListQuery($essence->getCategoryFileTemplateReference())
            ->orderBy([FilesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $imagesBlocks = ImagesBlock::getListQuery($essence->getCategoryImageTemplateReference())
            ->orderBy([ImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $conditionTemplates = ConditionTemplate::getListQuery($essence->getCategoryConditionTemplateReference())
            ->orderBy([ConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('@yicms-essences/Views/developer/category_templates', [
            'essence'                    => $essence,
            'devFieldGroup'              => $devFieldGroup,
            'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
            'fieldTemplatesSingle'       => $fieldTemplatesSingle,
            'devFilesGroup'              => $devFilesGroup,
            'filesBlocks'                => $filesBlocks,
            'devImagesGroup'             => $devImagesGroup,
            'imagesBlocks'               => $imagesBlocks,
            'devConditionsGroup'         => $devConditionsGroup,
            'conditionTemplates'         => $conditionTemplates
        ]);
    }

    /**
     * Render represents template page
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionEssenceRepresentTemplates($id)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($id);

        if (!$essence) throw new NotFoundHttpException('Wrong essenceId = ' . $id);

        //initialize fields group
        $devFieldGroup = new DevFieldsGroup();
        $devFieldGroup->setFieldTemplateReference($essence->getRepresentFieldTemplateReference());
        $devFieldGroup->initialize(Yii::$app->request->post('_fieldTemplateId'));

        //try to load validate and save field via pjax
        if ($devFieldGroup->load(Yii::$app->request->post()) && $devFieldGroup->validate()) {

            if (!$devFieldGroup->save()) {
                //TODO: bootbox error
            }

            $essence->annotate();

            return FieldsDevModalWidget::widget([
                'devFieldGroup' => $devFieldGroup,
                'dataSaved' => true,
            ]);
        }

        $devFilesGroup = new DevFilesGroup();
        $devFilesGroup->setFilesTemplateReference($essence->getRepresentFileTemplateReference());
        $devFilesGroup->initialize(Yii::$app->request->post('_fileTemplateId'));

        //try to load validate and save field via pjax
        if ($devFilesGroup->load(Yii::$app->request->post()) && $devFilesGroup->validate()) {

            if (!$devFilesGroup->save()) {
                //TODO: bootbox error
            }

            $essence->annotate();

            return FilesDevModalWidget::widget([
                'devFilesGroup' => $devFilesGroup,
                'dataSaved' => true,
            ]);
        }

        $devImagesGroup = new DevImagesGroup();
        $devImagesGroup->setImagesTemplateReference($essence->getRepresentImageTemplateReference());
        $devImagesGroup->initialize(Yii::$app->request->post('_imageTemplateId'));

        //try to load validate and save image block via pjax
        if ($devImagesGroup->load(Yii::$app->request->post()) && $devImagesGroup->validate()) {

            if (!$devImagesGroup->save()) {
                //TODO: bootbox error
            }

            $essence->annotate();

            return ImagesDevModalWidget::widget([
                'devImagesGroup' => $devImagesGroup,
                'dataSaved' => true,
            ]);
        }

        $devConditionsGroup = new DevConditionsGroup();
        $devConditionsGroup->setConditionsTemplateReference($essence->getRepresentConditionTemplateReference());
        $devConditionsGroup->initialize(Yii::$app->request->post('_conditionTemplateId'));

        //try to load validate and save image block via pjax
        if ($devConditionsGroup->load(Yii::$app->request->post()) && $devConditionsGroup->validate()) {

            if (!$devConditionsGroup->save()) {
                //TODO: bootbox error
            }

            $essence->annotate();

            return ConditionsDevModalWidget::widget([
                'devConditionsGroup' => $devConditionsGroup,
                'dataSaved' => true,
            ]);
        }

        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($essence->getRepresentFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $fieldTemplatesSingle = FieldTemplate::getListQuery($essence->getRepresentFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $filesBlocks = FilesBlock::getListQuery($essence->getRepresentFieldTemplateReference())
            ->orderBy([FilesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $imagesBlocks = ImagesBlock::getListQuery($essence->getRepresentImageTemplateReference())
            ->orderBy([ImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $conditionTemplates = ConditionTemplate::getListQuery($essence->getRepresentConditionTemplateReference())
            ->orderBy([ConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('@yicms-essences/Views/developer/represents_templates', [
            'essence'                    => $essence,
            'devFieldGroup'              => $devFieldGroup,
            'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
            'fieldTemplatesSingle'       => $fieldTemplatesSingle,
            'devFilesGroup'              => $devFilesGroup,
            'filesBlocks'                => $filesBlocks,
            'devImagesGroup'             => $devImagesGroup,
            'imagesBlocks'               => $imagesBlocks,
            'devConditionsGroup'         => $devConditionsGroup,
            'conditionTemplates'         => $conditionTemplates
        ]);
    }

    /**
     * Maintenance action for essence module
     * @return string
     * @throws EssencesException
     */
    public function actionMaintenance()
    {
        $config = EssencesConfigDb::getInstance();

        if ($config->load(Yii::$app->request->post()) && $config->validate()) {
            if ($config->save()) {
                return $this->render('@yicms-essences/Views/developer/maintenance', [
                    'config'  => $config,
                    'success' => true,
                ]);
            }

            throw new EssencesException('Can`t save data in database');
        }

        return $this->render('@yicms-essences/Views/developer/maintenance', [
            'config' => $config
        ]);
    }
}
