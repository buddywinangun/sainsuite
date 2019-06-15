<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add sessions table.
 *
 * For additional instructions/information, see
 * http://www.codeigniter.com/userguide3/libraries/sessions.html#database-driver
 *
 * The created table does not include an index/key on the timestamp field. The link
 * includes the command to create the index on PostgreSQL, but it is included within
 * the table definition in MySQL. To create the index in MySQL, after running the
 * migration below, execute the following command on your MySQL server:
 *
 * alter table sessions add index ci_sessions_timestamp (timestamp)
 */
class Migration_Add_sessions extends Migration
{
    private $sessionsTable = array(
        'name' => 'sessions', // the name of the table
        'key' => 'id',            // the primary key
        'fields' => array(        // field definitions
            'id' => array(
                'type'       => 'varchar',
                'constraint' => 128,
                'null'       => false,
            ),
            'ip_address' => array(
                'type'       => 'varchar',
                'constraint' => 45,
                'null'       => false,
            ),
            'timestamp' => array(
                'type'       => 'int',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ),
            // For PostgreSQL, use this timestamp definition, instead.
            // 'timestamp' => array(
            //     'type'    => 'bigint',
            //     'default' => 0,
            //     'null'    => false,
            // ),
            'data' => array(
                'type' => 'blob',
                'null' => false,
            ),
        ),
    );

    /**
     * Add the table to the database.
     *
     * @return void
     */
    public function up()
    {
        $this->dbforge->add_field($this->sessionsTable['fields']);
        $this->dbforge->add_key($this->sessionsTable['key'], true);

        // Create the table if it does not already exist.
        $this->dbforge->create_table($this->sessionsTable['name'], true);
    }

    /**
     * Remove the table from the database.
     *
     * @return void
     */
    public function down()
    {
        if ($this->db->table_exists($this->sessionsTable['name'])) {
            $this->dbforge->drop_table($this->sessionsTable['name']);
        }
    }
}
