<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsEssences\Base\Essences;

/** @var $this \yii\web\View */
/** @var $essence \Iliich246\YicmsEssences\Base\Essences */

$js = <<<JS
;(function() {
    var pjaxContainer   = $('#update-essence-container');
    var pjaxContainerId = '#update-essence-container';

    $(pjaxContainer).on('pjax:success', function() {
        $(".alert").hide().slideDown(500).fadeTo(500, 1);

        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 3000);
    });

    $(pjaxContainer).on('pjax:error', function(xhr, textStatus) {
        bootbox.alert({
            size: 'large',
            title: "There are some error on ajax request!",
            message: textStatus.responseText,
            className: 'bootbox-error'
        });
    });

    $('#essence-delete').on('click',  function() {
        var button = this;

        if (!$(button).is('[data-essence-id]')) return;

        var essenceId             = $(button).data('essenceId');
        var essenceHasConstraints = $(button).data('essenceHasConstraints');
        var homeUrl               = $(button).data('homeUrl');
        var deleteUrl             = homeUrl + '/essences/dev/delete-essence';

        if (!($(this).hasClass('essence-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('essence-confirm-state');
        } else {
            if (!essenceHasConstraints) {
                $.pjax({
                    url: deleteUrl + '?id=' + essenceId,
                    container: pjaxContainerId,
                    scrollTo: false,
                    push: false,
                    type: "POST",
                    timeout: 2500
                 });
            } else {
                var deleteButtonRow = $('.delete-button-row');

                var template = _.template($('#delete-with-pass-template').html());
                $(deleteButtonRow).empty();
                $(deleteButtonRow).append(template);

                var passwordInput = $('#essence-delete-password-input');
                var buttonDelete  = $('#button-delete-with-pass');

                $(buttonDelete).on('click', function() {
                    $.pjax({
                        url: deleteUrl + '?id=' + essenceId +
                                         '&deletePass=' + $(passwordInput).val(),
                        container: pjaxContainerId,
                        scrollTo: false,
                        push: false,
                        type: "POST",
                        timeout: 2500
                    });
                });

                $(pjaxContainer).on('pjax:error', function(event) {
                    bootbox.alert({
                        size: 'large',
                        title: "Wrong dev password",
                        message: "Page has not deleted",
                        className: 'bootbox-error'
                    });
                });
            }
        }
    });
})();
JS;

\Iliich246\YicmsCommon\Assets\LodashAsset::register($this);
$this->registerJs($js, $this::POS_READY);

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <?php if ($essence->scenario == Essences::SCENARIO_CREATE): ?>
            <h1>Create Essence</h1>
        <?php else: ?>
            <h1>Update Essence</h1>
            <h2>IMPORTANT! Do not change essences names in production without serious reason!</h2>
        <?php endif; ?>
    </div>

    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['list']) ?>"><span>Essences list</span></a> <span> / </span>
        <?php if ($essence->scenario == Essences::SCENARIO_CREATE): ?>
            <span>Create essence</span>
        <?php else: ?>
            <span>Update essence</span>
        <?php endif; ?>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">
            <div class="content-block-title">
                <?php if ($essence->scenario == Essences::SCENARIO_CREATE): ?>
                    <h3>Create essence</h3>
                <?php else: ?>
                    <h3>Update essence</h3>
                <?php endif; ?>
            </div>
            <?php if ($essence->scenario == Essences::SCENARIO_UPDATE): ?>
                <div class="row control-buttons">
                    <div class="col-xs-12">

                        <a href="<?= Url::toRoute(['essence-translates', 'id' => $essence->id]) ?>"
                           class="btn btn-primary">
                            Essence name translates
                        </a>

                        <a href="<?= Url::toRoute(['essence-category-templates', 'id' => $essence->id]) ?>"
                           class="btn btn-primary">
                            Essence category templates
                        </a>

                        <a href="<?= Url::toRoute(['essence-represent-templates', 'id' => $essence->id]) ?>"
                           class="btn btn-primary">
                            Essence represent templates
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-essence-container',
                ]
            ]) ?>
            <?php $form = ActiveForm::begin([
                'id' => 'create-update-essence-form',
                'options' => [
                    'data-pjax' => true,
                ],
            ]);
            ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong>Success!</strong> Essence data updated.
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($essence, 'program_name') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <?= $form->field($essence, 'is_categories')->checkbox() ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($essence, 'categories_create_by_user')->checkbox() ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($essence, 'count_subcategories') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($essence, 'is_multiple_categories')->checkbox() ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($essence, 'max_categories') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($essence, 'is_intermediate_categories')->checkbox() ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($essence, 'represents_pagination_count') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($essence, 'editable')->checkbox() ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($essence, 'visible')->checkbox() ?>
                </div>
            </div>

            <?php if ($essence->scenario == Essences::SCENARIO_CREATE): ?>

                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essence, 'createCategoriesStandardFields')->checkbox() ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essence, 'createRepresentsStandardFields')->checkbox() ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essence, 'createCategoriesSeoFields')->checkbox() ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essence, 'createRepresentSeoFields')->checkbox() ?>
                    </div>
                </div>

            <?php endif; ?>

            <?php if ($essence->scenario == Essences::SCENARIO_UPDATE): ?>

                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essence, 'category_form_name_field')->dropDownList(
                            $essence->getCategoriesFieldsList(),
                            [
                                //'prompt' => '123'
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($essence, 'represent_form_name_field')->dropDownList(
                            $essence->getRepresentsFieldsList(),
                        [
                            //'prompt' => ''
                        ]) ?>
                    </div>
                </div>

            <?php endif; ?>

            <?php if ($essence->scenario == Essences::SCENARIO_UPDATE): ?>
                <div class="row delete-button-row">
                    <div class="col-xs-12">
                        <br>
                        <button type="button"
                                class="btn btn-danger"
                                data-home-url="<?= \yii\helpers\Url::base() ?>"
                                data-essence-id="<?= $essence->id ?>"
                                data-essence-has-constraints="<?= (int)$essence->isConstraints() ?>"
                                id="essence-delete">
                            Delete essence
                        </button>
                    </div>
                </div>
                <script type="text/template" id="delete-with-pass-template">
                    <div class="col-xs-12">
                        <br>
                        <label for="page-delete-password-input">
                            Essence has constraints. Enter dev password for delete essence
                        </label>
                        <input type="password"
                               id="essence-delete-password-input"
                               class="form-control" name=""
                               value=""
                               aria-required="true"
                               aria-invalid="false">
                        <br>
                        <button type="button"
                                class="btn btn-danger"
                                id="button-delete-with-pass"
                            >
                            Yes, i am absolutely seriously!!!
                        </button>
                    </div>
                </script>
            <?php endif; ?>


            <div class="row control-buttons">
                <div class="col-xs-12">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                    <?= Html::resetButton('Cancel', ['class' => 'btn btn-default cancel-button']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <?php Pjax::end() ?>
        </div>
    </div>
</div>
