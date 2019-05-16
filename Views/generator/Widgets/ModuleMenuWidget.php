<?php

namespace app\yicms\Essences\Widgets;

use Yii;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\AbstractModuleMenuWidget;
use Iliich246\YicmsEssences\Base\Essences;

/**
 * Class ModuleMenuWidget
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class ModuleMenuWidget extends AbstractModuleMenuWidget
{
    /**
     * @inheritdoc
     */
    public static function getModuleName()
    {
        return strtolower(CommonModule::getModuleName());
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->route = Yii::$app->controller->action->getUniqueId();

        $essencesQuery = Essences::find()->orderBy(['essence_order' => SORT_ASC]);

        if (!CommonModule::isUnderDev())
            $essencesQuery->where([
                'editable' => true,
            ]);

        $essences = $essencesQuery->all();

        return $this->render('module_menu', [
            'widget'   => $this,
            'essences' => $essences
        ]);
    }
}
