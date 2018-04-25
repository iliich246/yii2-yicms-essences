<?php

use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $essences \Iliich246\YicmsEssences\Base\Essences[] */

$js = <<<JS
;(function() {
    var createEssenceButton = $('.create-essence-button');
    var homeUrl             = $(createEssenceButton).data('homeUrl');
    var essenceUpUrl        = homeUrl + '/essences/dev/essence-up-order';
    var essenceDownUrl      = homeUrl + '/essences/dev/essence-down-order';

    var pjaxContainer = $('#update-essences-list-container');

    $(document).on('click', '.glyphicon-arrow-up', function() {
        $.pjax({
            url: essenceUpUrl + '?essenceId=' + $(this).data('essenceId'),
            container: '#update-essences-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.glyphicon-arrow-down', function() {
        $.pjax({
            url: essenceDownUrl + '?essenceId=' + $(this).data('essenceId'),
            container: '#update-essences-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(pjaxContainer).on('pjax:error', function(xhr, textStatus) {
        bootbox.alert({
            size: 'large',
            title: "There are some error on ajax request!",
            message: textStatus.responseText,
            className: 'bootbox-error'
        });
    });
})();
JS;

$this->registerJs($js, $this::POS_READY);
?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1>List of essences</h1>
    </div>
    <div class="row content-block">
        <div class="col-xs-12">
            <div class="row control-buttons">
                <div class="col-xs-12">
                    <a href="<?= Url::toRoute(['create-essence']) ?>"
                       class="btn btn-primary create-essence-button"
                       data-home-url="<?= Url::base() ?>">
                        Create new essence
                    </a>
                </div>
            </div>

            <?= $this->render('/pjax/update-essences-list-container', [
                'essences' => $essences
            ]) ?>
        </div>
    </div>
</div>
