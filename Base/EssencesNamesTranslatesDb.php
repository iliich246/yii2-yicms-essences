<?php

namespace Iliich246\YicmsEssences\Base;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class EssencesNamesTranslatesDb
 *
 * @property int $id
 * @property int $essence_id
 * @property int $common_language_id
 * @property string $name
 * @property string $description
 * @property string $category_name
 * @property string $represent_name
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssencesNamesTranslatesDb extends ActiveRecord
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
            [['name', 'description', 'category_name', 'represent_name'], 'string', 'max' => 255],
            [['common_language_id'], 'exist', 'skipOnError' => true, 'targetClass' => LanguagesDb::className(), 'targetAttribute' => ['common_language_id' => 'id']],
            [['essence_id'], 'exist', 'skipOnError' => true, 'targetClass' => Essences::className(), 'targetAttribute' => ['essence_id' => 'id']],
        ];
    }
}
