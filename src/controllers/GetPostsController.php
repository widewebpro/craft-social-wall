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
class GetPostsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['get-posts-from-socials'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/social-media-posts/default
     *
     * @return mixed
     */

    public function actionGetPostsFromSocials($userId, $limit=100)
    {
        $dataInst = (new Query())->select(['inst_id', 'access_token'])
            ->from('{{%socialmediaposts_instagram}}')
            ->where("user_id = $userId")->one();
        $data = [];
        if ($dataInst){
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://graph.instagram.com/me/media?limit=$limit&fields=id,caption,media_url,permalink,thumbnail_url,timestamp,message&access_token=". $dataInst['access_token'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET"
            ]);

            $response = curl_exec($curl);

            curl_close($curl);
            $result = @json_decode($response);
            if (is_object($result) and isset($result->data))
            $data['instagram'] = $result->data;
        }
        $dataFb = (new Query())->select(['fb_id', 'access_token'])
            ->from('{{%socialmediaposts_facebook}}')
            ->where("user_id = $userId")->one();
        if ($dataFb){
            $token = $dataFb['access_token'];
            $fb_id = $dataFb['fb_id'];
            $appsecret_proof= hash_hmac('sha256', $token, Craft::$app->plugins->getPlugin('social-media-posts')->getSettings()->appIdFbSecret);
            $url = "https://graph.facebook.com/$fb_id/posts/?access_token=$token&fields=full_picture,permalink_url,is_private=0,comments.summary(true),message,created_time,privacy&limit=$limit&appsecret_proof=$appsecret_proof";
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/json;charset=utf-8\r\n",
                    'method'  => 'GET'
                )
            );
            $context  = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            $result = @json_decode($result);
            if (is_object($result) and isset($result->data)){
                $data = $result->data;
                $newData = [];
                foreach ($data as $key =>  $item){
                    if($item->privacy->value == 'EVERYONE'){
                        array_push($newData, $item);
                    }
                }
                $data['facebook'] = $newData;
            }
        }
        return \GuzzleHttp\json_encode($data);
    }

}
