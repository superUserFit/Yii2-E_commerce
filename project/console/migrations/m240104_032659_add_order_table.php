<?php

use yii\db\Migration;

/**
 * Class m240104_032659_add_order_table
 */
class m240104_032659_add_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%orders}}', [
            'id' => $this->primaryKey(),
            'total_price' => $this->decimal(10, 2)->notNull(),
            'status' => $this->integer(11)->notNull(),
            'firstName' => $this->string(255)->notNull(),
            'lastName' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull(),
            'transaction_id' => $this->string(255),
            'created_at' => $this->integer(11),
            'created_by' => $this->integer(11),
        ]);

        $this->createIndex(
            '{{%idx-orders-created_by}}',
            '{{%orders}}',
            'created_by',
        );

        $this->addForeignKey(
            '{{%fk-orders-created_by}}',
            '{{%orders}}',
            'created_by',
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
        echo "m240104_032659_add_order_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240104_032659_add_order_table cannot be reverted.\n";

        return false;
    }
    */
}
