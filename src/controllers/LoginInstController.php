<?php
/**
 * Social media posts plugin for Craft CMS 3.x
 *
 * Social media posts
 *
 * @link      WideWeb.pro
 * @copyright Copyright (c) 2020 WideWeb
 */

namespace wideweb\socialmediaposts\controllers;

use wideweb\socialmediaposts\SocialMediaPosts;

use Craft;
use craft\web\Controller;
use yii\db\Query;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    WideWeb
 * @package   SocialMediaPosts
 * @since     1.0.0
 */
class LoginInstController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['login-inst', 'register-user-fb', 'logout-inst'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/social-media-posts/default
     *
     * @return mixed
     */

    public function actionLogoutInst()
    {
        //ToDo delete user from table socialmediaposts_facebook after logout from app facebook
        return Craft::$app->db->createCommand()->delete('{{%socialmediaposts_instagram}}',
            [
                'user_id' => Craft::$app->user->getIdentity()->id
            ]
        )->execute();
    }

    public function actionLoginInst()
    {
        $code = $_GET['code'];
        $redirect = Craft::$app->sites->currentSite->baseUrl;
        $checkUserId = (new Query())->select('user_id')->from('{{%socialmediaposts_instagram}}')
        ->where(['user_id' => Craft::$app->user->getIdentity()->id])->one();
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.instagram.com/oauth/access_token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
                [
                    'client_id' => Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdInst,
                    'client_secret' => Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdInstSecret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirect . 'actions/social-media-posts/login-inst/login-inst',
                    'code' => $code,
                ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        $result = json_decode($response);
        $userId = $result->user_id;
        $accessToken = $result->access_token;
        // time long token
        $checkUserIdInst = (new Query())->select('user_id')->from('{{%socialmediaposts_instagram}}')
            ->where(['inst_id' => $userId])->one();
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://graph.instagram.com/access_token?access_token=$accessToken&grant_type=ig_exchange_token&client_secret=" . Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdInstSecret,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        $result = json_decode($response);
        $accessToken = $result->access_token;
        if (empty($checkUserIdInst) and empty($checkUserId)){
            Craft::$app->db->createCommand()->insert('{{%socialmediaposts_instagram}}',
                [
                    'user_id' => Craft::$app->user->getIdentity()->id,
                    'inst_id' => $userId,
                    'access_token' => $accessToken,
                ]
            )->execute();
        }else{
            Craft::$app->db->createCommand()->update('{{%socialmediaposts_instagram}}',
                [
                    'user_id' => Craft::$app->user->getIdentity()->id,
                    'inst_id' => $userId,
                    'access_token' => $accessToken,
                ],
                "inst_id = " .  $userId
            )->execute();
        }
        return Craft::$app->getResponse()->redirect('/site-preview');
    }

}
