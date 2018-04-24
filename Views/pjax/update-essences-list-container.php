<?php

use yii\widgets\Pjax;
use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $essences \Iliich246\YicmsEssences\Base\Essences[] */

?>

<?php Pjax::begin([
    'options' => [
        'id' => 'update-pages-list-container'
    ],
    'linkSelector' => false,
]) ?>
<div class="list-block">
    <?php foreach($essences as $essence): ?>
        <div class="row list-items">
            <div class="col-xs-10 list-title">
                <a href="<?= Url::toRoute(['update', 'id' => $essence->id]) ?>">
                    <p>
                        <?= $essence->program_name ?>
                    </p>
                </a>
            </div>
            <div class="col-xs-2 list-controls">
                <?php if ($essence->visible): ?>
                    <span class="glyphicon glyphicon-eye-open"></span>
                <?php else: ?>
                    <span class="glyphicon glyphicon-eye-close"></span>
                <?php endif; ?>
                <?php if ($essence->editable): ?>
                    <span class="glyphicon glyphicon-pencil"></span>
                <?php endif; ?>
                <?php if ($essence->canUpOrder()): ?>
                    <span class="glyphicon glyphicon-arrow-up"
                          data-essence-id="<?= $essence->id ?>"></span>
                <?php endif; ?>
                <?php if ($essence->canDownOrder()): ?>
                    <span class="glyphicon glyphicon-arrow-down"
                          data-essence-id="<?= $essence->id ?>"></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php Pjax::end() ?>
