<?php

use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $widget \Iliich246\YicmsEssences\Widgets\ModuleDevMenuWidget */

?>

<div class="row link-block">
    <div class="col-xs-12">
        <h2>Essences module</h2>
        <a <?php if ($widget->route == 'essences/dev/list'): ?> class="active" <?php endif; ?>
            href="<?= Url::toRoute('/essences/dev/list') ?>">
            List of essences
        </a>
        <a <?php if (
            ($widget->route == 'essences/dev/create')
            ||
            ($widget->route == 'essences/dev/update')
        ):?> class="active" <?php endif; ?>
            href="<?= Url::toRoute('/essences/dev/create') ?>">
            Create/update essence
        </a>
    </div>
</div>
<hr>

