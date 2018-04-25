<?php

namespace Iliich246\YicmsEssences\Base;

use Iliich246\YicmsCommon\Base\AbstractTranslateForm;

/**
 * Class EssenceDevTranslateForm
 *
 * @property EssencesNamesTranslatesDb $currentTranslateDb
 *
 * @package Iliich246\YicmsEssences\Base
 */
class EssenceDevTranslateForm extends AbstractTranslateForm
{
    /** @var string name of essence in current model language */
    public $name;
    /** @var string description of essence on current model language */
    public $description;
    /** @var Essences db associated with this model */
    public $essence;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => 'Essence name on language "' . $this->language->name . '"',
            'description' => 'Description of essence on language "' . $this->language->name . '"',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => '50', 'tooLong' => 'Name of essence must be less than 50 symbols'],
            ['description', 'string'],
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
        $this->currentTranslateDb->name = $this->name;
        $this->currentTranslateDb->description = $this->description;

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
            $this->name = $this->currentTranslateDb->name;
            $this->description = $this->currentTranslateDb->description;
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
        $this->currentTranslateDb->essence_id = $this->essence->id;

        return $this->currentTranslateDb->save();
    }
}
