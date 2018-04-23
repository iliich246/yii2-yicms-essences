<?php

use yii\db\Migration;
use Iliich246\YicmsCommon\Languages\Language;

/**
 * Class m180423_213106_essences_init
 *
 * ALTER DATABASE <database_name> CHARACTER SET utf8 COLLATE utf8_unicode_ci;
 */
class m180423_213106_essences_init extends Migration
{
    /**
 * @inheritdoc
 */
    public function safeUp()
    {
        /**
         * essences table
         */
        $this->createTable('{{%essences}}', [
            'id'                                     => $this->primaryKey(),
            'program_name'                           => $this->string(50),
            'is_categories'                          => $this->smallInteger(1),
            'count_subcategories'                    => $this->integer(),
            'is_multiple_categories'                 => $this->smallInteger(),
            'field_template_reference_category'      => $this->string(),
            'file_template_reference_category'       => $this->string(),
            'image_template_reference_category'      => $this->string(),
            'condition_template_reference_category'  => $this->string(),
            'field_template_reference_represent'     => $this->string(),
            'file_template_reference_represent'      => $this->string(),
            'image_template_reference_represent'     => $this->string(),
            'condition_template_reference_represent' => $this->string(),
        ]);

        /**
         * essences_config table
         */
        $this->createTable('{{%essences_config}}', [
            'id' => $this->primaryKey(),
        ]);

        $this->insert('{{%essences_config}}', [
            'id' => 1,
        ]);

        /**
         * pages_names_translates table
         */
        $this->createTable('{{%essences_names_translates}}', [
            'id'                 => $this->primaryKey(),
            'essence_id'         => $this->integer(),
            'common_language_id' => $this->integer(),
            'name'               => $this->string(),
            'description'        => $this->string(),
        ]);

        $this->addForeignKey('essences_names_translates-to-essences',
            '{{%essences_names_translates}}',
            'essence_id',
            '{{%essences}}',
            'id'
        );

        $this->addForeignKey('essences_names_translates-to-common_languages',
            '{{%essences_names_translates}}',
            'common_language_id',
            '{{%common_languages}}',
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }
}
