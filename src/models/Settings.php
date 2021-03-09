<?php
/**
 * Social media posts plugin for Craft CMS 3.x
 *
 * Social media posts
 *
 * @link      WideWeb.pro
 * @copyright Copyright (c) 2020 WideWeb
 */

namespace wideweb\socialmediaposts\models;

use wideweb\socialmediaposts\SocialMediaPosts;

use Craft;
use craft\base\Model;

/**
 * SocialMediaPosts Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    WideWeb
 * @package   SocialMediaPosts
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $appIdFb = 'App Id Facebook';
    public $appIdInst = 'App Id Instagram';
    public $appIdInstSecret = 'App Id Instagram Secret';
    public $appIdFbSecret = 'App Id Facebook Secret';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['appIdFb', 'string'],
            ['appIdInst', 'string'],
            ['appIdInstSecret', 'string'],
            ['appIdFbSecret', 'string'],
        ];
    }
}
