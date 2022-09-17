<?php

namespace Mac\Test;

use Exception;

class GithubOAuth
{
    public function __construct(
        public String $authorizeURL = "https://github.com/login/oauth/authorize",
        public String $tokenURL = "https://github.com/login/oauth/access_token",
        public String $apiURLBase = "https://api.github.com",
        public String $clientID = CLIENT_ID,
        public String $clientSecret = CLIENT_SECRET,
        public String $redirectURL = REDIRECT_URL
    )
    {}

    public function getAuthorizedUrl(): string
    {
      return $this->authorizeURL.'?'.http_build_query([
              'client_id' => CLIENT_ID,
              'redirect_url' => REDIRECT_URL,
              'state' =>  hash('sha256', microtime(TRUE).rand()),
              'scope' => 'user:email'
          ]);
    }

    public function getAccessToken($oauthCode)
    {
       $token = self::apiRequest(
           $this->tokenURL.'?'.http_build_query([
               'client_id' => CLIENT_ID,
               'client_secret' => CLIENT_SECRET,
               'state' => hash('sha256', microtime(TRUE).rand()),
               'code' => $oauthCode
           ])
       );
       return $token->access_token;
    }

    private function apiRequest($accessTokenUrl)
    {
        $apiURL = filter_var($accessTokenUrl, FILTER_VALIDATE_URL)
            ?  $accessTokenUrl
            :  $this->apiURLBase.'/user?access_token='.$accessTokenUrl;
        $context = stream_context_create([
            'http' => [
                'user_agent' => 'Github OAuth',
                'header' => 'Accept: application/json'
            ]
        ]);
        $response = file_get_contents($apiURL, false, $context);

        return $response ? json_decode($response) : $response;
    }

    /**
     * @throws Exception
     */
    public function getAuthenticatedUser($access_token){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiURLBase.'/user',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$access_token,
                'Cookie: _octo=GH1.1.1032220589.1663262708; logged_in=no',
                'user-agent: node.js'
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if($httpCode !==200){
            if(curl_errno($curl)){
                $errorMsg = curl_error($curl);
            }else{
                $errorMsg = $response;
            }
            throw new Exception('Error '.$httpCode.': '.$errorMsg);
        }else{
            return json_decode($response);
        }
    }

}