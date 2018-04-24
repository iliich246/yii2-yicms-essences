<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class EssencesNamesTranslatesDb
 *
 * @property int $id
 * @property int $essence_id
 * @property int $common_language_id
 * @property string $name
 * @property string $description
 *
 * @property Essences $essence
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssencesNamesTranslatesDb extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%essences_names_translates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['essence_id', 'common_language_id'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['common_language_id'], 'exist', 'skipOnError' => true, 'targetClass' => LanguagesDb::className(), 'targetAttribute' => ['common_language_id' => 'id']],
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
            'common_language_id' => 'Common Language ID',
            'name' => 'Name',
            'description' => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEssence()
    {
        return $this->hasOne(Essences::className(), ['id' => 'essence_id']);
    }
}
