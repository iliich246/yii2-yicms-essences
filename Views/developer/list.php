<?php

use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $essences \Iliich246\YicmsEssences\Base\Essences[] */

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1>List of essences</h1>
    </div>
    <div class="row content-block">
        <div class="col-xs-12">
            <div class="row control-buttons">
                <div class="col-xs-12">
                    <a href="<?= Url::toRoute(['create']) ?>"
                       class="btn btn-primary create-page-button"
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
