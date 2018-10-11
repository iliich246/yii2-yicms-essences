<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class EssenceRepresentToCategory
 *
 * @property int $id
 * @property int $category_id
 * @property int $represent_id
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssenceRepresentToCategory extends ActiveRecord
{
    /** @var array of buffer for represents
     * if view [<representId>] => [<categoryId1>, <categoryId2>, ... ,<categoryIdN>],
     */
    private static $representBuffer = [];
    /** @var array of buffer for categories
     * if view [<category_id>] => [<representId1>, <representId2>, ... ,<representIdN>],
     */
    private static $categoriesBuffer = [];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%essences_category_represent}}';
    }

    /**
     * Return array of categories id for represent id
     * @param $representId
     * @return array
     */
    public static function getCategoriesArrayForRepresent($representId)
    {
        if (isset(self::$representBuffer[$representId]))
            return self::$representBuffer[$representId];

        $categoriesReps = self::find()->where([
            'represent_id' => $representId
        ])->asArray()->all();

        $result = [];

        foreach ($categoriesReps as $cr)
            $result[] = $cr['category_id'];

        return self::$representBuffer[$representId] = $result;
    }
}
