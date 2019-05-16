<?php //template

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsEssences\EssencesModule;
use Iliich246\YicmsEssences\Base\EssencesRepresents;

/** @var $this \yii\web\View */
/** @var $essenceRepresent \Iliich246\YicmsEssences\Base\EssencesRepresents */
/** @var $fieldsGroup \Iliich246\YicmsCommon\Fields\FieldsGroup */
/** @var $filesBlocks \Iliich246\YicmsCommon\Files\FilesBlock[] */
/** @var $imagesBlocks \Iliich246\YicmsCommon\Images\ImagesBlock[] */
/** @var $conditionsGroup \Iliich246\YicmsCommon\Conditions\ConditionsGroup */

$js = <<<JS
;(function() {
    $('#represent-delete').on('click',  function() {
        var button = this;

        if (!$(button).is('[data-represent-id]')) return;

        var deleteUrl = $(button).data('url');

        if (!($(this).hasClass('represent-confirm-state'))) {
            $(this).before('<span>Are you sure that you want to delete represent? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('represent-confirm-state');
        } else {
            $.pjax({
                url: deleteUrl,
                container: '#create-update-essence-represent-container',
                scrollTo: false,
                push: false,
                type: "POST",
                timeout: 2500
            });
        }
    });
    
    var pjaxContainer = $('#create-update-essence-represent-container');
    
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

    $(document).on('click', '.add-category', function() {

        var url        = $(this).data('url');
        var categoryId = $('#essencesrepresents-category').val();
        
        if ($(this).data('isCreateMode') == 1) {
            url += '?categoryId=' + categoryId
        } else {
            url += '&categoryId=' + categoryId
        }

        $.pjax({
            url: url,
            container: '#create-update-essence-represent-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
         });
    });
    
    $(document).on('click', '.delete-category', function() {
        var url = $(this).data('url');     
        
        $.pjax({
            url: url,
            container: '#create-update-essence-represent-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
         });
    });
})();
JS;

$this->registerJs($js, $this::POS_READY);
?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <?php if ($essenceRepresent->scenario == EssencesRepresents::SCENARIO_CREATE): ?>
            <h1>Create represent</h1>
        <?php else: ?>
            <h1>Update represent</h1>
        <?php endif; ?>
    </div>

    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['represents-list', 'essenceId' => $essenceRepresent->essence->id]) ?>">
            <span>
                <?= EssencesModule::t('app', 'List of represents') ?></span>
        </a>
        <span> / </span>
        <?php if ($essenceRepresent->scenario == EssencesRepresents::SCENARIO_CREATE): ?>
            <span><?= EssencesModule::t('app', 'Create represent') ?></span>
        <?php else: ?>
            <span><?= EssencesModule::t('app', 'Update represent') ?></span>
        <?php endif; ?>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">
            <div class="content-block-title">

                <?php Pjax::begin([
                    'options' => [
                        'id' => 'create-update-essence-represent-container',
                    ],
                    'enablePushState'    => false,
                    'enableReplaceState' => false
                ]) ?>

                <?php if ($essenceRepresent->scenario == EssencesRepresents::SCENARIO_CREATE): ?>
                    <h3>Create represent</h3>
                <?php else: ?>
                    <h3>Update represent</h3>
                <?php endif; ?>

                <?php $form = ActiveForm::begin([
                    'id' => 'create-update-essence-represent',
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

                <?php if ($essenceRepresent->essence->isCategories()): ?>
                    <?php if (!$essenceRepresent->essence->isMultipleCategories()): ?>
                        <div class="row">
                            <div class="col-xs-12">

                                <?= $form->field($essenceRepresent, 'category')->dropDownList(
                                    CommonModule::isUnderDev() ?
                                        $essenceRepresent->getCategoriesForDropList() :
                                        $essenceRepresent->getCategoriesForDropList(false)
                                    )
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($essenceRepresent->scenario == EssencesRepresents::SCENARIO_UPDATE): ?>
                            <?php if ($essenceRepresent->canAddCategory()): ?>
                            <div class="row">
                                <div class="col-xs-11">
                                    <?= $form->field($essenceRepresent, 'category')->dropDownList(
                                            $essenceRepresent->getCategoriesForDropList(false)
                                    )
                                    ?>
                                </div>

                                <div class="col-xs-1">
                                    <button type="button"
                                            style="width: 100%;margin-top: 30px"
                                            class="btn btn-primary add-category"
                                            data-is-create-mode="
                                            <?php if ($essenceRepresent->scenario == EssencesRepresents::SCENARIO_CREATE): ?>
                                            1
                                            <?php else: ?>
                                            0
                                            <?php endif; ?>"
                                            data-url="<?= Url::toRoute(['/essences/admin/add-category-to-represent',
                                                'representId' => $essenceRepresent->id]) ?>"
                                        >
                                        +
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-xs-12">
                                    <?= $form->field($essenceRepresent, 'category')->dropDownList(
                                        $essenceRepresent->getCategoriesForDropList())
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-xs-12">
                                <?php foreach($essenceRepresent->getCategories() as $category): ?>
                                    <button type="button" class="btn btn-default">
                                        <?= $category->name() ?>
                                        <?php if ($essenceRepresent->canDeleteCategory($category)): ?>
                                        <span class="glyphicon glyphicon-remove delete-category" data-url="<?= Url::toRoute([
                                            '/essences/admin/remove-category-from-represent',
                                            'representId' => $essenceRepresent->id,
                                            'categoryId'  => $category->id
                                        ]) ?>"></span>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (CommonModule::isUnderDev()): ?>
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essenceRepresent, 'editable')->checkbox() ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essenceRepresent, 'visible')->checkbox() ?>
                    </div>
                </div>

                <?php if ($essenceRepresent->scenario == EssencesRepresents::SCENARIO_UPDATE): ?>

                    <div class="row delete-button-row">
                        <div class="col-xs-12">
                            <br>
                            <button type="button"
                                    class="btn btn-danger"
                                    data-url="<?= Url::toRoute([
                                        '/essences/admin/represent-delete',
                                        'id' => $essenceRepresent->id
                                    ]) ?>"
                                    data-represent-id="<?= $essenceRepresent->id ?>"
                                    id="represent-delete">
                                Delete represent
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($essenceRepresent->scenario == EssencesRepresents::SCENARIO_UPDATE): ?>
                    <div class="row control-buttons">
                        <div class="col-xs-12">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                            <?= Html::resetButton('Cancel', ['class' => 'btn btn-default cancel-button']) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row control-buttons">
                        <div class="col-xs-12">
                            <?= Html::submitButton('Create new represent', ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php ActiveForm::end(); ?>
                <?php Pjax::end() ?>
            </div>
        </div>
    </div>

    <?php if ($essenceRepresent->scenario == EssencesRepresents::SCENARIO_UPDATE): ?>

    <div class="row content-block form-block">
        <div class="col-xs-12">
            <div class="content-block-title">
                <h3>Text fields</h3>
                <h4>Edit of text field on the page</h4>
            </div>

            <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Common/Views/pjax/fields', [
                'fieldTemplateReference' => $essenceRepresent->getFieldTemplateReference(),
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
                        'fileReference' => $essenceRepresent->getFileReference(),
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
                        'imageReference' => $essenceRepresent->getImageReference(),
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
                        'conditionTemplateReference' => $essenceRepresent->getConditionTemplateReference(),
                    ]) ?>

                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
