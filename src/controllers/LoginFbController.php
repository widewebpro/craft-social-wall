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
class LoginFbController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['login-fb', 'register-user-fb', 'logout-fb'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/social-media-posts/default
     *
     * @return mixed
     */
    public function actionLoginFb()
    {
        $data = @json_decode($_GET['data']);
        if (is_object($data)){
        $access_token = $data->accessToken;
        $fb_id = $data->userID;
        $user_id = Craft::$app->user->getIdentity()->id;
        $checkUserId = (new Query())->select('id')->from('{{%socialmediaposts_facebook}}')
            ->where("user_id = $user_id")->one();
        $checkUserIdFb = (new Query())->select('id')->from('{{%socialmediaposts_facebook}}')
            ->where("fb_id = $fb_id")->one();
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://graph.facebook.com/oauth/access_token?fb_exchange_token=$access_token&grant_type=fb_exchange_token&client_id=" . Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdFb . "&client_secret=" . Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdFbSecret,
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
        $access_token = $result->access_token;

        if (empty($checkUserId) and empty($checkUserIdFb)){
        return Craft::$app->db->createCommand()->insert('{{%socialmediaposts_facebook}}',
            [
                'user_id' => $user_id,
                'fb_id' => $fb_id,
                'access_token' => $access_token,
            ]
        )->execute();
        }else {
            return Craft::$app->db->createCommand()->update('{{%socialmediaposts_facebook}}',
                [
                    'user_id' => $user_id,
                    'fb_id' => $fb_id,
                    'access_token' => $access_token,
                ],
                "fb_id = " .  $fb_id
            )->execute();
        }
        }
        return 'Bad request';
    }

    public function actionLogoutFb()
    {
        //ToDo delete user from table socialmediaposts_facebook after logout from app facebook
        return Craft::$app->db->createCommand()->delete('{{%socialmediaposts_facebook}}',
            [
                'user_id' => Craft::$app->user->getIdentity()->id
            ]
        )->execute();
    }
    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/social-media-posts/default/do-something
     *
     * @return mixed
     */

    private static function base64_url_decode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    private static function parseSignedRequest() {
        if (isset($_REQUEST['signed_request'])) {
            $signed_request = $_REQUEST['signed_request'];
            list($encoded_sig, $payload) = explode('.', $signed_request, 2);

            // decode the data
            $sig = self::base64_url_decode($encoded_sig);
            $data = json_decode(self::base64_url_decode($payload), true);

            if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
                return false;
            }

            // Adding the verification of the signed_request below
            $expected_sig = hash_hmac('sha256', $payload, '8034a937a1471dde6848221c7f6dc8ce', $raw = true);
            if ($sig !== $expected_sig) {
                return false;
            }

            return $data;
        } else {
            return false;
        }
    }

    public function actionRegisterUserFb()
    {
        return "
        <script src='https://connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v9.0&appId=". Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdFb ."&autoLogAppEvents=1'>
    window.fbAsyncInit = function() {
        FB.init({
            appId      : " . Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdFb . ",
            cookie     : true,
            xfbml      : true,
            version    : 'v9.0'
        });
        FB.Event.subscribe('auth.login', function(response) {
            window.location.href='/actions/social-media-posts/default/do-something';
        });
        FB.AppEvents.logPageView();

    };

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/en_US/sdk.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    </script>
    <script type='text/javascript'>
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected'){
            data = response.authResponse;
            data['CRAFT_CSRF_TOKEN'] = '" . Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdFb . "';
            console.log(data);
            const url = '/actions/social-media-posts/login-fb/login-fb?data=' + JSON.stringify(data);
            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.text())
                .then(result => console.log(result))
                .catch(error => console.log('error', error));

        }
    });
    window.location = '" . Craft::$app->getRequest()->referrer . "'
</script>
" ;

    }
}
