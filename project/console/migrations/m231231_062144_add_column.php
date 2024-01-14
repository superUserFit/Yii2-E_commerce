<?php

use yii\db\Migration;

/**
 * Class m231231_062144_add_column
 */
class m231231_062144_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'verification_token', $this->string()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231231_062144_add_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231231_062144_add_column cannot be reverted.\n";

        return false;
    }
    */
}
