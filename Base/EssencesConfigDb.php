<?php

namespace Iliich246\YicmsEssences\Base;

use Iliich246\YicmsCommon\Base\AbstractModuleConfiguratorDb;

/**
 * Class EssencesConfigDb
 *
 * @property int $id
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssencesConfigDb extends AbstractModuleConfiguratorDb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%essences_config}}';
    }
}
