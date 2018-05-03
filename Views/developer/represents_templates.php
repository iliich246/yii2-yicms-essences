<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsEssences\Base\Essences;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\FieldsDevModalWidget;
use Iliich246\YicmsCommon\Files\FilesDevModalWidget;
use Iliich246\YicmsCommon\Images\ImagesDevModalWidget;
use Iliich246\YicmsCommon\Conditions\ConditionsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $essence Iliich246\YicmsEssences\Base\Essences */
/** @var $devFieldGroup \Iliich246\YicmsCommon\Fields\DevFieldsGroup */
/** @var $fieldTemplatesTranslatable FieldTemplate[] */
/** @var $fieldTemplatesSingle FieldTemplate[] */
/** @var $filesBlocks \Iliich246\YicmsCommon\Files\FilesBlock[] */
/** @var $devFilesGroup \Iliich246\YicmsCommon\Files\DevFilesGroup */
/** @var $imagesBlocks \Iliich246\YicmsCommon\Images\ImagesBlock[] */
/** @var $devImagesGroup \Iliich246\YicmsCommon\Images\DevImagesGroup */
/** @var $devConditionsGroup Iliich246\YicmsCommon\Conditions\DevConditionsGroup */
/** @var $conditionTemplates Iliich246\YicmsCommon\Conditions\ConditionTemplate[] */
/** @var $success bool */

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1>Edit essence (<?= $essence->program_name ?>) represent templates</h1>
    </div>

    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['list']) ?>"><span>Essences list</span></a>

        <span> / </span>

        <a href="<?= Url::toRoute(['update-essence', 'id' => $essence->id]) ?>">
            <span>Update essence (<?= $essence->program_name ?>)</span>
        </a>

        <span> / </span>

        <span>Essence represent templates</span>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">

            <div class="content-block-title">
                <h3>Edit represent templates</h3>
            </div>

        </div>
    </div>

    <?= $this->render('@yicms-common/views/pjax/update-fields-list-container', [
        'fieldTemplateReference'     => $essence->getCategoryFieldTemplateReference(),
        'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
        'fieldTemplatesSingle'       => $fieldTemplatesSingle
    ]) ?>

    <?= FieldsDevModalWidget::widget([
        'devFieldGroup' => $devFieldGroup,
    ])
    ?>

    <?= $this->render('@yicms-common/Views/pjax/update-files-list-container', [
        'fileTemplateReference' => $essence->getRepresentFileTemplateReference(),
        'filesBlocks'           => $filesBlocks,
    ]) ?>

    <?= FilesDevModalWidget::widget([
        'devFilesGroup' => $devFilesGroup,
        'action' => Url::toRoute(['/essences/dev/essence-category-templates',
            'id' => $essence->id])
    ]) ?>

    <?= $this->render('@yicms-common/Views/pjax/update-images-list-container', [
        'imageTemplateReference' => $essence->getRepresentImageTemplateReference(),
        'imagesBlocks'           => $imagesBlocks,
    ]) ?>

    <?= ImagesDevModalWidget::widget([
        'devImagesGroup' => $devImagesGroup,
        'action' => Url::toRoute(['/essences/dev/essence-category-templates',
            'id' => $essence->id])
    ]) ?>

    <?= $this->render('@yicms-common/Views/pjax/update-conditions-list-container', [
        'conditionTemplateReference' => $essence->getRepresentConditionTemplateReference(),
        'conditionsTemplates'        => $conditionTemplates,
    ]) ?>

    <?= ConditionsDevModalWidget::widget([
        'devConditionsGroup' => $devConditionsGroup,
        'action' => Url::toRoute(['/essences/dev/essence-category-templates',
            'id' => $essence->id])
    ]) ?>
</div>
