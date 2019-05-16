<?php

use yii\helpers\Url;
use yii\widgets\Pjax;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsEssences\EssencesModule;

/** @var $this \yii\web\View */
/** @var $essence \Iliich246\YicmsEssences\Base\Essences */
/** @var $category \Iliich246\YicmsEssences\Base\EssencesCategories */

$js = <<<JS
;(function() {
    var createNewCategoryButton = $('.create-essence-category-button');
    var homeUrl                 = $(createNewCategoryButton).data('homeUrl');
    var essenceCategoryDownUrl  = homeUrl + '/essences/admin/essence-category-order-down';
    var essenceCategoryUpUrl    = homeUrl + '/essences/admin/essence-category-order-up';

    $(document).on('click', '.glyphicon-arrow-down', function() {
        $.pjax({
            url: essenceCategoryDownUrl + '?essenceCategoryId=' + $(this).data('categoryId'),
            container: '#essence-categories-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.glyphicon-arrow-up', function() {
        $.pjax({
            url: essenceCategoryUpUrl + '?essenceCategoryId=' + $(this).data('categoryId'),
            container: '#essence-categories-list-container',
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
        <h1><?= EssencesModule::t('app', 'List of categories') ?></h1>
    </div>

    <div class="row content-block">
        <div class="col-xs-12">
            <div class="row control-buttons">
                <?php if (CommonModule::isUnderDev() || $essence->canCreateCategoryByAdmin()): ?>
                <div class="col-xs-12">
                    <a href="<?= Url::toRoute(['create-category', 'essenceId' => $essence->id]) ?>"
                       class="btn btn-primary create-essence-category-button"
                       data-home-url="<?= Url::base() ?>">
                        <?= EssencesModule::t('app', 'Create new category') ?>
                        <?php if (!$essence->canCreateCategoryByAdmin()): ?> (dev mode) <?php endif; ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php Pjax::begin([
                'options' => [
                    'class' => 'pjax-container',
                    'id'    => 'essence-categories-list-container'
                ],
            ]); ?>

            <div class="list-block">
                <?php foreach($essence->categories as $category): ?>
                    <div class="row list-items">
                        <div class="col-xs-6 list-title">
                            <a data-pjax="0"
                               href="<?= Url::toRoute(['update-category', 'categoryId' => $category->id]) ?>"
                            >
                                <p>
                                    <?php for($i=0;$i<$category->getLevel();$i++): ?>
                                        -
                                    <?php endfor; ?>
                                    <?= $category->adminName() ?>
                                </p>
                            </a>
                        </div>
                        <div class="col-xs-2 list-controls">
                            <?php if ($category->canUpOrder()): ?>
                                <span class="glyphicon glyphicon-arrow-up"
                                      data-category-id="<?= $category->id ?>"></span>
                            <?php endif; ?>
                            <?php if ($category->canDownOrder()): ?>
                                <span class="glyphicon glyphicon-arrow-down"
                                      data-category-id="<?= $category->id ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach;  ?>
            </div>
            <?php Pjax::end() ?>
        </div>
    </div>
</div>
