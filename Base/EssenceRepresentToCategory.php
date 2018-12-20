<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;

/**
 * Class EssenceRepresentToCategory
 *
 * @property int $id
 * @property int $category_id
 * @property int $represent_id
 * @property int $represent_order
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssenceRepresentToCategory extends ActiveRecord implements SortOrderInterface
{
    use SortOrderTrait;

    /** @var array of buffer for represents
     * if view [<representId>] => [<categoryId1>, <categoryId2>, ... ,<categoryIdN>],
     */
    private static $representBuffer = [];
    /** @var array of buffer for categories
     * if view [<category_id>] => [<representId1>, <representId2>, ... ,<representIdN>],
     */
    private static $categoriesBuffer = [];
    /** @var int id of category that proxy from represent  for find represent order relative category */
    private $orderCategoryProxy;

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
     * Up order of represent in category group
     * @param $representId
     * @param $categoryId
     * @return bool
     */
    public static function upRepresentOrderInCategory($representId, $categoryId)
    {
        $orderWorker = self::findOrderWorker($representId, $categoryId);

        if (!$orderWorker) return false;

        $orderWorker->orderCategoryProxy = $categoryId;
        return $orderWorker->upOrder();
    }

    /**
     * Down order of represent in category group
     * @param $representId
     * @param $categoryId
     * @return bool
     */
    public static function downRepresentOrderInCategory($representId, $categoryId)
    {
        $orderWorker = self::findOrderWorker($representId, $categoryId);

        if (!$orderWorker) return false;

        $orderWorker->orderCategoryProxy = $categoryId;
        return $orderWorker->downOrder();
    }

    /**
     * Returns true if represent can up his order in category group
     * @param $representId
     * @param $categoryId
     * @return bool
     */
    public static function canUpRepresentOrderInCategory($representId, $categoryId)
    {
        $orderWorker = self::findOrderWorker($representId, $categoryId);

        if (!$orderWorker) return false;

        $orderWorker->orderCategoryProxy = $categoryId;
        return $orderWorker->canUpOrder();
    }

    /**
     * Returns true if represent can down his order in category group
     * @param $representId
     * @param $categoryId
     * @return bool
     */
    public static function canDownRepresentOrderInCategory($representId, $categoryId)
    {
        $orderWorker = self::findOrderWorker($representId, $categoryId);

        if (!$orderWorker) return false;

        $orderWorker->orderCategoryProxy = $categoryId;
        return $orderWorker->canDownOrder();
    }

    /**
     * Returns max order of represent in category group
     * @param $representId
     * @param $categoryId
     * @return bool|int|mixed
     */
    public static function maxRepresentOrderInCategory($representId, $categoryId)
    {
        /** @var self $orderWorker */
        $orderWorker = self::find()->where([
            'category_id'  => $categoryId,
        ])->one();

        if (!$orderWorker) return 1;

        $orderWorker->orderCategoryProxy = $categoryId;

        return $orderWorker->maxOrder();
    }

    /**
     * Find object for work with represent order in category group
     * @param $representId
     * @param $categoryId
     * @return EssenceRepresentToCategory
     */
    private static function findOrderWorker($representId, $categoryId)
    {
        /** @var self $orderWorker */
        $orderWorker = self::find()->where([
            'category_id'  => $categoryId,
            'represent_id' => $representId,
        ])->one();

        return $orderWorker;
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'category_id' => $this->orderCategoryProxy
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'represent_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->represent_order;
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

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->represent_order = $value;
    }


    /**
     * @inheritdoc
     */
    public function configToChangeOfOrder()
    {
        //$this->scenario = self::SCENARIO_UPDATE;
    }

    /**
     * @inheritdoc
     */
    public function getOrderAble()
    {
        return $this;
    }
}
