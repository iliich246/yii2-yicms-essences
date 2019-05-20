<?php

namespace Iliich246\YicmsEssences\Base;

use Iliich246\YicmsCommon\Base\AbstractModuleConfiguratorDb;

/**
 * Class EssencesConfigDb
 *
 * @property integer $isGenerated
 * @property integer $strongGenerating
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['isGenerated', 'strongGenerating'], 'boolean'],
        ];
    }
}
