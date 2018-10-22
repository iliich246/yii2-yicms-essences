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
            'categories_create_by_user'              => $this->boolean(),
            'count_subcategories'                    => $this->integer(),
            'is_multiple_categories'                 => $this->smallInteger(),
            'is_intermediate_categories'             => $this->smallInteger(),
            'max_categories'                         => $this->smallInteger(),
            'essence_order'                          => $this->integer(),
            'editable'                               => $this->boolean(),
            'visible'                                => $this->boolean(),
            'category_form_name_field'               => $this->integer(),
            'represent_form_name_field'              => $this->integer(),
            'represents_pagination_count'            => $this->integer(),
            'delete_subcategories'                   => $this->boolean(),
            'delete_represents'                      => $this->boolean(),
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

        /**
         * essences_categories table
         */
        $this->createTable('{{%essences_categories}}', [
            'id'                  => $this->primaryKey(),
            'essence_id'          => $this->integer(),
            'parent_id'           => $this->integer(),
            'editable'            => $this->boolean(),
            'visible'             => $this->boolean(),
            'category_order'      => $this->integer(),
            'system_route'        => $this->string(),
            'ruled_route'         => $this->string(),
            'field_reference'     => $this->string(),
            'file_reference'      => $this->string(),
            'image_reference'     => $this->string(),
            'condition_reference' => $this->string(),
            'created_at'          => $this->integer(),
            'updated_at'          => $this->integer(),
        ]);

        $this->addForeignKey('essences_categories-to-essences',
            '{{%essences_categories}}',
            'essence_id',
            '{{%essences}}',
            'id'
        );

        /**
         * essences_represents table
         */
        $this->createTable('{{%essences_represents}}', [
            'id'                  => $this->primaryKey(),
            'essence_id'          => $this->integer(),
            'represent_order'     => $this->integer(),
            'editable'            => $this->boolean(),
            'visible'             => $this->boolean(),
            'system_route'        => $this->string(),
            'ruled_route'         => $this->string(),
            'field_reference'     => $this->string(),
            'file_reference'      => $this->string(),
            'image_reference'     => $this->string(),
            'condition_reference' => $this->string(),
            'created_at'          => $this->integer(),
            'updated_at'          => $this->integer(),
        ]);

        $this->addForeignKey('essences_represents-to-essences',
            '{{%essences_represents}}',
            'essence_id',
            '{{%essences}}',
            'id'
        );

        /**
         * essences_category_represent table
         */
        $this->createTable('{{%essences_category_represent}}', [
            'id'              => $this->primaryKey(),
            'category_id'     => $this->integer(),
            'represent_id'    => $this->integer(),
            'represent_order' => $this->integer(),
        ]);

        $this->addForeignKey('essences_category_represent-to-essences_categories',
            '{{%essences_category_represent}}',
            'category_id',
            '{{%essences_categories}}',
            'id'
        );

        $this->addForeignKey('essences_category_represent-to-essences_represents',
            '{{%essences_category_represent}}',
            'represent_id',
            '{{%essences_represents}}',
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('essences_category_represent-to-essences_represents',
            '{{%essences_category_represent}}');
        $this->dropForeignKey('essences_category_represent-to-essences_categories',
            '{{%essences_category_represent}}');
        $this->dropTable('{{%essences_category_represent}}');

        $this->dropForeignKey('essences_represents-to-essences',
            '{{%essences_represents}}');
        $this->dropTable('{{%essences_represents}}');

        $this->dropForeignKey('essences_categories-to-essences',
            '{{%essences_categories}}');
        $this->dropTable('{{%essences_categories}}');

        $this->dropForeignKey('essences_names_translates-to-common_languages',
            '{{%essences_names_translates}}');
        $this->dropForeignKey('essences_names_translates-to-essences',
            '{{%essences_names_translates}}');
        $this->dropTable('{{%essences_names_translates}}');

        $this->dropTable('{{%essences_config}}');

        $this->dropTable('{{%essences}}');
    }
}
