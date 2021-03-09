<?php
/**
 * Social media posts plugin for Craft CMS 3.x
 *
 * Social media posts
 *
 * @link      WideWeb.pro
 * @copyright Copyright (c) 2020 WideWeb
 */

namespace wideweb\socialmediaposts\variables;

use wideweb\socialmediaposts\SocialMediaPosts;

use Craft;
use yii\db\Query;

/**
 * Social media posts Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.socialMediaPosts }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    WideWeb
 * @package   SocialMediaPosts
 * @since     1.0.0
 */
class SocialMediaPostsVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.socialMediaPosts.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.socialMediaPosts.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }

    // FUNCTIONS FOR FACEBOOK

    public function getPhotoUserFb($userId)
    {
        $dataFb = (new Query())->select(['fb_id', 'access_token'])
            ->from('{{%socialmediaposts_facebook}}')
            ->where("user_id = $userId")->one();
        if ($dataFb){
            $token = $dataFb['access_token'];
            $fb_id = $dataFb['fb_id'];
            $url = "https://graph.facebook.com/$fb_id?fields=picture&access_token=$token";
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/json;charset=utf-8\r\n",
                    'method'  => 'GET'
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $result = @json_decode($result);
            if (is_object($result)and isset($result->picture->data->url)){
                return  $result->picture->data->url;
            }
            return $result;

        }else{
            return false;
        }
    }

    public function checkLoginFb($userId)
    {
        $dataFb = (new Query())->select(['id'])
            ->from('{{%socialmediaposts_facebook}}')
            ->where("user_id = $userId")->one();
        if ($dataFb){
            return true;
        }
        return false;
    }

    public function getPostsFromFb($userId, $limit = 9)
    {
        $dataFb = (new Query())->select(['fb_id', 'access_token'])
            ->from('{{%socialmediaposts_facebook}}')
            ->where("user_id = $userId")->one();
        if ($dataFb){
            $token = $dataFb['access_token'];
            $fb_id = $dataFb['fb_id'];
            $url = "https://graph.facebook.com/$fb_id/posts/?access_token=$token&fields=full_picture,permalink_url,comments.summary(true),message,created_time&limit=$limit";
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/json;charset=utf-8\r\n",
                    'method'  => 'GET'
                )
            );
            $context  = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            $result = @json_decode($result);
            if (is_object($result)){
            return  $result->data;
            }
            return $result;

        }else{
            return false;
        }

    }

    // FUNCTIONS FOR INSTAGRAM

    public function checkLoginInst($userId)
    {
        $dataFb = (new Query())->select(['id'])
            ->from('{{%socialmediaposts_instagram}}')
            ->where("user_id = $userId")->one();
        if ($dataFb){
            return true;
        }
        return false;
    }

    public function getPostsFromInst($userId, $limit = 9)
    {
        $dataInst = (new Query())->select(['inst_id', 'access_token'])
            ->from('{{%socialmediaposts_instagram}}')
            ->where("user_id = $userId")->one();
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
        $result = json_decode($response);
        return $result->data;
        }else{
            return false;
        }
    }

}
