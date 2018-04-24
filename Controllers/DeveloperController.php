<?php

namespace Iliich246\YicmsEssences\Controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Base\DevFilter;
use Iliich246\YicmsCommon\Base\CommonUser;
use Iliich246\YicmsCommon\Base\CommonHashForm;
use Iliich246\YicmsEssences\Base\Essences;

/**
 * Class DeveloperController
 *
 * Controller for developer section in essences module
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DeveloperController extends Controller
{
    /** @inheritdoc */
    public $layout = '@yicms-common/Views/layouts/developer';
    /** @inheritdoc */
    public $defaultAction = 'list';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
//            'root' => [
//                'class' => DevFilter::className(),
//                'except' => ['login-as-root'],
//            ],
        ];
    }

    /**
     * Renders list of all essences
     * @return string
     */
    public function actionList()
    {
        $essences = Essences::find()->orderBy([
            'essences_order' => SORT_ASC
        ])->all();

        return $this->render('/developer/list', [
            'essences' => $essences,
        ]);
    }

    public function actionCreateEssence()
    {

    }

    public function actionUpdateEssence()
    {

    }

    public function actionEssenceTranslates($id)
    {

    }

    public function actionDeleteEssence($id, $deletePass = false)
    {

    }

    /**
     * Action for up essence order
     * @param $essenceId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionEssenceUpOrder($essenceId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var Essences $essence */
        $essence = Essences::findOne($essenceId);

        if (!$essence) throw new NotFoundHttpException('Wrong essenceId = ' . $essenceId);

        $essence->configToChangeOfOrder();
        $essence->upOrder();

        $essences = Essences::find()->orderBy([
            'essence_order' => SORT_ASC
        ])->all();

        return $this->render('/pjax/update-essences-list-container', [
            'essences' => $essences
        ]);
    }

    /**
     * Action for down essence order
     * @param $essenceId
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionEssenceDownOrder($essenceId)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var Essences $essence */
        $essence = Essences::findOne($essenceId);

        if (!$essence) throw new NotFoundHttpException('Wrong essenceId = ' . $essenceId);

        $essence->configToChangeOfOrder();
        $essence->downOrder();

        $essences = Essences::find()->orderBy([
            'essence_order' => SORT_ASC
        ])->all();

        return $this->render('/pjax/update-essences-list-container', [
            'essences' => $essences
        ]);
    }

}
