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

    /**
     * Clear represents buffer for current represent id
     * @param $representId
     */
    public static function clearBufferForRepresent($representId)
    {
        if (isset(self::$representBuffer[$representId]))
            unset(self::$representBuffer[$representId]);
    }

    /**
     * Return array of represents id for category id
     * @param $categoryId
     * @return array
     */
    public static function getRepresentsArrayForCategory($categoryId)
    {
        if (isset(self::$categoriesBuffer[$categoryId]))
            return self::$categoriesBuffer[$categoryId];

        $representsReps = self::find()->where([
            'category_id' => $categoryId
        ])->asArray()->all();

        $result = [];

        foreach ($representsReps as $rr)
            $result[] = $rr['represent_id'];

        return self::$categoriesBuffer[$categoryId] = $result;
    }

    /**
     * Clear represents buffer for current category id
     * @param $categoryId
     */
    public static function clearBufferForCategory($categoryId)
    {
        if (isset(self::$categoriesBuffer[$categoryId]))
            unset(self::$categoriesBuffer[$categoryId]);
    }
}
