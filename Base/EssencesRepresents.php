<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class AbstractTreeNode
 *
 * @property int $id
 * @property int $represent_order
 * @property int $editable
 * @property int $visible
 * @property string $system_route
 * @property string $ruled_route
 * @property string $field_reference
 * @property string $file_reference
 * @property string $image_reference
 * @property string $condition_reference
 * @property int $created_at
 * @property int $updated_at
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssencesRepresents extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%essences_represents}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['represent_order', 'editable', 'visible', 'created_at', 'updated_at'], 'integer'],
            [['system_route', 'ruled_route', 'field_reference', 'file_reference', 'image_reference', 'condition_reference'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'represent_order' => 'Represent Order',
            'editable' => 'Editable',
            'visible' => 'Visible',
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
    public function getCategories()
    {
        return $this->hasMany(EssencesCategories::class, ['id' => 'category_id'])
            ->viaTable('{{%essences_category_represent}}', ['represent_id' => 'id']);
    }
}
