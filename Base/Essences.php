<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;

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

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

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
    public function init()
    {
        $this->visible                = true;
        $this->editable               = true;
        $this->is_categories          = true;
        $this->count_subcategories    = 0;
        $this->is_multiple_categories = true;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['program_name', 'required', 'message' => 'Obligatory input field'],
            ['program_name', 'string', 'max' => '50', 'tooLong' => 'Program name must be less than 50 symbols'],
            ['program_name', 'validateProgramName'],
            [['is_categories', 'count_subcategories', 'is_multiple_categories', 'essence_order'], 'integer'],
            [['editable', 'visible'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'program_name', 'is_categories', 'editable', 'visible', 'is_multiple_categories'
            ],
            self::SCENARIO_UPDATE => [
                'program_name', 'is_categories', 'editable', 'visible', 'is_multiple_categories'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'program_name' => 'Program Name',
            'is_categories' => 'Is Categories',
            'count_subcategories' => 'Count Subcategories  (0 - infinity)',
            'is_multiple_categories' => 'Is Multiple Categories',
        ];
    }

    /**
     * Returns instance of essence by his name
     * @param $programName
     * @return array|Essences|null|ActiveRecord
     * @throws EssencesException
     */
    public static function getByName($programName)
    {
        //TODO: makes buffer of essences
        /** @var self $essence */
        $essence = self::find()
            ->where(['program_name' => $programName])
            ->one();

        if ($essence) return $essence;

        Yii::error("Сan not find essence with name " . $programName, __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new EssencesException('Сan not find essence with name ' . $programName);
        }

        return new self();
    }

    /**
     * Validates the program name.
     * This method serves as the inline validation for page program name.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateProgramName($attribute, $params)
    {
        if (!$this->hasErrors()) {

            $essencesQuery = self::find()->where(['program_name' => $this->program_name]);

            if ($this->scenario == self::SCENARIO_UPDATE)
                $essencesQuery->andWhere(['not in', 'program_name', $this->getOldAttribute('program_name')]);

            $essences = $essencesQuery->all();
            if ($essences)$this->addError($attribute, 'Essence with same name already exist in system');
        }
    }

    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        if ($this->hasErrors()) return;

        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->field_template_reference_category  = FieldTemplate::generateTemplateReference();
            $this->field_template_reference_represent = FieldTemplate::generateTemplateReference();

            $this->file_template_reference_category  = FilesBlock::generateTemplateReference();
            $this->file_template_reference_represent = FilesBlock::generateTemplateReference();

            $this->image_template_reference_category  = ImagesBlock::generateTemplateReference();
            $this->image_template_reference_represent =  ImagesBlock::generateTemplateReference();

            $this->condition_template_reference_category  = ConditionTemplate::generateTemplateReference();
            $this->condition_template_reference_represent = ConditionTemplate::generateTemplateReference();
        }
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->scenario == self::SCENARIO_CREATE)
            $this->essence_order = $this->maxOrder();

        return parent::save(false);
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        //return parent::delete();
        return true;
    }

    /**
     * Return true if essence has any constraints
     * @return bool
     */
    public function isConstraints()
    {
        //TODO: make this method
        return true;
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
     * Returns field_template_reference_category
     * @return string
     */
    public function getCategoryFieldTemplateReference()
    {
        if (!$this->field_template_reference_category) {
            $this->field_template_reference_category = FieldTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->field_template_reference_category;
    }

    /**
     * Returns file_template_reference_category
     * @return string
     */
    public function getCategoryFileTemplateReference()
    {
        if (!$this->file_template_reference_category) {
            $this->file_template_reference_category = FilesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->file_template_reference_category;
    }

    /**
     * Returns image_template_reference_category
     * @return string
     */
    public function getCategoryImageTemplateReference()
    {
        if (!$this->image_template_reference_category) {
            $this->image_template_reference_category = ImagesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->image_template_reference_category;
    }

    /**
     * Returns condition_template_reference_category
     * @return string
     */
    public function getCategoryConditionTemplateReference()
    {
        if (!$this->condition_template_reference_category) {
            $this->condition_template_reference_category = ConditionTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->condition_template_reference_category;
    }

    /**
     * Returns field_template_reference_represent
     * @return string
     */
    public function getRepresentFieldTemplateReference()
    {
        if (!$this->field_template_reference_represent) {
            $this->field_template_reference_represent = FieldTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->field_template_reference_represent;
    }

    /**
     * Returns file_template_reference_represent
     * @return string
     */
    public function getRepresentFileTemplateReference()
    {
        if (!$this->file_template_reference_represent) {
            $this->file_template_reference_represent = FilesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->file_template_reference_represent;
    }

    /**
     * Returns image_template_reference_represent
     * @return string
     */
    public function getRepresentImageTemplateReference()
    {
        if (!$this->image_template_reference_represent) {
            $this->image_template_reference_represent = ImagesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->image_template_reference_represent;
    }

    /**
     * Returns condition_template_reference_represent
     * @return string
     */
    public function getRepresentConditionTemplateReference()
    {
        if (!$this->condition_template_reference_represent) {
            $this->condition_template_reference_represent = ConditionTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->condition_template_reference_represent;
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
        $this->scenario = self::SCENARIO_UPDATE;
    }

    /**
     * @inheritdoc
     */
    public function getOrderAble()
    {
        return $this;
    }
}
