<?php

namespace Iliich246\YicmsEssences;

use Yii;
use Iliich246\YicmsCommon\Base\AbstractConfigurableModule;
use Iliich246\YicmsCommon\Base\YicmsModuleInterface;

/**
 * Class EssenceModule
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssenceModule extends AbstractConfigurableModule implements YicmsModuleInterface
{
    /** @inheritdoc */
    public $controllerMap = [
        'dev' => 'Iliich246\YicmsEssences\Controllers\DeveloperController'
    ];

    /**
     * @inherited
     */
    public function getNameSpace()
    {
        return __NAMESPACE__;
    }

    /**
     * @inherited
     */
    public static function getModuleName()
    {
        return 'Essences';
    }
}
