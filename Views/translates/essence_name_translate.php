<?php

/* @var $this \yii\web\View */
/* @var $translateModel \Iliich246\YicmsEssences\Base\EssenceDevTranslateForm */

?>

<?= $form->field($translateModel, "[$translateModel->key]name")->textInput() ?>

<?= $form->field($translateModel, "[$translateModel->key]description")->textarea() ?>
