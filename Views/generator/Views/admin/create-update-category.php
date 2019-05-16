<?php //template

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsEssences\EssencesModule;
use Iliich246\YicmsEssences\Base\EssencesCategories;

/** @var $this \yii\web\View */
/** @var $essenceCategory \Iliich246\YicmsEssences\Base\EssencesCategories */
/** @var $fieldsGroup \Iliich246\YicmsCommon\Fields\FieldsGroup */
/** @var $filesBlocks \Iliich246\YicmsCommon\Files\FilesBlock[] */
/** @var $imagesBlocks \Iliich246\YicmsCommon\Images\ImagesBlock[] */
/** @var $conditionsGroup \Iliich246\YicmsCommon\Conditions\ConditionsGroup */

$js = <<<JS
;(function() {
    $('#category-delete').on('click',  function() {
        var button = this;

        if (!$(button).is('[data-category-id]')) return;

        var deleteUrl = $(button).data('deleteUrl');

        if (!($(this).hasClass('category-confirm-state'))) {
            $(this).before('<span>Are you sure that you want to delete category? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('category-confirm-state');
        } else {
            $.pjax({
                url: deleteUrl,
                container: '#create-update-essence-category-container',
                scrollTo: false,
                push: false,
                type: "POST",
                timeout: 2500
            });
        }
    });
    
    var pjaxContainer = $('#create-update-essence-category-container');
    
    $(pjaxContainer).on('pjax:success', function() {
        $(".alert").hide().slideDown(500).fadeTo(500, 1);
    
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 3000);
        
        $(pjaxContainer).on('pjax:error', function(xhr, textStatus) {
            bootbox.alert({
                size: 'large',
                title: "There are some error on ajax request!",
                message: textStatus.responseText,
                className: 'bootbox-error'
            });
        });   
    });   
})();
JS;

$this->registerJs($js, $this::POS_READY);

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1>Edit page</h1>
    </div>

<?php  ?>
    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['categories-list', 'essenceId' => $essenceCategory->essence->id]) ?>">
            <span>
                <?= EssencesModule::t('app', 'List of categories') ?></span>
        </a>
        <span> / </span>
        <?php if ($essenceCategory->scenario == EssencesCategories::SCENARIO_CREATE): ?>
            <span><?= EssencesModule::t('app', 'Create category') ?></span>
        <?php else: ?>
            <span><?= EssencesModule::t('app', 'Update category') ?></span>
        <?php endif; ?>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">
            <div class="content-block-title">
                <?php if ($essenceCategory->scenario == EssencesCategories::SCENARIO_CREATE): ?>
                    <h3><?= EssencesModule::t('app', 'Create category') ?></h3>
                <?php else: ?>
                    <h3><?= EssencesModule::t('app', 'Update category') ?></h3>
                <?php endif; ?>

                <?php Pjax::begin([
                    'options' => [
                        'id' => 'create-update-essence-category-container',
                    ],
                    'enablePushState'    => false,
                    'enableReplaceState' => false
                ]) ?>
                <?php $form = ActiveForm::begin([
                    'id' => 'create-update-essence-category',
                    'options' => [
                        'data-pjax' => true,
                    ],
                ]);
                ?>

                <?php if (isset($success) && $success): ?>
                    <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">×</span></button>
                        <strong>Success!</strong> Data on page updated.
                    </div>
                <?php endif; ?>

                <?php if (CommonModule::isUnderDev() || $essenceCategory->essence->getMaxCategoriesLevel() != 1): ?>
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essenceCategory, 'parent_id')->dropDownList(
                            $essenceCategory->getCategoriesForDropList(), [

                            ]
                        )
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (CommonModule::isUnderDev()): ?>
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essenceCategory, 'editable')->checkbox() ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essenceCategory, 'visible')->checkbox() ?>
                    </div>
                </div>

                <?php if ($essenceCategory->scenario == EssencesCategories::SCENARIO_UPDATE): ?>
                    <div class="row delete-button-row-page">
                        <div class="col-xs-12">
                            <br>
                            <button type="button"
                                    class="btn btn-danger"
                                    data-delete-url="<?= Url::toRoute([
                                        '/essences/admin/category-delete',
                                        'id' => $essenceCategory->id
                                    ])?>"
                                    data-category-id="<?= $essenceCategory->id ?>"
                                    id="category-delete">
                                Delete category
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($essenceCategory->scenario == EssencesCategories::SCENARIO_UPDATE): ?>
                    <div class="row control-buttons">
                        <div class="col-xs-12">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                            <?= Html::resetButton('Cancel', ['class' => 'btn btn-default cancel-button']) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row control-buttons">
                        <div class="col-xs-12">
                            <?= Html::submitButton('Create new category', ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php ActiveForm::end(); ?>
                <?php Pjax::end() ?>
            </div>
        </div>
    </div>

    <?php if ($essenceCategory->scenario == EssencesCategories::SCENARIO_UPDATE): ?>

    <div class="row content-block form-block">
        <div class="col-xs-12">
            <div class="content-block-title">
                <h3>Text fields</h3>
                <h4>Edit of text field on the page</h4>
            </div>

            <?= $this->render(CommonModule::getInstance()->yicmsLocation  . '/Common/Views/pjax/fields', [
                'fieldTemplateReference' => $essenceCategory->getFieldTemplateReference(),
                'fieldsGroup'            => $fieldsGroup
            ]) ?>

        </div>
    </div>

    <?php if ($filesBlocks): ?>
        <div class="row content-block">
            <div class="col-xs-12">
                <h3>File blocks</h3>
                <h4>Edit of file blocks on the page</h4>

                <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Common/Views/files/files-blocks', [
                    'filesBlocks'   => $filesBlocks,
                    'fileReference' => $essenceCategory->getFileReference(),
                ]) ?>

            </div>
        </div>

        <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Common/Views/files/files-modal') ?>
    <?php endif; ?>

    <?php if ($imagesBlocks): ?>
        <div class="row content-block">
            <div class="col-xs-12">
                <h3>Image blocks</h3>
                <h4>Edit of image blocks on the page</h4>

                <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Common/Views/images/images-blocks', [
                    'imagesBlocks'   => $imagesBlocks,
                    'imageReference' => $essenceCategory->getImageReference(),
                ]) ?>

            </div>
        </div>

        <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Common/Views/images/images-modal') ?>

    <?php endif; ?>

    <?php if ($conditionsGroup->isConditions()): ?>
        <div class="row content-block">
            <div class="col-xs-12">
                <h3>Conditions blocks</h3>

                <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Common/Views/conditions/conditions', [
                    'conditionsGroup'            => $conditionsGroup,
                    'conditionTemplateReference' => $essenceCategory->getConditionTemplateReference(),
                ]) ?>

            </div>
        </div>

    <?php endif; ?>


    <?php endif; ?>


</div>