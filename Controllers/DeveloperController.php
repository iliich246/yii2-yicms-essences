<?php

namespace Iliich246\YicmsEssences\Controllers;

use Iliich246\YicmsEssences\Base\EssenceDevTranslateForm;
use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Base\DevFilter;
use Iliich246\YicmsCommon\Base\CommonUser;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Base\CommonHashForm;
use Iliich246\YicmsEssences\Base\Essences;
use Iliich246\YicmsEssences\Base\EssencesException;

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
            'essence_order' => SORT_ASC
        ])->all();

        return $this->render('/developer/list', [
            'essences' => $essences,
        ]);
    }

    /**
     * Creates new essence
     * @return string|\yii\web\Response
     */
    public function actionCreateEssence()
    {
        $essence = new Essences();
        $essence->scenario = Essences::SCENARIO_CREATE;

        if ($essence->load(Yii::$app->request->post()) && $essence->validate()) {

            if ($essence->save()) {
                return $this->redirect(Url::toRoute(['update-essence', 'id' => $essence->id]));
            } else {
                //TODO: add bootbox error
            }
        }

        return $this->render('/developer/create_update_essence', [
            'essence' => $essence,
        ]);
    }

    /**
     * Updates essence
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdateEssence($id)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($id);

        if (!$essence)
            throw new NotFoundHttpException('Wrong essence ID');

        $essence->scenario = Essences::SCENARIO_UPDATE;

        //update page via pjax
        if ($essence->load(Yii::$app->request->post()) && $essence->validate()) {

            if ($essence->save()) {
                $success = true;
            } else {
                $success = false;
            }

            return $this->render('/developer/create_update_essence', [
                'essence' => $essence,
                'success' => $success
            ]);
        }

        return $this->render('/developer/create_update_essence', [
            'essence' => $essence,
        ]);
    }

    /**
     * Displays page for work with admin translations of essences
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionEssenceTranslates($id)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($id);

        if (!$essence)
            throw new NotFoundHttpException('Wrong essence ID');

        $languages = Language::getInstance()->usedLanguages();

        $translateModels = [];

        foreach($languages as $key => $language) {
            $essenceTranslate = new EssenceDevTranslateForm();
            $essenceTranslate->setLanguage($language);
            $essenceTranslate->setEssence($essence);
            $essenceTranslate->loadFromDb();

            $translateModels[$key] = $essenceTranslate;
        }

        if (Model::loadMultiple($translateModels, Yii::$app->request->post()) &&
            Model::validateMultiple($translateModels)) {

            /** @var EssenceDevTranslateForm $translateModel */
            foreach($translateModels as $key=>$translateModel) {
                $translateModel->save();
            }

            return $this->render('/developer/essence_translates', [
                'essence'         => $essence,
                'translateModels' => $translateModels,
                'success'         => true,
            ]);
        }

        return $this->render('/developer/essence_translates', [
            'essence'         => $essence,
            'translateModels' => $translateModels,
        ]);

    }

    /**
     * Action for delete essence
     * @param $id
     * @param bool|false $deletePass
     * @return \yii\web\Response
     * @throws EssencesException
     * @throws NotFoundHttpException
     */
    public function actionDeleteEssence($id, $deletePass = false)
    {
        /** @var Essences $essence */
        $essence = Essences::findOne($id);

        if (!$essence)
            throw new NotFoundHttpException('Wrong essence ID');

        if ($essence->isConstraints())
            if (!Yii::$app->security->validatePassword($deletePass, CommonHashForm::DEV_HASH))
                throw new EssencesException('Wrong dev password');

        if ($essence->delete())
            return $this->redirect(Url::toRoute(['list']));

        throw new EssencesException('Delete error');
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
