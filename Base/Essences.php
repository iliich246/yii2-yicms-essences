<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;
use Iliich246\YicmsEssences\EssencesModule;

/**
 * Class Essences
 *
 * @property int $id
 * @property string $program_name
 * @property int $is_categories
 * @property int $categories_create_by_user
 * @property int $count_subcategories
 * @property int $is_multiple_categories
 * @property int $is_intermediate_categories
 * @property int $max_categories
 * @property int $essence_order
 * @property bool $editable
 * @property bool $visible
 * @property int $category_form_name_field
 * @property int $represent_form_name_field
 * @property int $represents_pagination_count
 * @property bool $delete_subcategories
 * @property bool $delete_represents
 * @property string $field_template_reference_category
 * @property string $file_template_reference_category
 * @property string $image_template_reference_category
 * @property string $condition_template_reference_category
 * @property string $field_template_reference_represent
 * @property string $file_template_reference_represent
 * @property string $image_template_reference_represent
 * @property string $condition_template_reference_represent
 *
 * @property EssencesCategories[] $categories
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class Essences extends AbstractTreeNodeCollection implements
    SortOrderInterface,
    NonexistentInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    /** @var self[] buffer array */
    private static $essencesBuffer = [];
    /** @var bool if true standard fields template for categories will be created on essence create */
    public $createCategoriesStandardFields = true;
    /** @var bool if true standard fields template for represents will be created on essence create */
    public $createRepresentsStandardFields = true;
    /** @var bool if true seo fields template for categories will be created on essence create */
    public $createCategoriesSeoFields      = true;
    /** @var bool if true seo fields template for represents will be created on essence create */
    public $createRepresentSeoFields       = true;
    /** @var EssencesRepresents[] buffer*/
    private $representsBuffer = [];
    /** @var bool when true all represents was fetched from db */
    private $isAllRepresentsFetched = false;
    /** @var EssencesNamesTranslatesDb[] buffer for language */
    private $essenceNameTranslations;
    /** @var bool keep nonexistent state of essence */
    private $isNonexistent = false;
    /** @var string keeps name of nonexistent essence */
    private $nonexistentName;

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
        $this->visible                        = true;
        $this->editable                       = true;
        $this->is_categories                  = true;
        $this->categories_create_by_user      = true;
        $this->count_subcategories            = 0;
        $this->is_multiple_categories         = false;
        $this->is_intermediate_categories     = false;
        $this->max_categories                 = 0;
        $this->represents_pagination_count    = 50;
        $this->delete_subcategories           = true;
        $this->delete_represents              = false;
        $this->createRepresentsStandardFields = true;
        $this->createRepresentSeoFields       = true;
        $this->createCategoriesStandardFields = true;
        $this->createCategoriesSeoFields      = true;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'program_name', 'required', 'message' => 'Obligatory input field'
            ],
            [
                'program_name', 'string', 'max' => '50', 'tooLong' => 'Program name must be less than 50 symbols'
            ],
            [
                'program_name', 'validateProgramName'
            ],
            [
                ['is_categories', 'count_subcategories', 'essence_order', 'max_categories'],
                'integer'
            ],
            [
                [
                    'editable',
                    'visible',
                    'is_multiple_categories',
                    'is_intermediate_categories',
                    'categories_create_by_user',
                    'delete_subcategories',
                    'delete_represents',
                    'createRepresentsStandardFields',
                    'createCategoriesStandardFields',
                    'createRepresentSeoFields',
                    'createCategoriesSeoFields',
                ],
                'boolean'
            ],
            [
                [
                    'category_form_name_field',
                    'represent_form_name_field',

                ],
                'integer'
            ],

        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'program_name', 'is_categories', 'editable', 'visible', 'is_multiple_categories',
                'category_form_name_field', 'represent_form_name_field', 'count_subcategories',
                'is_intermediate_categories', 'max_categories', 'categories_create_by_user',
                'represents_pagination_count', 'delete_subcategories', 'delete_represents',
                'createCategoriesStandardFields', 'createRepresentsStandardFields',
                'createCategoriesSeoFields', 'createRepresentSeoFields'
            ],
            self::SCENARIO_UPDATE => [
                'program_name', 'is_categories', 'editable', 'visible', 'is_multiple_categories',
                'category_form_name_field', 'represent_form_name_field', 'count_subcategories',
                'is_intermediate_categories', 'max_categories', 'categories_create_by_user',
                'represents_pagination_count', 'delete_subcategories', 'delete_represents'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'program_name'               => 'Program Name',
            'is_categories'              => 'Is Categories',
            'categories_create_by_user'  => 'Categories can be created by admin',
            'count_subcategories'        => 'Count Subcategories  (0 - infinity)',
            'is_multiple_categories'     => 'Is Multiple Categories',
            'is_intermediate_categories' => 'Is intermediate categories for represents',
            'max_categories'             => 'Max count of multiple categories (0 - infinity)',
            'category_form_name_field'   => 'Name forming field for categories',
            'represent_form_name_field'  => 'Name forming field for represents',
            'delete_subcategories'       => 'Delete subcategories on category delete',
            'delete_represents'          => 'Delete represents on category delete',
        ];
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function afterValidate()
    {
        parent::afterValidate();

        if ($this->hasErrors()) return;

        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->field_template_reference_category      = FieldTemplate::generateTemplateReference();
            $this->field_template_reference_represent     = FieldTemplate::generateTemplateReference();

            $this->file_template_reference_category       = FilesBlock::generateTemplateReference();
            $this->file_template_reference_represent      = FilesBlock::generateTemplateReference();

            $this->image_template_reference_category      = ImagesBlock::generateTemplateReference();
            $this->image_template_reference_represent     =  ImagesBlock::generateTemplateReference();

            $this->condition_template_reference_category  = ConditionTemplate::generateTemplateReference();
            $this->condition_template_reference_represent = ConditionTemplate::generateTemplateReference();
        }
    }

    /**
     * Returns instance of essence by her name
     * @param $programName
     * @return array|Essences|null|ActiveRecord
     * @throws EssencesException
     */
    public static function getByName($programName)
    {
        foreach(self::$essencesBuffer as $essence)
            if ($essence->program_name == $programName)
                return $essence;

        /** @var self $essence */
        $essence = self::find()
            ->where(['program_name' => $programName])
            ->one();

        if ($essence) {
            self::$essencesBuffer[$essence->id] = $essence;
            return $essence;
        }

        Yii::error("Сan not find essence with name " . $programName, __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new EssencesException('Сan not find essence with name ' . $programName);
        }

        $nonexistentEssence= new self();
        $nonexistentEssence->setNonexistent();
        $nonexistentEssence->nonexistentName = $programName;

        return $nonexistentEssence;
    }

    /**
     * Returns instance of essence by her id
     * @param $id
     * @return Essences|null|static
     * @throws EssencesException
     */
    public static function getInstance($id)
    {
        if (isset(self::$essencesBuffer[$id]))
            return self::$essencesBuffer[$id];

        $essence = self::findOne($id);

        if ($essence) {
            self::$essencesBuffer[$essence->id] = $essence;
            return $essence;
        }

        Yii::error("Сan not find essence with id " . $id, __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new EssencesException('Сan not find essence with id ' . $id);
        }

        return $essence;
    }

    /**
     * Returns category by id
     * @param $id
     * @return EssencesCategories|AbstractTreeNode
     */
    public function getCategoryById($id)
    {
        if ($this->isNonexistent()) {
            $nonexistentCategory = new EssencesCategories();
            $nonexistentCategory->setNonexistent();

            return $nonexistentCategory;
        }

        return $this->getNodeById($id);
    }

    /**
     * Returns represent by id
     * @param $id
     * @return EssencesRepresents|null|static
     */
    public function getRepresentById($id)
    {
        if ($this->isNonexistent()) {
            $nonexistentRepresent = new EssencesRepresents();
            $nonexistentRepresent->setNonexistent();

            return $nonexistentRepresent;
        }

        if (isset($this->representsBuffer[$id]))
            return $this->representsBuffer[$id];

        return $this->representsBuffer[$id] = EssencesRepresents::findOne($id);
    }

    /**
     * Return all represents for this essence
     * @return EssencesRepresents[]
     */
    public function getAllRepresents()
    {
        if ($this->isNonexistent())
            return [];

        if ($this->isAllRepresentsFetched)
            return $this->representsBuffer;

        $this->isAllRepresentsFetched = true;

        return $this->representsBuffer = EssencesRepresents::find()->where([
            'essence_id' => $this->id
        ])->orderBy(['represent_order' => SORT_ASC])
          ->indexBy('id')
          ->all();
    }

    /**
     * Returns ActiveQuery for find all represents for this essence
     * @return ActiveQuery
     */
    public function getAllRepresentsQuery()
    {
        return EssencesRepresents::find()->where([
            'essence_id' => $this->id
        ]);
    }

    /**
     * Creates list of categories for filter represents drop list
     * @return array
     * @throws EssencesException
     */
    public function getCategoriesForDropList()
    {
        $list = [];

        $list[0] = EssencesModule::t('app', 'All represents');

        if ($this->isRepresentsWithoutCategories())
            $list[-1] = EssencesModule::t('app', 'Represents without categories');

        /** @var EssencesCategories $node */
        foreach($this->traversalByTreeOrder() as $node) {
            if ($node->countRepresents() == 0) continue;

            $list[$node->id] = $node->getNodeName();
        }

        return $list;
    }

    /**
     * Return true if essence consist represents without assigned categories
     * @return bool
     * @throws EssencesException
     */
    public function isRepresentsWithoutCategories()
    {
        foreach($this->getAllRepresents() as $represent)
            if ($represent->countCategories() == 0) return true;

        return false;
    }

    /**
     * Returns array of represents without assigned categories for this essence
     * @return array
     * @throws EssencesException
     */
    public function getRepresentsWithoutCategories()
    {
        $result = [];

        foreach($this->getAllRepresents() as $represent)
            if ($represent->countCategories() == 0) $result[$represent->id] = $represent;

        return $result;
    }

    /**
     * Return query for find all represents without category for this essence
     * @return ActiveQuery
     * @throws EssencesException
     */
    public function getRepresentsWithoutCategoriesQuery()
    {
        $ids = [];

        foreach($this->getAllRepresents() as $represent)
            if ($represent->countCategories() == 0) $ids = $represent->id;

        return EssencesRepresents::find()->where([
            'in', 'id', $ids
        ]);
    }

    /**
     * Creates new essence with all service records
     * @return bool
     * @throws EssencesException
     */
    public function create()
    {
        if ($this->isNonexistent) return false;

        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->essence_order = $this->maxOrder();
        }

        if (!$this->save(false))
            throw new EssencesException('Can not create essence '. $this->program_name);

        if ($this->createCategoriesSeoFields) {
            $fieldTemplate                           = new FieldTemplate();
            $fieldTemplate->field_template_reference = $this->field_template_reference_category;
            $fieldTemplate->scenario                 = FieldTemplate::SCENARIO_CREATE;
            $fieldTemplate->program_name             = 'title';
            $fieldTemplate->type                     = FieldTemplate::TYPE_INPUT;
            $fieldTemplate->language_type            = FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE;
            $fieldTemplate->visible                  = true;
            $fieldTemplate->editable                 = true;
            $fieldTemplate->field_order              = 1;

            $fieldTemplate->save(false);

            $fieldTemplate                           = new FieldTemplate();
            $fieldTemplate->field_template_reference = $this->field_template_reference_category;
            $fieldTemplate->scenario                 = FieldTemplate::SCENARIO_CREATE;
            $fieldTemplate->program_name             = 'meta_description';
            $fieldTemplate->type                     = FieldTemplate::TYPE_TEXT;
            $fieldTemplate->language_type            = FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE;
            $fieldTemplate->visible                  = true;
            $fieldTemplate->editable                 = true;
            $fieldTemplate->field_order              = 2;

            $fieldTemplate->save(false);

            $fieldTemplate                           = new FieldTemplate();
            $fieldTemplate->field_template_reference = $this->field_template_reference_category;
            $fieldTemplate->scenario                 = FieldTemplate::SCENARIO_CREATE;
            $fieldTemplate->program_name             = 'meta_keywords';
            $fieldTemplate->type                     = FieldTemplate::TYPE_TEXT;
            $fieldTemplate->language_type            = FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE;
            $fieldTemplate->visible                  = true;
            $fieldTemplate->editable                 = true;
            $fieldTemplate->field_order              = 3;

            $fieldTemplate->save(false);
            //TODO: makes create translates for standard fields
        }

        if ($this->createCategoriesStandardFields) {
            $fieldTemplate                           = new FieldTemplate();
            $fieldTemplate->field_template_reference = $this->field_template_reference_category;
            $fieldTemplate->scenario                 = FieldTemplate::SCENARIO_CREATE;
            $fieldTemplate->program_name             = 'name';
            $fieldTemplate->type                     = FieldTemplate::TYPE_INPUT;
            $fieldTemplate->language_type            = FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE;
            $fieldTemplate->visible                  = true;
            $fieldTemplate->editable                 = true;

            $count = FieldTemplate::find()->where([
                'field_template_reference' => $this->field_template_reference_category
            ])->count();

            $fieldTemplate->field_order = ++$count;

            $fieldTemplate->save(false);

            $this->category_form_name_field = $fieldTemplate->id;
            $this->save(false);
        }

        if ($this->createRepresentSeoFields) {
            $fieldTemplate                           = new FieldTemplate();
            $fieldTemplate->field_template_reference = $this->field_template_reference_represent;
            $fieldTemplate->scenario                 = FieldTemplate::SCENARIO_CREATE;
            $fieldTemplate->program_name             = 'title';
            $fieldTemplate->type                     = FieldTemplate::TYPE_INPUT;
            $fieldTemplate->language_type            = FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE;
            $fieldTemplate->visible                  = true;
            $fieldTemplate->editable                 = true;
            $fieldTemplate->field_order              = 1;

            $fieldTemplate->save(false);

            $fieldTemplate                           = new FieldTemplate();
            $fieldTemplate->field_template_reference = $this->field_template_reference_represent;
            $fieldTemplate->scenario                 = FieldTemplate::SCENARIO_CREATE;
            $fieldTemplate->program_name             = 'meta_description';
            $fieldTemplate->type                     = FieldTemplate::TYPE_TEXT;
            $fieldTemplate->language_type            = FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE;
            $fieldTemplate->visible                  = true;
            $fieldTemplate->editable                 = true;
            $fieldTemplate->field_order              = 2;

            $fieldTemplate->save(false);

            $fieldTemplate                           = new FieldTemplate();
            $fieldTemplate->field_template_reference = $this->field_template_reference_represent;
            $fieldTemplate->scenario                 = FieldTemplate::SCENARIO_CREATE;
            $fieldTemplate->program_name             = 'meta_keywords';
            $fieldTemplate->type                     = FieldTemplate::TYPE_TEXT;
            $fieldTemplate->language_type            = FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE;
            $fieldTemplate->visible                  = true;
            $fieldTemplate->editable                 = true;
            $fieldTemplate->field_order              = 3;

            $fieldTemplate->save(false);
            //TODO: makes create translates for standard fields
        }

        if ($this->createRepresentsStandardFields) {
            $fieldTemplate                           = new FieldTemplate();
            $fieldTemplate->field_template_reference = $this->field_template_reference_represent;
            $fieldTemplate->scenario                 = FieldTemplate::SCENARIO_CREATE;
            $fieldTemplate->program_name             = 'name';
            $fieldTemplate->type                     = FieldTemplate::TYPE_INPUT;
            $fieldTemplate->language_type            = FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE;
            $fieldTemplate->visible                  = true;
            $fieldTemplate->editable                 = true;

            $count = FieldTemplate::find()->where([
                'field_template_reference' => $this->field_template_reference_category
            ])->count();

            $fieldTemplate->field_order = ++$count;

            $fieldTemplate->save(false);

            $this->represent_form_name_field = $fieldTemplate->id;
            $this->save(false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        if ($this->isNonexistent) return false;

        //for categories
        /** @var FieldTemplate[] $fieldTemplatesCategory */
        $fieldTemplatesCategory = FieldTemplate::find()->where([
            'field_template_reference' => $this->getCategoryFieldTemplateReference(),
        ])->all();

        foreach($fieldTemplatesCategory as $fieldTemplate)
            $fieldTemplate->delete();

        /** @var FilesBlock[] $filesBlocksCategory */
        $filesBlocksCategory = FilesBlock::find()->where([
            'file_template_reference' => $this->getCategoryFileTemplateReference(),
        ])->all();

        foreach($filesBlocksCategory as $fileBlock)
            $fileBlock->delete();

        /** @var ImagesBlock[] $imageBlocksCategory */
        $imageBlocksCategory = ImagesBlock::find()->where([
            'image_template_reference' => $this->getCategoryImageTemplateReference(),
        ])->all();

        foreach($imageBlocksCategory as $imageBlock)
            $imageBlock->delete();

        /** @var ConditionTemplate[] $conditionTemplatesCategory */
        $conditionTemplatesCategory = ConditionTemplate::find()->where([
            'condition_template_reference' => $this->getCategoryConditionTemplateReference(),
        ])->all();

        foreach($conditionTemplatesCategory as $conditionTemplate)
            $conditionTemplate->delete();

        //for represents
        /** @var FieldTemplate[] $fieldTemplatesRepresent */
        $fieldTemplatesRepresent = FieldTemplate::find()->where([
            'field_template_reference' => $this->getRepresentFieldTemplateReference(),
        ])->all();

        foreach($fieldTemplatesRepresent as $fieldTemplate)
            $fieldTemplate->delete();

        /** @var FilesBlock[] $filesBlocksRepresent */
        $filesBlocksRepresent = FilesBlock::find()->where([
            'file_template_reference' => $this->getRepresentFileTemplateReference(),
        ])->all();

        foreach($filesBlocksRepresent as $fileBlock)
            $fileBlock->delete();

        /** @var ImagesBlock[] $imageBlocksRepresent */
        $imageBlocksRepresent = ImagesBlock::find()->where([
            'image_template_reference' => $this->getRepresentImageTemplateReference(),
        ])->all();

        foreach($imageBlocksRepresent as $imageBlock)
            $imageBlock->delete();

        /** @var ConditionTemplate[] $conditionTemplatesRepresent */
        $conditionTemplatesRepresent = ConditionTemplate::find()->where([
            'condition_template_reference' => $this->getRepresentConditionTemplateReference(),
        ])->all();

        foreach($conditionTemplatesRepresent as $conditionTemplate)
            $conditionTemplate->delete();

        /** @var EssencesRepresents[] $represents */
        $represents = EssencesRepresents::find()->where([
            'essence_id' => $this->id
        ])->all();

        foreach ($represents as $represent) {
            /** @var EssenceRepresentToCategory[] $representToCategories */
            $representToCategories = EssenceRepresentToCategory::find()->where([
                'represent_id' => $represent->id,
            ])->all();

            foreach ($representToCategories as $representCategory)
                $representCategory->delete();

            $represent->simpleDelete();
        }

        /** @var EssencesCategories[] $categories */
        $categories = EssencesCategories::find()->where([
            'essence_id' => $this->id
        ])->all();

        foreach ($categories as $category) {
            /** @var EssenceRepresentToCategory[] $representToCategories */
            $representToCategories = EssenceRepresentToCategory::find()->where([
                'category_id' => $category->id,
            ])->all();

            foreach ($representToCategories as $representCategory)
                $representCategory->delete();

            $category->simpleDelete();
        }

        /** @var EssencesNamesTranslatesDb[] $essenceNames */
        $essenceNames = EssencesNamesTranslatesDb::find()->where([
            'essence_id' => $this->id
        ])->all();

        foreach($essenceNames as $essenceName)
            $essenceName->delete();

        return parent::delete();
    }

    /**
     * Return true if essence has any constraints
     * @return bool
     */
    public function isConstraints()
    {
        $categoriesCount = EssencesCategories::find()->where([
            'essence_id' => $this->id
        ])->count();

        if ($categoriesCount) return true;

        $representsCount = EssencesRepresents::find()->where([
            'essence_id' => $this->id
        ])->count();

        if ($representsCount) return true;

        return false;
    }

    /**
     * Returns name of essence
     * @param LanguagesDb|null $language
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function name(LanguagesDb $language = null)
    {
        if (!$language) $language = Language::getInstance()->getCurrentLanguage();

        if (!EssencesNamesTranslatesDb::getTranslate($this->id, $language->id)) return $this->program_name;

        return EssencesNamesTranslatesDb::getTranslate($this->id, $language->id)->name;
    }

    /**
     * Returns description of essence
     * @param LanguagesDb|null $language
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function description(LanguagesDb $language = null)
    {
        if (!$language) $language = Language::getInstance()->getCurrentLanguage();

        if (!EssencesNamesTranslatesDb::getTranslate($this->id, $language->id)) return '';

        return EssencesNamesTranslatesDb::getTranslate($this->id, $language->id)->description;
    }

    /**
     * Returns true if essence has categories
     * @return bool
     */
    public function isCategories()
    {
        return !!$this->is_categories;
    }

    /**
     * Returns true if essence has multiple categories
     * @return bool
     */
    public function isMultipleCategories()
    {
        return !!$this->is_multiple_categories;
    }

    /**
     * Returns true if for this essence represents can have intermediate categories
     * @return bool
     */
    public function isIntermediateCategories()
    {
        return !!$this->is_intermediate_categories;
    }

    /**
     * Returns true if user can create categories
     * @return bool
     */
    public function canCreateCategoryByAdmin()
    {
        return !!$this->categories_create_by_user;
    }

    /**
     *
     * @return AbstractTreeNode[]|EssencesCategories[]
     */
    public function getCategories()
    {
        return $this->traversalByTreeOrder();
    }

    /**
     * Method return list of category fields for drop down lists
     * This method do not buffer templates
     * @return array
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getCategoriesFieldsList()
    {
        /** @var FieldTemplate[] $fieldTemplates */
        $fieldTemplates = FieldTemplate::find()->where([
            'field_template_reference' => $this->getCategoryFieldTemplateReference(),
        ])->all();

        $result = [0 => 'No field selected'];

        foreach($fieldTemplates as $fieldTemplate)
            $result[$fieldTemplate->id] = $fieldTemplate->program_name;

        return $result;
    }

    /**
     * Method return list of represents fields for drop down lists
     * This method do not buffer templates
     * @return array
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getRepresentsFieldsList()
    {
        /** @var FieldTemplate[] $fieldTemplates */
        $fieldTemplates = FieldTemplate::find()->where([
            'field_template_reference' => $this->getRepresentFieldTemplateReference(),
        ])->all();

        $result = [0 => 'No field selected'];

        foreach($fieldTemplates as $fieldTemplate)
            $result[$fieldTemplate->id] = $fieldTemplate->program_name;

        return $result;
    }

    /**
     * Returns list of categories for categories lists
     * @return array
     * @throws EssencesException
     */
    public function getListForCategories()
    {
        $list = [];

        $list[0] = EssencesModule::t('app', 'Root category');

        $treeOrder = $this->traversalByTreeOrder();

        /** @var EssencesCategories $elem */
        foreach($treeOrder as $elem) {

            $levelString = '';
            for ($i = 1; $i < $elem->getLevel(); $i++)
                $levelString .= '-';

            $list[$elem->id] = $levelString . $elem->getNodeName();
        }

        return $list;
    }

    /**
     * Returns field_template_reference_category
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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

    /**
     * @inheritdoc
     */
    public function getMaxCategoriesLevel()
    {
        return $this->count_subcategories;
    }

    /**
     * @inheritdoc
     */
    protected function getTreeNodes()
    {
        return EssencesCategories::find()->where([
            'essence_id' => $this->id,
        ])->all();
    }

    /**
     * @inheritdoc
     */
    protected function getTreeNode($id)
    {
        return EssencesCategories::find()->where([
            'id'         => $id,
            'essence_id' => $this->id,
        ])->one();
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
}
