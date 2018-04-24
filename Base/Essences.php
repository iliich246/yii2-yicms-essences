<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;

/**
 * Class Essences
 *
 * @property int $id
 * @property string $program_name
 * @property int $is_categories
 * @property int $count_subcategories
 * @property int $is_multiple_categories
 * @property int $essence_order
 * @property bool $editable
 * @property bool $visible
 * @property string $field_template_reference_category
 * @property string $file_template_reference_category
 * @property string $image_template_reference_category
 * @property string $condition_template_reference_category
 * @property string $field_template_reference_represent
 * @property string $file_template_reference_represent
 * @property string $image_template_reference_represent
 * @property string $condition_template_reference_represent
 *
 * @property EssencesCategories[] $essencesCategories
 * @property EssencesNamesTranslatesDb[] $essencesNamesTranslates
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class Essences extends ActiveRecord implements SortOrderInterface
{
    use SortOrderTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%essences}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_categories', 'count_subcategories', 'is_multiple_categories', 'essence_order'], 'integer'],
            [['editable', 'visible'], 'boolean'],
            [['program_name'], 'string', 'max' => 50],
            [['field_template_reference_category', 'file_template_reference_category', 'image_template_reference_category', 'condition_template_reference_category', 'field_template_reference_represent', 'file_template_reference_represent', 'image_template_reference_represent', 'condition_template_reference_represent'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'program_name' => 'Program Name',
            'is_categories' => 'Is Categories',
            'count_subcategories' => 'Count Subcategories',
            'is_multiple_categories' => 'Is Multiple Categories',
            'field_template_reference_category' => 'Field Template Reference Category',
            'file_template_reference_category' => 'File Template Reference Category',
            'image_template_reference_category' => 'Image Template Reference Category',
            'condition_template_reference_category' => 'Condition Template Reference Category',
            'field_template_reference_represent' => 'Field Template Reference Represent',
            'file_template_reference_represent' => 'File Template Reference Represent',
            'image_template_reference_represent' => 'Image Template Reference Represent',
            'condition_template_reference_represent' => 'Condition Template Reference Represent',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEssencesCategories()
    {
        return $this->hasMany(EssencesCategories::className(), ['essence_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEssencesNamesTranslates()
    {
        return $this->hasMany(EssencesNamesTranslatesDb::className(), ['essence_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find();
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'essence_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->essence_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->essence_order = $value;
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
