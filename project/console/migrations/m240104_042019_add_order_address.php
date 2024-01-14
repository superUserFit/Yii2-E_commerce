<?php

use yii\db\Migration;

/**
 * Class m240104_042019_add_order_address
 */
class m240104_042019_add_order_address extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_address}}', [
            'order_id' => $this->integer()->notNull(),
            'address' => $this->string(255)->notNull(),
            'city' => $this->string(255)->notNull(),
            'state' => $this->string(255)->notNull(),
            'country' => $this->string(255)->notNull(),
            'zipcode' => $this->string(255),
        ]);

        $this->addPrimaryKey('PK_order_address', '{{%order_address}}', 'order_id');

        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-order_address-order_id}}',
            '{{%order_address}}',
            'order_id'
        );

        // add foreign key for table `{{%orders}}`
        $this->addForeignKey(
            '{{%fk-order_address-order_id}}',
            '{{%order_address}}',
            'order_id',
            '{{%orders}}',
            'id',
            'CASCADE'
        );
    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240104_042019_add_order_address cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240104_042019_add_order_address cannot be reverted.\n";

        return false;
    }
    */
}
