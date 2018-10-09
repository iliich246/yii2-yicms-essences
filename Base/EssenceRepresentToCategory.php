<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class EssenceRepresentToCategory
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssenceRepresentToCategory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%essences_category_represent}}';
    }
}
