<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Racik
 *
 * Web App Starter CodeIgniter-based
 *
 * @package   Racik
 * @copyright Copyright (c) 2019
 * @since     Version 1.2
 * @link      https://github.com/boedwinangun/racik
 * @filesource
 */

/**
 * Migration Interface
 *
 * All migrations should implement this, forces up() and down() and gives access
 * to the CI super-global.
 *
 * @todo Move this to a separate file and require it in the Migrations library.
 *
 * @package Racik\Modules\Migrations\Libraries\Migrations
 * @author  Phil Sturgeon http://philsturgeon.co.uk/
 */
abstract class Migration
{
    /** @var string The type of migration being run, either 'forge' or 'sql'. */
    public $migration_type = 'forge';

    //--------------------------------------------------------------------------

    /**
     * Abstract method run when increasing the schema version.
     *
     * Typically installs new data to the database or creates new tables.
     */
    abstract public function up();

    /**
     * Abstract method run when decreasing the schema version.
     */
    abstract public function down();

    /**
     * Getter method
     *
     * @param mixed $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }
}
