<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
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
class EssencesCategories extends ActiveRecord implements
    FieldsInterface,
    FieldReferenceInterface,
    FilesInterface,
    FilesReferenceInterface,
    ImagesInterface,
    ImagesReferenceInterface,
    ConditionsReferenceInterface,
    ConditionsInterface,
    SortOrderInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    const MODE_CASUAL = 0;
    const MODE_BASKET = 1;
    const MODE_TOP    = 2;

    /** @var FieldsHandler instance of field handler object */
    private $fieldHandler;
    /** @var FilesHandler instance of file handler object*/
    private $fileHandler;
    /** @var ImagesHandler instance of image handler object*/
    private $imageHandler;
    /** @var ConditionsHandler instance of condition handler object*/
    private $conditionHandler;

    private $tempEssence;

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
     * PHP getter magic method.
     * This method is overridden so that essence attribute will return $this->tempEssence
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)    {

        if (!$this->getIsNewRecord() && $name != 'essence') return parent::__get($name);

        return $this->tempEssence;

    }

    /**
     * PHP setter magic method.
     * This method is overridden so that for not saved object essence attribute will save in
     * $this->tempEssence, not in AR attributes
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (!$this->getIsNewRecord() && $name != 'essence') {
            parent::__set($name, $value);
            return;
        }

        $this->tempEssence = $value;
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepresents()
    {
        return $this->hasMany(EssencesRepresents::class, ['id' => 'represent_id'])
                ->viaTable('{{%essences_category_represent}}', ['category_id' => 'id']);
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
     */
    public function getField($name)
    {
        return $this->getFieldHandler()->getField($name);
    }

    /**
     * @inheritdoc
     */
    public function getFieldTemplateReference()
    {
        $essence = $this->essence;

        //throw new \Exception(print_r($essence,true));

        if (!$essence->field_template_reference_category) {
            $essence->field_template_reference_category = FieldTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $essence->field_template_reference_category;
    }

    /**
     * @inheritdoc
     */
    public function getFieldReference()
    {
        return 'xxx';

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
     */
    public function getFileTemplateReference()
    {
        $essence = $this->essence;

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
     */
    public function getImageTemplateReference()
    {
        $essence = $this->essence;

        if (!$essence->image_template_reference_category) {
            $essence->image_template_reference_category = ImagesBlock::generateTemplateReference();
            $essence->save(false);
        }

        return $essence->image_template_reference_category;
    }

    /**
     * @inheritdoc
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
     */
    public function getConditionTemplateReference()
    {
        $essence = $this->essence;

        if (!$essence->condition_template_reference_category) {
            $essence->condition_template_reference_category = ConditionTemplate::generateTemplateReference();
            $essence->save(false);
        }

        return $essence->condition_template_reference_category;
    }

    /**
     * @inheritdoc
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
        return self::find();
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
}
