<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Annotations\Annotator;
use Iliich246\YicmsCommon\Annotations\AnnotateInterface;
use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;
use Iliich246\YicmsCommon\Fields\Field;
use Iliich246\YicmsCommon\Fields\FieldsHandler;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\FieldsInterface;
use Iliich246\YicmsCommon\Fields\FieldReferenceInterface;
use Iliich246\YicmsCommon\Files\File;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Files\FilesHandler;
use Iliich246\YicmsCommon\Files\FilesInterface;
use Iliich246\YicmsCommon\Files\FilesReferenceInterface;
use Iliich246\YicmsCommon\Images\Image;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Images\ImagesHandler;
use Iliich246\YicmsCommon\Images\ImagesInterface;
use Iliich246\YicmsCommon\Images\ImagesReferenceInterface;
use Iliich246\YicmsCommon\Conditions\Condition;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;
use Iliich246\YicmsCommon\Conditions\ConditionsHandler;
use Iliich246\YicmsCommon\Conditions\ConditionsInterface;
use Iliich246\YicmsCommon\Conditions\ConditionsReferenceInterface;
use Iliich246\YicmsEssences\EssencesModule;

/**
 * Class AbstractTreeNode
 *
 * @property int $id
 * @property int $essence_id
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
 * @property Essences $essence
 * @property EssencesCategories[] $categories
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssencesRepresents extends ActiveRecord implements
    FieldsInterface,
    FieldReferenceInterface,
    FilesInterface,
    FilesReferenceInterface,
    ImagesInterface,
    ImagesReferenceInterface,
    ConditionsReferenceInterface,
    ConditionsInterface,
    FictiveInterface,
    SortOrderInterface,
    NonexistentInterface,
    AnnotateInterface,
    AnnotatorFileInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE   = 0;
    const SCENARIO_UPDATE   = 1;
    //this scenario needed only for generating field, file ... templates and references
    const SCENARIO_GENERATE = 2;

    /** @var integer for works with categories input */
    public $category;
    /** @var FieldsHandler instance of field handler object */
    private $fieldHandler;
    /** @var FilesHandler instance of file handler object */
    private $fileHandler;
    /** @var ImagesHandler instance of image handler object */
    private $imageHandler;
    /** @var ConditionsHandler instance of condition handler object */
    private $conditionHandler;
    /** @var Essences instance */
    private $essenceInstance;
    /** @var bool keeps state of fictive value */
    private $isFictive = false;
    /** @var EssencesCategories[]|null buffer of categories for this represent */
    private $categoriesBuffer = null;
    /** @var bool keep nonexistent state of represent */
    private $isNonexistent = false;
    /** @var string keeps name of nonexistent represent */
    private $nonexistentName;
    /** @var bool state of annotation necessity */
    private $needToAnnotate = true;
    /** @var Annotator instance */
    private $annotator = null;
    /** @var AnnotatorFileInterface instance */
    private static $parentFileAnnotator;
    /** @var array of exception words for magical getter/setter */
    protected static $annotationExceptionWords = [
        'isNewRecord',
        'dirtyAttributes',
        'scenario',
        'essence',
        'id',
        'essence_id',
        'represent_order',
        'editable',
        'visible',
        'system_route',
        'ruled_route',
        'field_reference',
        'file_reference',
        'image_reference',
        'condition_reference',
        'created_at',
        'updated_at'
    ];

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
            [
                [
                    'represent_order',
                    'editable',
                    'visible',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [
                [
                    'system_route',
                    'ruled_route',
                    'field_reference',
                    'file_reference',
                    'image_reference',
                    'condition_reference'
                ],
                'string',
                'max' => 255
            ],
            [
                'category', 'validateCategory'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->visible  = true;
        $this->editable = true;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'editable'     => 'Editable',
            'visible'      => 'Visible',
            'system_route' => 'System Route',
            'ruled_route'  => 'Ruled Route',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'editable', 'visible', 'category'
            ],
            self::SCENARIO_UPDATE => [
                'editable', 'visible','category'
            ],
            self::SCENARIO_DEFAULT  => [],
            self::SCENARIO_GENERATE => []
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * Validates category
     * @param $attribute
     * @param $params
     */
    public function validateCategory($attribute, $params)
    {
        if (!$this->hasErrors()) {
            //$this->addError($attribute, 'PENIS');
        }
    }

    /**
     * Return array of categories of this represent
     * @return EssencesCategories[]|[]
     * @throws EssencesException
     */
    public function getCategories()
    {
        if (!is_null($this->categoriesBuffer)) return $this->categoriesBuffer;

        $this->categoriesBuffer = [];

        foreach(EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id) as $categoryId) {
            $category = $this->getEssence()->getCategoryById($categoryId);

            if ($category)
                $this->categoriesBuffer[$category->id] = $category;
        }

        return $this->categoriesBuffer;
    }

    /**
     * Return one category of this represent
     * @return EssencesCategories
     * @throws EssencesException
     */
    public function getCategory()
    {
        if (!is_null($this->categoriesBuffer)) {
            reset($this->categoriesBuffer);
            return current($this->categoriesBuffer);
        }

        return current($this->getCategories());
    }

    /**
     * Return count of categories associated with this represent
     * @return int
     * @throws EssencesException
     */
    public function countCategories()
    {
        if (!is_null($this->categoriesBuffer)) return count($this->categoriesBuffer);

        return count($this->getCategories());
    }

    /**
     * Creates list of categories for create/update represent drop lists
     * @param bool $needNoCategoryItem
     * @return array
     * @throws EssencesException
     * @throws \Exception
     */
    public function getCategoriesForDropList($needNoCategoryItem = true)
    {
        $list = [];

        if ($needNoCategoryItem &&
            $this->scenario != self::SCENARIO_CREATE &&
            !EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id))
            $list[0] = EssencesModule::t('app', 'No category selected');

        /** @var EssencesCategories $category */
        foreach ($this->getEssence()->getCategories() as $category) {
            if ($this->scenario == self::SCENARIO_UPDATE &&
                $this->getEssence()->isMultipleCategories() &&
                in_array($category->id, EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id))) {
                continue;
            }

            if (!CommonModule::isUnderDev() &&
                !$this->getEssence()->isIntermediateCategories() &&
                $category->isChildren())
                continue;

            $category->offAnnotation();

            $list[$category->id] = $category->getNodeName();

            if (!CommonModule::isUnderDev()) continue;

            $devString = ' |DEV:';
            $devString .= ' id=' . $category->id;

            if (!$this->getEssence()->isIntermediateCategories() && $category->isChildren())
                $devString .= ' (only dev can use this)';

            $list[$category->id] .= $devString;
        }

        return $list;
    }

    /**
     * Returns true is for this represent category can be added
     * @return bool
     * @throws EssencesException
     */
    public function canAddCategory()
    {
        if (!CommonModule::isUnderDev() && !$this->editable) return false;

        $categoriesArray = EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id);

        if (!$this->getEssence()->isMultipleCategories() &&
            count($categoriesArray) > 0 &&
            !CommonModule::isUnderDev()) return false;

        if ($this->getEssence()->max_categories <= count($categoriesArray) &&
        !CommonModule::isUnderDev()) return false;

        return true;
    }

    /**
     * Add category to represent
     * @param EssencesCategories $category
     * @return bool
     * @throws EssencesException
     */
    public function addCategory(EssencesCategories $category)
    {
        if (!CommonModule::isUnderDev() && !$this->editable) return false;

        $categoriesArray = EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id);

        if (!$this->getEssence()->isMultipleCategories() && count($categoriesArray) > 0 && !CommonModule::isUnderDev()) return false;

        if ((int)$this->getEssence()->max_categories <= count($categoriesArray) && !CommonModule::isUnderDev()) return false;

//        if (!CommonModule::isUnderDev() &&
//            $this->getEssence()->max_categories != 0 &&
//            $this->getEssence()->max_categories < count($categoriesArray))
//            return false;

        if ($this->getEssence()->id != $category->getEssence()->id) return false;

        if (in_array($category->id, $categoriesArray)) return false;

        $middle = new EssenceRepresentToCategory();
        $middle->category_id  = $category->id;
        $middle->represent_id = $this->id;
        $middle->represent_order =
            EssenceRepresentToCategory::maxRepresentOrderInCategory($this->id , $category->id);

        if ($middle->save(false)) {
            EssenceRepresentToCategory::clearBufferForRepresent($this->id);
            $this->categoriesBuffer = null;
            return true;
        }

        return false;
    }

    /**
     * Return  true, if category can be deleted from represent
     * @param EssencesCategories $category
     * @return bool
     * @throws EssencesException
     */
    public function canDeleteCategory(EssencesCategories $category)
    {
        if ($this->getEssence()->id != $category->getEssence()->id) return false;

        $categoriesArray = EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id);

        if (CommonModule::isUnderDev()) return true;

        if (!in_array($category->id, $categoriesArray)) return false;

        if (count($categoriesArray) == 1) return false;

        return true;
    }

    /**
     * Deletes category from represent
     * @param EssencesCategories $category
     * @return bool
     * @throws EssencesException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteCategory(EssencesCategories $category)
    {
        if (!CommonModule::isUnderDev() && !$this->editable) return false;

        if ($this->getEssence()->id != $category->getEssence()->id) return false;

        $categoriesArray = EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id);

        if (!in_array($category->id, $categoriesArray)) return false;

        $middle = EssenceRepresentToCategory::find()->where([
            'category_id'  => $category->id,
            'represent_id' => $this->id,
        ])->one();

        if ($middle->delete()) {
            EssenceRepresentToCategory::clearBufferForRepresent($this->id);
            $this->categoriesBuffer = null;

            return true;
        }

        return false;
    }

    /**
     * Essences getter
     * @return Essences|null
     * @throws EssencesException
     */
    public function getEssence()
    {
        if ($this->essenceInstance) return $this->essenceInstance;

        $this->essenceInstance = Essences::getInstanceById($this->essence_id);

        return $this->essenceInstance;
    }

    /**
     * Essences setter
     * @param Essences $essence
     * @return void
     */
    public function setEssence(Essences $essence)
    {
        $this->essenceInstance = $essence;
    }

    /**
     * Returns name of represent in admin part
     * @return string
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function adminName()
    {
        if ($this->isNonexistent()) {
            if (CommonModule::isUnderDev()) return 'Try to output name of nonexistent category';

            return '';
        }

        $nameFormFieldId = $this->getEssence()->represent_form_name_field;

        if (!$nameFormFieldId) {

            if (CommonModule::isUnderDev())
                return 'ID = ' . $this->id . ' (DEV: represent has no selected name forming field)';

            return 'ID = ' . $this->id;
        }

        if (is_null($fieldTemplate = FieldTemplate::getInstanceById($nameFormFieldId))) {
            Yii::warning(
                "Can`t fetch for FieldTemplate with ID = " .  $nameFormFieldId, __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new EssencesException(
                    "YICMS_STRICT_MODE:
                Can`t fetch for FieldTemplate with ID = " .  $nameFormFieldId);
            }

            if (CommonModule::isUnderDev())
                return 'ID = ' . $this->id . ' (DEV: Can`t fetch for FieldTemplate with ID = )' . $nameFormFieldId;

            return 'ID = ' . $this->id;
        }

        $nameFormingField = $this->getField($fieldTemplate->program_name);

        if (!$nameFormingField->isTranslate()) {
            if (CommonModule::isUnderDev())
                return 'ID = ' . $this->id . ' (DEV: represent with empty name)';

            return 'ID = ' . $this->id . EssencesModule::t('app', ' (Represent has no name)');
        }

        if (CommonModule::isUnderDev())
            return  'ID = ' . $this->id . ' ' .  $this->getField($fieldTemplate->program_name);

        return (string)$this->getField($fieldTemplate->program_name);
    }

    /**
     * Returns name of represent
     * @return string
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function name()
    {
        if ($this->isNonexistent()) {
            if (CommonModule::isUnderDev()) return 'Try to output name of nonexistent category';

            return '';
        }

        $nameFormFieldId = $this->getEssence()->represent_form_name_field;

        if (!$nameFormFieldId) {
            if (CommonModule::isUnderDev() && defined('YICMS_ALERTS'))
                return 'ID = ' . $this->id . ' (DEV: represent has no selected name forming field)';

            return '';
        }

        if (is_null($fieldTemplate = FieldTemplate::getInstanceById($nameFormFieldId))) {
            Yii::warning(
                "Can`t fetch for FieldTemplate with ID = " .  $nameFormFieldId, __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new EssencesException(
                    "YICMS_STRICT_MODE:
                Can`t fetch for FieldTemplate with ID = " .  $nameFormFieldId);
            }

            if (CommonModule::isUnderDev() && defined('YICMS_ALERTS'))
                return 'ID = ' . $this->id . ' (DEV: Can`t fetch for FieldTemplate with ID = )' . $nameFormFieldId;

            return '';
        }

        $nameFormingField = $this->getField($fieldTemplate->program_name);

        if (!$nameFormingField->isTranslate()) {
            if (CommonModule::isUnderDev() && defined('YICMS_ALERTS'))
                return 'ID = ' . $this->id . ' (DEV: represent with empty name)';

            if (CommonModule::isUnderAdmin() && defined('YICMS_ALERTS'))
                return 'Represent with empty name';

            return '';
        }

        if (CommonModule::isUnderDev() && defined('YICMS_ALERTS'))
            return  'ID = ' . $this->id . ' ' .  $this->getField($fieldTemplate->program_name);

        return (string)$this->getField($fieldTemplate->program_name);
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->essence_id      = $this->essence->id;
            $this->represent_order = $this->maxOrder();

            if (!parent::save()) {
                Yii::warning('Can`t save represent',__METHOD__);
                if (defined('YICMS_STRICT'))
                    throw new EssencesException("Can`t save represent");

                return false;
            }

            if (!$this->getEssence()->is_multiple_categories ||
                $this->getEssence()->is_categories) {

                $middle = new EssenceRepresentToCategory();
                $middle->category_id     = $this->category;
                $middle->represent_id    = $this->id;
                $middle->essence_id      = $this->getEssence()->id;
                $middle->represent_order =
                    EssenceRepresentToCategory::maxRepresentOrderInCategory($this->id ,$this->category);

                $middle->save('false');
            }

            return true;
        }

        if ($this->scenario == self::SCENARIO_UPDATE) {
            if (!$this->getEssence()->is_multiple_categories) {
                $middleArray =
                    EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id);

                if (!count($middleArray)) {
                    $middle = new EssenceRepresentToCategory();
                    $middle->category_id     = $this->category;
                    $middle->represent_id    = $this->id;
                    $middle->essence_id      = $this->getEssence()->id;
                    $middle->represent_order =
                        EssenceRepresentToCategory::maxRepresentOrderInCategory($this->id ,$this->category);

                    $middle->save('false');
                } else {
                    reset($middleArray);
                    $firstCategoryId = current($middleArray);

                    /** @var EssenceRepresentToCategory $middle */
                    $middle = EssenceRepresentToCategory::find()->where([
                        'category_id'  => $firstCategoryId,
                        'represent_id' => $this->id,
                    ])->one();

                    if ($middle) {
                        if ($middle->category_id != $this->category) {
                            $middle->category_id     = $this->category;
                            $middle->essence_id      = $this->getEssence()->id;
                            $middle->represent_order =
                                EssenceRepresentToCategory::maxRepresentOrderInCategory($this->id ,$this->category);

                            $middle->save(false);
                        }

                    } else {
                        Yii::warning("Can`t fetch middle represent to category", __METHOD__);

                        if (defined('YICMS_STRICT'))
                            throw new EssencesException("Can`t fetch middle represent to category");
                    }
                }
            }

            return parent::save();
        }

        if ($this->scenario == self::SCENARIO_GENERATE)
            return parent::save();

        return false;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete()
    {
        /** @var FieldTemplate[] $fieldTemplates */
        $fieldTemplates = FieldTemplate::find()->where([
            'field_template_reference' => $this->getEssence()->getRepresentFieldTemplateReference(),
        ])->all();

        foreach($fieldTemplates as $fieldTemplate) {
            /** @var Field $field */
            $field = Field::find()->where([
                'common_fields_template_id' => $fieldTemplate->id,
                'field_reference'           => $this->field_reference,
            ])->one();

            if ($field) $field->delete();
        }

        /** @var ConditionTemplate[] $conditionTemplates */
        $conditionTemplates = ConditionTemplate::find()->where([
            'condition_template_reference' => $this->getEssence()->getRepresentConditionTemplateReference(),
        ])->all();

        foreach($conditionTemplates as $conditionTemplate) {
            /** @var Condition $condition */
            $condition = Condition::find()->where([
                'common_condition_template_id' => $conditionTemplate->id,
                'condition_reference'          => $this->condition_reference
            ])->one();

            if ($condition) $condition->delete();
        }

        /** @var FilesBlock[] $fileBlocks */
        $fileBlocks = FilesBlock::find()->where([
            'file_template_reference' => $this->getEssence()->getRepresentFileTemplateReference()
        ])->all();

        foreach($fileBlocks as $fileBlock) {
            /** @var File $file */
            $file = File::find()->where([
                'common_files_template_id' => $fileBlock->id,
                'file_reference'           => $this->file_reference
            ])->one();

            if ($file) $file->delete();
        }

        /** @var ImagesBlock[] $imagesBlocks */
        $imagesBlocks = ImagesBlock::find()->where([
            'image_template_reference' => $this->getEssence()->getRepresentImageTemplateReference()
        ])->all();

        foreach($imagesBlocks as $imageBlock) {
            /** @var Image $image */
            $image = Image::find()->where([
                'common_images_templates_id' => $imageBlock->id,
                'image_reference'            => $this->image_reference
            ])->one();

            if ($image) $image->delete();
        }

        /** @var EssenceRepresentToCategory[] $representToCategories */
        $representToCategories = EssenceRepresentToCategory::find()->where([
            'represent_id' => $this->id,
        ])->all();

        foreach ($representToCategories as $representCategory)
            $representCategory->delete();

        return parent::delete();
    }

    /**
     * Delete represent only without any constrains
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function simpleDelete()
    {
        return parent::delete();
    }

    /**
     * Proxy method name() to magical __toString()
     * @return string
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function __toString()
    {
        return (string)$this->name();
    }

    /**
     * Magical get method for use object annotations
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (in_array($name, self::$annotationExceptionWords))
            return parent::__get($name);

        if ($this->scenario === self::SCENARIO_CREATE)
            return parent::__get($name);

        if (strpos($name, 'field_') === 0) {
            if ($this->isField(substr($name, 6)))
                return $this->getFieldHandler()->getField(substr($name, 6));

            return parent::__get($name);
        }

        if (strpos($name, 'file_') === 0) {
            if ($this->isFileBlock(substr($name, 5)))
                return $this->getFileHandler()->getFileBlock(substr($name, 5), $this->id);

            return parent::__get($name);
        }

        if (strpos($name, 'image_') === 0) {
            if ($this->isImageBlock(substr($name, 6)))
                return $this->getImagesHandler()->getImageBlock(substr($name, 6), $this->id);

            return parent::__get($name);
        }

        if (strpos($name, 'condition_') === 0) {
            if ($this->isCondition(substr($name, 10)))
                return $this->getConditionsHandler()->getCondition(substr($name, 10));

            return parent::__get($name);
        }

        if ($this->getFieldHandler()->isField($name))
            return $this->getFieldHandler()->getField($name);

        if ($this->getFileHandler()->isFileBlock($name))
            return $this->getFileHandler()->getFileBlock($name, $this->id);

        if ($this->getImagesHandler()->isImageBlock($name))
            return $this->getImagesHandler()->getImageBlock($name, $this->id);

        if ($this->getConditionsHandler()->isCondition($name))
            return $this->getConditionsHandler()->getCondition($name);

        return parent::__get($name);
    }

    /**
     * Associate field category with represents category, used for input fields
     * @return int
     */
    public function loadCategoryField()
    {
        $categories = EssenceRepresentToCategory::getCategoriesArrayForRepresent($this->id);

        if (!$categories)
            return $this->category = 0;

        reset($categories);

        return $this->category = current($categories);
    }

    /**
     * @inheritdoc
     */
    public function getFieldHandler()
    {
        if (!$this->fieldHandler)
            $this->fieldHandler = new FieldsHandler($this);

        return $this->fieldHandler;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \Exception
     */
    public function getField($name)
    {
        if ($this->isFictive()) {
            $fictiveField = new Field();
            $fictiveField->setFictive();

            /** @var FieldTemplate $template */
            $template = FieldTemplate::getInstance($this->getEssence()->getRepresentFieldTemplateReference(), $name);
            $fictiveField->setTemplate($template);

            return $fictiveField;
        }

        return $this->getFieldHandler()->getField($name);
    }

    /**
     * @inheritdoc
     */
    public function isField($name)
    {
        return $this->getFieldHandler()->isField($name);
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFieldTemplateReference()
    {
        $essence = $this->getEssence();

        if (!$essence->field_template_reference_represent) {
            $essence->field_template_reference_represent = FieldTemplate::generateTemplateReference();

            $oldScenario = $this->scenario;
            $this->scenario = self::SCENARIO_GENERATE;
            $this->save(false);
            $this->scenario = $oldScenario;
        }

        return $essence->field_template_reference_represent;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFieldReference()
    {
        if (!$this->field_reference) {
            $this->field_reference = Field::generateReference();

            $oldScenario = $this->scenario;
            $this->scenario = self::SCENARIO_GENERATE;
            $this->save(false);
            $this->scenario = $oldScenario;
        }

        return $this->field_reference;
    }

    /**
     * @inheritdoc
     */
    public function getFileHandler()
    {
        if (!$this->fileHandler)
            $this->fileHandler = new FilesHandler($this);

        return $this->fileHandler;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFileReference()
    {
        if (!$this->file_reference) {
            $this->file_reference = File::generateReference();

            $oldScenario = $this->scenario;
            $this->scenario = self::SCENARIO_GENERATE;
            $this->save(false);
            $this->scenario = $oldScenario;
        }

        return $this->file_reference;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFileTemplateReference()
    {
        $essence = $this->essence;

        if (!$essence->file_template_reference_represent) {
            $essence->file_template_reference_represent = FilesBlock::generateTemplateReference();

            $oldScenario = $this->scenario;
            $this->scenario = self::SCENARIO_GENERATE;
            $this->save(false);
            $this->scenario = $oldScenario;
        }

        return $essence->file_template_reference_represent;
    }

    /**
     * @inheritdoc
     */
    public function getFileBlock($name)
    {
        return $this->getFileHandler()->getFileBlock($name, $this->id);
    }

    /**
     * @inheritdoc
     */
    public function isFileBlock($name)
    {
        $this->getFileHandler()->isFileBlock($name, $this->id);
    }

    /**
     * @inheritdoc
     */
    public function getImagesHandler()
    {
        if (!$this->imageHandler)
            $this->imageHandler = new ImagesHandler($this);

        return $this->imageHandler;
    }

    /**
     * @inheritdoc
     */
    public function getImageBlock($name)
    {
        return $this->getImagesHandler()->getImageBlock($name, $this->id);
    }

    /**
     * @inheritdoc
     */
    public function isImageBlock($name)
    {
        return $this->getImagesHandler()->isImageBlock($name, $this->id);
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getImageTemplateReference()
    {
        $essence = $this->essence;

        if (!$essence->image_template_reference_represent) {
            $essence->image_template_reference_represent = ImagesBlock::generateTemplateReference();

            $oldScenario = $this->scenario;
            $this->scenario = self::SCENARIO_GENERATE;
            $this->save(false);
            $this->scenario = $oldScenario;
        }

        return $essence->image_template_reference_represent;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getImageReference()
    {
        if (!$this->image_reference) {
            $this->image_reference = Image::generateReference();

            $oldScenario = $this->scenario;
            $this->scenario = self::SCENARIO_GENERATE;
            $this->save(false);
            $this->scenario = $oldScenario;
        }

        return $this->image_reference;
    }

    /**
     * @inheritdoc
     */
    public function getConditionsHandler()
    {
        if (!$this->conditionHandler)
            $this->conditionHandler = new ConditionsHandler($this);

        return $this->conditionHandler;
    }

    /**
     * @inheritdoc
     */
    public function getCondition($name)
    {
        return $this->getConditionsHandler()->getCondition($name);
    }

    /**
     * @inheritdoc
     */
    public function isCondition($name)
    {
        return $this->getConditionsHandler()->isCondition($name);
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getConditionTemplateReference()
    {
        $essence = $this->essence;

        if (!$essence->condition_template_reference_represent) {
            $essence->condition_template_reference_represent = ConditionTemplate::generateTemplateReference();

            $oldScenario = $this->scenario;
            $this->scenario = self::SCENARIO_GENERATE;
            $this->save(false);
            $this->scenario = $oldScenario;
        }

        return $essence->condition_template_reference_represent;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getConditionReference()
    {
        if (!$this->condition_reference) {
            $this->condition_reference = Condition::generateReference();

            $oldScenario = $this->scenario;
            $this->scenario = self::SCENARIO_GENERATE;
            $this->save(false);
            $this->scenario = $oldScenario;
        }

        return $this->condition_reference;
    }

    /**
     * Up represent order in category group
     * @param $categoryId
     * @return bool
     */
    public function upInCategory($categoryId)
    {
        return EssenceRepresentToCategory::upRepresentOrderInCategory($this->id, $categoryId);
    }

    /**
     * Down represent order in category group
     * @param $categoryId
     * @return bool
     */
    public function downInCategory($categoryId)
    {
        return EssenceRepresentToCategory::downRepresentOrderInCategory($this->id, $categoryId);
    }

    /**
     * Returns true if represent can up his order in category group
     * @param $categoryId
     * @return bool
     */
    public function canUpInCategory($categoryId)
    {
        return EssenceRepresentToCategory::canUpRepresentOrderInCategory($this->id, $categoryId);
    }

    /**
     * Returns true if represent can down his order in category group
     * @param $categoryId
     * @return bool
     */
    public function canDownInCategory($categoryId)
    {
        return EssenceRepresentToCategory::canDownRepresentOrderInCategory($this->id, $categoryId);
    }

//    public function maxOrderInCategory($categoryId)
//    {
//        return EssenceRepresentToCategory::maxRepresentOrderInCategory($this->id, $categoryId);
//    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'essence_id' => $this->essence_id,
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
        $this->scenario = self::SCENARIO_UPDATE;
    }

    /**
     * @inheritdoc
     */
    public function getOrderAble()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFictive()
    {
        $this->isFictive = true;
    }

    /**
     * @inheritdoc
     */
    public function clearFictive()
    {
        $this->isFictive = false;
    }

    /**
     * @inheritdoc
     */
    public function isFictive()
    {
        return $this->isFictive;
    }

    /**
     * @inheritdoc
     */
    public function isNonexistent()
    {
        return $this->isNonexistent;
    }

    /**
     * @inheritdoc
     */
    public function setNonexistent()
    {
        $this->isNonexistent = true;
    }

    /**
     * @inheritdoc
     */
    public function getNonexistentName()
    {
        return $this->nonexistentName;
    }

    /**
     * @inheritdoc
     */
    public function setNonexistentName($name)
    {
        $this->nonexistentName = $name;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \ReflectionException
     */
    public function annotate()
    {
        FieldTemplate::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            FieldTemplate::getAnnotationsStringArray($this->getFieldTemplateReference())
        );

        FilesBlock::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            FilesBlock::getAnnotationsStringArray($this->getFileTemplateReference())
        );

        ImagesBlock::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            ImagesBlock::getAnnotationsStringArray($this->getImageTemplateReference())
        );

        ConditionTemplate::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            ConditionTemplate::getAnnotationsStringArray($this->getConditionTemplateReference())
        );

        $this->getAnnotator()->finish();
    }

    /**
     * @inheritdoc
     */
    public function offAnnotation()
    {
        $this->needToAnnotate = false;
    }

    /**
     * @inheritdoc
     */
    public function onAnnotation()
    {
        $this->needToAnnotate = true;
    }

    /**
     * @inheritdoc
     */
    public function isAnnotationActive()
    {
        return $this->needToAnnotate;
    }

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function getAnnotator()
    {
        if (!is_null($this->annotator)) return $this->annotator;

        $this->annotator = new Annotator();
        $this->annotator->setAnnotatorFileObject($this);
        $this->annotator->prepare();

        return $this->annotator;
    }

    /**
     * Sets parent file annotator
     * @param AnnotatorFileInterface $fileAnnotator
     */
    public static function setParentFileAnnotator(AnnotatorFileInterface $fileAnnotator)
    {
        self::$parentFileAnnotator = $fileAnnotator;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationFileName()
    {
        return ucfirst(mb_strtolower($this->essence->program_name)) . 'Represent';
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationFilePath()
    {
        $path = $this->essence->getAnnotationFilePath();
        $path .= '/' . $this->getAnnotationFileName();

        return $path;
    }

    /**
     * @inheritdoc
     */
    public function getExtendsUseClass()
    {
        return 'Iliich246\YicmsEssences\Base\EssencesRepresents';
    }

    /**
     * @inheritdoc
     */
    public function getExtendsClassName()
    {
        return 'EssencesRepresents';
    }

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public static function getAnnotationTemplateFile()
    {
        $class = new \ReflectionClass(self::class);
        return dirname($class->getFileName())  . '/annotations/essence-represents.php';
    }

    /**
     * @inheritdoc
     */
    public static function getAnnotationFileNamespace()
    {
        return self::$parentFileAnnotator->getAnnotationFileNamespace() . '\\'
            . self::$parentFileAnnotator->getAnnotationFileName() . 'Represent';
    }
}
