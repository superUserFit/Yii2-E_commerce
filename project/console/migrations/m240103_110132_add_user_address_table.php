<?php

use yii\db\Migration;

/**
 * Class m240103_110132_add_user_address_table
 */
class m240103_110132_add_user_address_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_address}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'address' => $this->string(255)->notNull(),
            'city' => $this->string(255)->notNull(),
            'state' => $this->string(255)->notNull(),
            'country' => $this->string(255)->notNull(),
            'zipcode' => $this->string(255)->notNull(),
        ]);

        $this->createIndex(
            '{{%idx-user_address-user_id}}',
            '{{%user_address}}',
            'user_id',
        );

        $this->addForeignKey(
            '{{%fk-user_address-user_id}}',
            '{{%user_address}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240103_110132_add_user_address_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240103_110132_add_user_address_table cannot be reverted.\n";

        return false;
    }
    */
}
