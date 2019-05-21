<?php

namespace Iliich246\YicmsEssences;

use Iliich246\YicmsEssences\Base\EssencesConfigDb;
use Yii;
use yii\base\BootstrapInterface;
use Iliich246\YicmsCommon\Base\Generator;
use Iliich246\YicmsCommon\Base\YicmsModuleInterface;
use Iliich246\YicmsCommon\Base\AbstractConfigurableModule;

/**
 * Class EssenceModule
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssencesModule extends AbstractConfigurableModule implements
    BootstrapInterface,
    YicmsModuleInterface
{
    /** @var bool keeps true if for this module was generated changeable admin files */
    public $isGenerated = false;
    /** @var bool if true generator will be generate in strong mode, even existed files will be replaced */
    public $strongGenerating = false;

    /** @inheritdoc */
    public $configurable = [
        'isGenerated',
    ];

    /** @inheritdoc */
    public $controllerMap = [
        'dev' => 'Iliich246\YicmsEssences\Controllers\DeveloperController'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        //TODO: makes correct build of controller map via common->$yicmsLocation
        $this->controllerMap['admin'] = 'app\yicms\Essences\Controllers\AdminController';

//        Yii::setAlias('@yicms-essences', Yii::getAlias('@vendor') .
//            DIRECTORY_SEPARATOR .
//            'iliich246' .
//            DIRECTORY_SEPARATOR .
//            'yii2-yicms-essences');

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $generator = new Generator($this);
        $generator->generate();
    }

    /**
     * Proxy translate method from module to framework
     * @param $category
     * @param $message
     * @param array $params
     * @param null $language
     * @return mixed
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        //Implement this method correctly
        return $message;
    }

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
    public function getModuleDir()
    {
        return __DIR__;
    }

    /**
     * @inherited
     */
    public function isGenerated()
    {
        return !!$this->isGenerated;
    }

    /**
     * @inherited
     */
    public function setAsGenerated()
    {
        $config = EssencesConfigDb::getInstance();
        $config->isGenerated = true;

        $config->save(false);
    }

    /**
     * @inherited
     */
    public function isGeneratorInStrongMode()
    {
        return !!$this->strongGenerating;
    }

    /**
     * @inherited
     */
    public static function getModuleName()
    {
        return 'Essences';
    }
}
