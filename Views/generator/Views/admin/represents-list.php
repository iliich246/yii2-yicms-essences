<?php //template

use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;
use Iliich246\YicmsEssences\EssencesModule;

/** @var $this \yii\web\View */
/** @var $essence \Iliich246\YicmsEssences\Base\Essences */
/** @var $represents \Iliich246\YicmsEssences\Base\EssencesRepresents[] */
/** @var $pagination \yii\data\Pagination */
/** @var $isOrder bool */
/** @var $selectedCategory integer */

$js = <<<JS
;(function() {
    var pjaxContainer = $('#essence-represents-list-container');

    $('.apply-filter').click(function() {
        var url = $(this).data('url');
        var categoryFilter = $('#category-filter').val();

        $.pjax({
            url: url + '&categoryId=' + categoryFilter,
            container: '#essence-represents-list-container',
            scrollTo: false,
            push: true,
            type: "POST",
            timeout: 2500
         });
    });
    
    $.pjax.defaults.timeout = 2500;

    $(document).on('click', '.up-order', function() {
        var url = $(this).data('url');

        $.pjax({
            url: url,
            container: '#essence-represents-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.down-order', function() {
        var url = $(this).data('url');

        $.pjax({
            url: url,
            container: '#essence-represents-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.up-order-category', function() {
        var url = $(this).data('url');

        $.pjax({
            url: url,
            container: '#essence-represents-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.down-order-category', function() {
        var url = $(this).data('url');

        $.pjax({
            url: url,
            container: '#essence-represents-list-container',
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
        <h1><?= EssencesModule::t('app', 'List of represents') ?></h1>
    </div>

    <div class="row content-block">
        <div class="col-xs-12">
            <div class="row control-buttons">
                <div class="col-xs-12">
                    <a href="<?= Url::toRoute(['represent-create', 'essenceId' => $essence->id]) ?>"
                       class="btn btn-primary create-essence-category-button"
                       data-home-url="<?= Url::base() ?>">
                        <?= EssencesModule::t('app', 'Create new represent') ?>
                    </a>
                </div>
            </div>

            <?php if ($essence->is_categories): ?>

            <div class="row content-block form-block">
                <div class="col-xs-12">
                    <div class="row">
                        <div class="col-xs-12">
                            <?= \yii\bootstrap\Html::dropDownList('filter', $selectedCategory, $essence->getCategoriesForDropList(), [
                                'style' => 'width: 100%',
                                'id' => 'category-filter',
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= \yii\bootstrap\Html::button('Apply filter', [
                                'class' => 'btn btn-primary apply-filter',
                                'style' => 'float: right; margin-top: 10px;',
                                'data-url' => Url::toRoute(['/essences/admin/represents-list',
                                    'essenceId' => $essence->id])
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php endif; ?>

            <?php Pjax::begin([
                'options' => [
                    'class' => 'pjax-container',
                    'id' => 'essence-represents-list-container'
                ],
                'enablePushState'    => false,
                'enableReplaceState' => false
            ]); ?>

            <div class="list-block">
                <?php foreach ($represents as $represent): ?>
                    <div class="row list-items">
                        <div class="col-xs-6 list-title">
                            <a data-pjax="0"
                               href="<?= Url::toRoute(['/essences/admin/represent-update', 'id' => $represent->id]) ?>">
                                <p>
                                    <?= $represent->adminName() ?>
                                </p>
                            </a>
                        </div>
                        <?php if ($isOrder): ?>
                            <?php if (!$selectedCategory): ?>
                                <div class="col-xs-2 list-controls">
                                    <?php if ($represent->canUpOrder()): ?>
                                        <span class="glyphicon glyphicon-arrow-up up-order"
                                              data-url="<?= Url::toRoute([
                                                  '/essences/admin/represent-up-order',
                                                  'id'   => $represent->id,
                                                  'page' => Yii::$app->request->get('page')
                                              ]) ?>">
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($represent->canDownOrder()): ?>
                                        <span class="glyphicon glyphicon-arrow-down down-order"
                                              data-url="<?= Url::toRoute([
                                                  '/essences/admin/represent-down-order',
                                                  'id'   => $represent->id,
                                                  'page' => Yii::$app->request->get('page')
                                              ]) ?>">
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="col-xs-2 list-controls">
                                    <?php if ($represent->canUpInCategory($selectedCategory)): ?>
                                        <span class="glyphicon glyphicon-arrow-up up-order-category"
                                              data-url="<?= Url::toRoute([
                                                  '/essences/admin/represent-up-order-in-category',
                                                  'representId' => $represent->id,
                                                  'categoryId'  => $selectedCategory,
                                                  'page'        => Yii::$app->request->get('page')
                                              ]) ?>">
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($represent->canDownInCategory($selectedCategory)): ?>
                                        <span class="glyphicon glyphicon-arrow-down down-order-category"
                                              data-url="<?= Url::toRoute([
                                                  '/essences/admin/represent-down-order-in-category',
                                                  'representId' => $represent->id,
                                                  'categoryId'  => $selectedCategory,
                                                  'page'        => Yii::$app->request->get('page')
                                              ]) ?>">
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?= LinkPager::widget([
                    'pagination' => $pagination,
                ]); ?>

            </div>
            <?php Pjax::end() ?>
        </div>
    </div>
</div>
