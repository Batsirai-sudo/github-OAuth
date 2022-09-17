<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

use Mac\Test\GithubOAuth;

const CLIENT_ID = "cad1eb7a063577f50a3d";
const CLIENT_SECRET = "5110221aaec375b42847bbddb67c07485e3e86a9";
const REDIRECT_URL = 'https:// localhost:9001';
session_start();

$gitClient = new GithubOAuth();

$authorizedUrl = $gitClient->getAuthorizedUrl();

if (isset($_SESSION['access_token'])){
    $accessToken = $_SESSION['access_token'];

}

if(isset($accessToken)){
    try {
        $gitUser = $gitClient->getAuthenticatedUser($accessToken);
    } catch (Exception $e) {
    }
    $output = json_encode($gitUser);

}elseif (isset($_GET["code"])){
    $accessToken = $gitClient->getAccessToken($_GET["code"]);
    $_SESSION['access_token'] = $accessToken;
    header('Location: ./');
}
else{
    $authURL = $gitClient->getAuthorizedUrl();

    $output = '<a href="'.htmlspecialchars($authURL).'">Sign up with github</a>';
}

?>

<html lang="">
<head><title>hi</title></head>
<body>
<?= $output ?>
</body>
</html>
