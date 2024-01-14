<?php

use yii\db\Migration;

/**
 * Class m240103_083508_add_table
 */
class m240103_083508_add_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'description' => 'LONGTEXT',
            'image' => $this->string(2000),
            'price' => $this->decimal(10, 2)->notNull(),
            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11),
            'created_by' => $this->integer(11),
            'updated_by' => $this->integer(11),
        ]);

        $this->createIndex(
            '{{%idx-product-created_by}}',
            '{{%product}}',
            'created_by',
        );

        $this->addForeignKey(
            '{{%fk-product-created_by}}',
            '{{%product}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE',
        );

        $this->createIndex(
            '{{%idx-product-updated_by}}',
            '{{%product}}',
            'updated_by',
        );

        $this->addForeignKey(
            '{{%fk-product-updated_by}}',
            '{{%product}}',
            'updated_by',
            '{{%user}}',
            'id',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240103_083508_add_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240103_083508_add_table cannot be reverted.\n";

        return false;
    }
    */
}
