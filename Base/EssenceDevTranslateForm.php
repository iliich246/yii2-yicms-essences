<?php

namespace Iliich246\YicmsEssences\Base;

use Iliich246\YicmsCommon\Base\AbstractTranslateForm;

/**
 * Class EssenceDevTranslateForm
 *
 * @property EssencesNamesTranslatesDb $currentTranslateDb
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class EssenceDevTranslateForm extends AbstractTranslateForm
{
    /** @var string name of essence in current model language */
    public $name;
    /** @var string description of essence on current model language */
    public $description;
    /** @var string name of category in admin part of site */
    public $categoryName;
    /** @var string name of represents in admin part of site  */
    public $representName;
    /** @var Essences db associated with this model */
    public $essence;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'          => 'Essence name on language "' . $this->language->name . '"',
            'description'   => 'Description of essence on language "' . $this->language->name . '"',
            'categoryName'  => 'Name of category of essence on language"' . $this->language->name . '"',
            'representName' => 'Name of represent of essence on language"' . $this->language->name . '"',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => '50', 'tooLong' => 'Name of essence must be less than 50 symbols'],
            [['description', 'categoryName', 'representName'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getViewName()
    {
        return '@yicms-essences/Views/translates/essence_name_translate';
    }

    /**
     * Sets essence of model
     * @param Essences $essence
     */
    public function setEssence(Essences $essence)
    {
        $this->essence = $essence;
    }

    /**
     * @inheritdoc
     */
    protected function isCorrectConfigured()
    {
        if (!parent::isCorrectConfigured() || !$this->essence) return false;
        return true;
    }

    /**
     * Saves new data in data base
     * @return bool
     */
    public function save()
    {
        $this->currentTranslateDb->name           = $this->name;
        $this->currentTranslateDb->description    = $this->description;
        $this->currentTranslateDb->category_name  = $this->categoryName;
        $this->currentTranslateDb->represent_name = $this->representName;

        return $this->currentTranslateDb->save();
    }

    /**
     * @inheritdoc
     */
    public function getCurrentTranslateDb()
    {
        if ($this->currentTranslateDb) return $this->currentTranslateDb;

        $this->currentTranslateDb = EssencesNamesTranslatesDb::find()
            ->where([
                'common_language_id' => $this->language->id,
                'essence_id'         => $this->essence->id,
            ])
            ->one();

        if (!$this->currentTranslateDb)
            $this->createTranslateDb();
        else {
            $this->name          = $this->currentTranslateDb->name;
            $this->description   = $this->currentTranslateDb->description;
            $this->categoryName  = $this->currentTranslateDb->category_name;
            $this->representName = $this->currentTranslateDb->represent_name;
        }

        return $this->currentTranslateDb;
    }

    /**
     * @inheritdoc
     */
    protected function createTranslateDb()
    {
        $this->currentTranslateDb = new EssencesNamesTranslatesDb();
        $this->currentTranslateDb->common_language_id = $this->language->id;
        $this->currentTranslateDb->essence_id         = $this->essence->id;

        return $this->currentTranslateDb->save();
    }
}
