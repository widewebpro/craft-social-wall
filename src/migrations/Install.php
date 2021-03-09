<?php
/**
 * Social media posts plugin for Craft CMS 3.x
 *
 * Social media posts
 *
 * @link      WideWeb.pro
 * @copyright Copyright (c) 2020 WideWeb
 */

namespace wideweb\socialmediaposts\migrations;

use wideweb\socialmediaposts\SocialMediaPosts;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * Social media posts Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    WideWeb
 * @package   SocialMediaPosts
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

    // socialmediaposts_facebook table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%socialmediaposts_facebook}}');
        $tableSchema2 = Craft::$app->db->schema->getTableSchema('{{%socialmediaposts_instagram}}');

        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%socialmediaposts_facebook}}',
                [
                    'id' => $this->primaryKey(),
                    'user_id' => $this->string(255)->notNull()->unique(),
                    'fb_id' => $this->string(255)->notNull()->unique(),
                    'access_token' => $this->string(255)->notNull(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                // Custom columns in the table
//                    'siteId' => $this->integer()->notNull(),
                ]
            );
        }

        if ($tableSchema2 === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%socialmediaposts_instagram}}',
                [
                    'id' => $this->primaryKey(),
                    'user_id' => $this->string(255)->notNull()->unique(),
                    'inst_id' => $this->string(255)->notNull()->unique(),
                    'access_token' => $this->string(255)->notNull(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    // Custom columns in the table
//                    'siteId' => $this->integer()->notNull(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
    // socialmediaposts_facebook table
//        $this->createIndex(
//            $this->db->getIndexName(
//                '{{%socialmediaposts_facebook}}',
//                'some_field',
//                true
//            ),
//            '{{%socialmediaposts_facebook}}',
//            'some_field',
//            true
//        );
//        // Additional commands depending on the db driver
//        switch ($this->driver) {
//            case DbConfig::DRIVER_MYSQL:
//                break;
//            case DbConfig::DRIVER_PGSQL:
//                break;
//        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
    // socialmediaposts_facebook table
//        $this->addForeignKey(
//            $this->db->getForeignKeyName('{{%socialmediaposts_facebook}}', 'siteId'),
//            '{{%socialmediaposts_facebook}}',
//            'siteId',
//            '{{%sites}}',
//            'id',
//            'CASCADE',
//            'CASCADE'
//        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
    // socialmediaposts_facebook table
        $this->dropTableIfExists('{{%socialmediaposts_facebook}}');
        $this->dropTableIfExists('{{%socialmediaposts_instagram}}');
    }
}
