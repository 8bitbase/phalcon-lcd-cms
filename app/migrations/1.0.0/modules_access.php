<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ModulesAccessMigration_100
 */
class ModulesAccessMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('modules_access', [
                'columns' => [
                    new Column(
                        'id_profile',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 11,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'id_module',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 11,
                            'after' => 'id_profile'
                        ]
                    ),
                    new Column(
                        'is_view',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 4,
                            'after' => 'id_module'
                        ]
                    ),
                    new Column(
                        'is_edit',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 4,
                            'after' => 'is_view'
                        ]
                    ),
                    new Column(
                        'is_add',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 4,
                            'after' => 'is_edit'
                        ]
                    ),
                    new Column(
                        'is_delete',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 4,
                            'after' => 'is_add'
                        ]
                    ),
                    new Column(
                        'id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 11,
                            'after' => 'is_delete'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['id'], 'PRIMARY')
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '33',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'utf8_general_ci'
                ],
            ]
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {
        $this->batchInsert('modules_access', [
                'id_profile',
                'id_module',
                'is_view',
                'is_edit',
                'is_add',
                'is_delete',
                'id'
            ]
        );
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {
        $this->batchDelete('modules_access');
    }

}
