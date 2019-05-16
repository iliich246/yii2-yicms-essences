<?php //template

use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $essences \Iliich246\YicmsEssences\Base\Essences[] */
/** @var $widget \app\yicms\Essences\Widgets\ModuleMenuWidget */

?>
<?php foreach ($essences as $essence): ?>
<div class="row link-block">
    <div class="col-xs-12">
        <h2><?= $essence->name() ?></h2>

        <?php if ($essence->is_categories): ?>
            <a <?php if ($widget->route == 'essences/admin/categories-list'): ?> class="active" <?php endif; ?>
                href="<?= Url::toRoute(['/essences/admin/categories-list', 'essenceId' => $essence->id]) ?>">
                Categories
            </a>
        <?php endif; ?>

        <a <?php if (($widget->route == 'essences/admin/represents-list')
            &&
            Yii::$app->request->get('essenceId') == $essence->id): ?> class="active"
        <?php endif; ?>
            href="<?= Url::toRoute(['/essences/admin/represents-list', 'essenceId' => $essence->id]) ?>">

            Elements list
        </a>

    </div>
</div>
<hr>
<?php endforeach; ?>
