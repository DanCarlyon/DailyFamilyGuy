<?php
require "autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

class postImageToTwitter extends curl_request {


    public function runProgram() {
        $selectedImage = $this->_getAnImageToUse();
        $connection = $this->_verifyLogin();
        $this->_postSelectedImageToTwitter($selectedImage['image']);

        file_put_contents('test.gif', file_get_contents($selectedImage['image']));

        $access_token = ''; //user access token
        $access_token_secret = ''; //user secret
        $connection = new TwitterOAuth('', '', $access_token, $access_token_secret); //client tokens

        $media2 = $connection->upload('media/upload', ['media' => 'test.gif']);
        $parameters = [
            'status' => '#FamilyGuy #DailyFamilyGuy',
            'media_ids' => $media2->media_id_string
        ];
        if($connection->post('statuses/update', $parameters)) {
            echo 'Post Success'.PHP_EOL;
        }
    }

    private function _getAnImageToUse() {
        $_img = $this->getResponseFromGiphy('family guy');
        while(!is_array($_img)) {
            $_img = $this->getResponseFromGiphy('family guy');
        }
        return $_img;
    }

    private function _postSelectedImageToTwitter($image)
    {
        $url = "https://api.twitter.com/1.1/statuses/update.json";
        $variables = array(
            'status'=>'test'
        );
        $variables_string = '';
        foreach($variables as $key=>$value) { $variables_string .= $key.'='.$value.'&'; }
        rtrim($variables_string, '&');
        $headers = array(
            "POST /1.1/statuses/update.json".$variables_string." HTTP/1.1",
            "Host: api.twitter.com",
            "User-Agent: DanCarlyon Twitter Family Guy App Application-only OAuth App v.1",
            "Authorization: Bearer ".$this->bearerToken
        );
        $ch = curl_init();  // setup a curl
        curl_setopt($ch, CURLOPT_URL,$url);  // set url to send to
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // set custom headers
        curl_setopt($ch,CURLOPT_POST, count($variables));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $variables_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return output
        $response = curl_exec ($ch); // execute the curl
        curl_close($ch); // close the curl
        print_r($response);
    }

    private function _verifyLogin()
    {
        $access_token = '';
        $access_token_secret = '';
        $connection = new TwitterOAuth('', '', $access_token, $access_token_secret);
        return $connection->get("account/verify_credentials");
    }

}