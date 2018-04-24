<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class EssencesCategories
 *
 * @property int $id
 * @property int $essence_id
 * @property int $parent_id
 * @property int $editable
 * @property int $visible
 * @property int $mode
 * @property int $category_order
 * @property string $system_route
 * @property string $ruled_route
 * @property string $field_reference
 * @property string $file_reference
 * @property string $image_reference
 * @property string $condition_reference
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Essences $essence
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssencesCategories extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%essences_categories}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['essence_id', 'parent_id', 'editable', 'visible', 'mode', 'category_order', 'created_at', 'updated_at'], 'integer'],
            [['system_route', 'ruled_route', 'field_reference', 'file_reference', 'image_reference', 'condition_reference'], 'string', 'max' => 255],
            [['essence_id'], 'exist', 'skipOnError' => true, 'targetClass' => Essences::className(), 'targetAttribute' => ['essence_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'essence_id' => 'Essence ID',
            'parent_id' => 'Parent ID',
            'editable' => 'Editable',
            'visible' => 'Visible',
            'mode' => 'Mode',
            'category_order' => 'Category Order',
            'system_route' => 'System Route',
            'ruled_route' => 'Ruled Route',
            'field_reference' => 'Field Reference',
            'file_reference' => 'File Reference',
            'image_reference' => 'Image Reference',
            'condition_reference' => 'Condition Reference',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEssence()
    {
        return $this->hasOne(Essences::className(), ['id' => 'essence_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepresents()
    {
        return $this->hasMany(EssencesRepresents::class, ['id' => 'represent_id'])
                ->viaTable('{{%essences_category_represent}}', ['category_id' => 'id']);
    }
}
/*public function getMarkets() {
    return $this->hasMany(Market::className(), ['id' => 'market_id'])
      ->viaTable('tbl_user_market', ['user_id' => 'id']);
}*/