<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\behaviors\TimestampBehavior;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
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
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class EssencesCategories
 *
 * @property int $id
 * @property int $essence_id
 * @property int $parent_id
 * @property int $editable
 * @property int $visible
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
class EssencesCategories extends AbstractTreeNode implements
    FieldsInterface,
    FieldReferenceInterface,
    FilesInterface,
    FilesReferenceInterface,
    ImagesInterface,
    ImagesReferenceInterface,
    ConditionsReferenceInterface,
    ConditionsInterface,
    FictiveInterface,
    SortOrderInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    /** @var FieldsHandler instance of field handler object */
    private $fieldHandler;
    /** @var FilesHandler instance of file handler object */
    private $fileHandler;
    /** @var ImagesHandler instance of image handler object */
    private $imageHandler;
    /** @var ConditionsHandler instance of condition handler object */
    private $conditionHandler;
    /** @var bool keeps state of fictive value */
    private $isFictive = false;
    /** @var bool needed for delete sequence. Used for subcategories mark */
    private $markedAsSubcategory = false;

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
    public function init()
    {
        parent::init();

        if ($this->isNewRecord) {
            $this->visible   = true;
            $this->editable  = true;
            $this->parent_id = 0;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'essence_id',
                    'parent_id',
                    'editable',
                    'visible',
                    'category_order',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ], [
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
                'parent_id', 'validateParent'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'parent_id', 'editable', 'visible',
            ],
            self::SCENARIO_UPDATE => [
                'parent_id', 'editable', 'visible',
            ],
            self::SCENARIO_DEFAULT => [],
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent_id' => 'Parent ID',
            'editable'  => 'Editable',
            'visible'   => 'Visible',
        ];
    }

    /**
     *
     * Validates the parent of category.
     * This method serves as the inline validation for category parent id.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     * @throws EssencesException
     */
    public function validateParent($attribute, $params)
    {
        if (!$this->hasErrors()) {
//            if (!CommonModule::isUnderDev() &&
//                ($this->getEssence()->count_subcategories > 0) &&
//                ($this->getLevel() > $this->getEssence()->count_subcategories - 2)) {
//
//                $this->addError($attribute, EssencesModule::t('app', 'Wrong parent category'));
//            }
        }
    }

    /**
     * Essence getter
     * @return Essences|AbstractTreeNodeCollection|null
     * @throws EssencesException
     */
    public function getEssence()
    {
        if ($this->collection) return $this->collection;

        return $this->collection = Essences::getInstance($this->essence_id);
    }

    /**
     * Essence setter
     * @param Essences $essence
     */
    public function setEssence(Essences $essence)
    {
        $this->setCollection($essence);
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->essence_id     = $this->essence->id;
            $this->category_order = $this->maxOrder();
        }

        if ($this->scenario == self::SCENARIO_UPDATE) {
            if ($this->oldAttributes['parent_id'] != $this->parent_id) {
                $this->category_order = $this->maxOrder();
            }
        }

        return parent::save();
    }

    /**
     * Creates list of categories for create/update category drop lists
     * @return array
     * @throws EssencesException
     */
    public function getCategoriesForDropList()
    {
        $list = [];

        $list[0] = EssencesModule::t('app', 'Root category');

        $tree = $this->getEssence()->getCategories();

        /** @var EssencesCategories $node */
        foreach($tree as $node) {
            if ($this->scenario == self::SCENARIO_UPDATE && $node->id == $this->id)
                continue;

            if (!CommonModule::isUnderDev() &&
                ($this->getEssence()->count_subcategories > 0) &&
                ($node->getLevel() > $this->getEssence()->count_subcategories - 2)
            )
                continue;

            $levelString = '';
            for ($i = 0; $i < $node->getLevel(); $i++)
                $levelString .= '-';

            $list[$node->id] = $levelString . $node->getNodeName();

            if (!CommonModule::isUnderDev()) continue;

            $devString = ' |DEV:';
            $devString .= ' id=' . $node->id;

            if ($this->getEssence()->count_subcategories > 0 &&
                $node->getLevel() > $this->getEssence()->count_subcategories - 2)
                $devString .= ' (only dev can use this)';

            $list[$node->id] .= $devString;
        }

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        if ($this->isChildren()) {
            foreach ($this->getChildren() as $children) {

                /** @var EssencesCategories $subCategory */
                $subCategory = $children['node'];
                $subCategory->markedAsSubcategory = true;
                $subCategory->delete();
            }
        }

        foreach ($this->getRepresents() as $represent) {

            $represent->deleteCategory($this);

            if (!$represent->countCategories()) continue;

            if ($this->getEssence()->delete_represents)
                $represent->delete();
        }

        if (!$this->markedAsSubcategory) return parent::delete();

        if ($this->getEssence()->delete_subcategories)
            return parent::delete();

        $this->visible   = false;
        $this->parent_id = 0;
        $this->category_order = $this->maxOrder();

        return $this->save(false);
    }

    /**
     * Returns name of category via name forming field
     * @return string
     * @throws EssencesException
     * @throws \Exception
     */
    public function name()
    {
        $nameFormFieldId = $this->getEssence()->category_form_name_field;

        if (!$nameFormFieldId) {
            return $this->id;
        }

        /** @var FieldTemplate $fieldTemplate */
        $fieldTemplate = FieldTemplate::getInstanceById($nameFormFieldId);

        if (!$fieldTemplate) {
            //TODO: error message
            return $this->id;
        }

        return $this->getField($fieldTemplate->program_name) . ' (' . $this->id . ' )' . 'p=' . $this->parent_id;
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     */
    public function getNodeName(LanguagesDb $language = null)
    {
        return static::name();
    }

    /**
     * @return EssencesRepresents[]
     */
    public function getRepresents()
    {
        return $this->getRepresentsQuery()->all();
    }

    /**
     * Return ActiveQuery for find all represents for this category
     * @param int $sort
     * @return ActiveQuery
     */
    public function getRepresentsQuery($sort = SORT_ASC)
    {
        return  EssencesRepresents::find()
            ->leftJoin('{{%essences_category_represent}}', '{{%essences_category_represent}}.`represent_id` = {{%essences_represents}}.`id`')
            ->where(['{{%essences_category_represent}}.category_id' => $this->id])
            ->orderBy(['{{%essences_category_represent}}.represent_order' => $sort]);
    }

    /**
     * Returns count of represents in this category
     * @return int
     */
    public function countRepresents()
    {
        return count(EssenceRepresentToCategory::getRepresentsArrayForCategory($this->id));
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
     * @throws \Exception
     */
    public function getField($name)
    {
        if ($this->isFictive()) {
            $fictiveField = new Field();
            $fictiveField->setFictive();

            /** @var FieldTemplate $template */
            $template = FieldTemplate::getInstance($this->getEssence()->getCategoryFieldTemplateReference(), $name);
            $fictiveField->setTemplate($template);
        }

        return $this->getFieldHandler()->getField($name);
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFieldTemplateReference()
    {
        $essence = $this->getEssence();

        if (!$essence->field_template_reference_category) {
            $essence->field_template_reference_category = FieldTemplate::generateTemplateReference();
            $essence->save(false);
        }

        return $essence->field_template_reference_category;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFieldReference()
    {
        if (!$this->field_reference) {
            $this->field_reference = Field::generateReference();
            $this->save(false);
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFileReference()
    {
        if (!$this->file_reference) {
            $this->file_reference = File::generateReference();
            $this->save(false);
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
        $essence = $this->getEssence();

        if (!$essence->file_template_reference_category) {
            $essence->file_template_reference_category = FilesBlock::generateTemplateReference();
            $essence->save(false);
        }

        return $essence->file_template_reference_category;
    }

    /**
     * @inheritdoc
     */
    public function getFileBlock($name)
    {
        return $this->getFileHandler()->getFileBlock($name);
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
        return $this->getImagesHandler()->getImageBlock($name);
    }

    /**
     * @inheritdoc
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getImageTemplateReference()
    {
        $essence = $this->getEssence();

        if (!$essence->image_template_reference_category) {
            $essence->image_template_reference_category = ImagesBlock::generateTemplateReference();
            $essence->save(false);
        }

        return $essence->image_template_reference_category;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getImageReference()
    {
        if (!$this->image_reference) {
            $this->image_reference = Image::generateReference();
            $this->save(false);
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
     * @throws EssencesException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getConditionTemplateReference()
    {
        $essence = $this->getEssence();

        if (!$essence->condition_template_reference_category) {
            $essence->condition_template_reference_category = ConditionTemplate::generateTemplateReference();
            $essence->save(false);
        }

        return $essence->condition_template_reference_category;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getConditionReference()
    {
        if (!$this->condition_reference) {
            $this->condition_reference = Condition::generateReference();
            $this->save(false);
        }

        return $this->condition_reference;
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'essence_id' => $this->essence_id,
            'parent_id'  => $this->parent_id,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'category_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->category_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->category_order = $value;
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
    public function getSortFieldName()
    {
        return 'category_order';
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
}
