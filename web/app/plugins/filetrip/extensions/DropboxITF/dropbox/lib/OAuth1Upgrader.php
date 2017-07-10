<?php
namespace DropboxITF;

/**
 * Lets you convert OAuth 1 access tokens to OAuth 2 access tokens.  First call {@link
 * If that succeeds, call {@link OAuth1AccessTokenUpgrader::disableOAuth1AccessToken()}
 * to disable the OAuth 1 access token.
 *
 * <code>
 * use \Dropbox as dbx;
 * $appInfo = dbx\AppInfo::loadFromJsonFile(...);
 * $clientIdentifier = "my-app/1.0";
 * $oauth1AccessToken = dbx\OAuth1AccessToken(...);
 *
 * $upgrader = new dbx\OAuth1AccessTokenUpgrader($appInfo, $clientIdentifier, ...);
 * $oauth2AccessToken = $upgrader->getOAuth2AccessToken($oauth1AccessToken);
 * $upgrader->disableOAuth1AccessToken($oauth1AccessToken);
 * </code>
 */
class OAuth1Upgrader extends AuthBase
{

    /**
     * Make a Dropbox API call to disable the given OAuth 1 access token.
     *
     * See <a href="https://www.dropbox.com/developers/documentation/http/documentation#auth-token-revoke">/oauth2/token/revoke</a>.
     *
     * @param OAuth1AccessToken $oauth1AccessToken
     *
     * @throws Exception
     */
    function disableOAuth1AccessToken($oauth1AccessToken)
    {
        OAuth1AccessToken::checkArg("oauth1AccessToken", $oauth1AccessToken);

        $response = self::doPost($oauth1AccessToken, "oauth2/token/revoke");

        if ($response->statusCode !== 200) throw RequestUtil::unexpectedStatus($response);
    }

    /**
     * @param OAuth1AccessToken $oauth1AccessToken
     * @param string $path
     *
     * @return HttpResponse
     *
     * @throws Exception
     */
    private function doPost($oauth1AccessToken, $path)
    {
        // Construct the OAuth 1 header.
        $signature = rawurlencode($this->appInfo->getSecret()) . "&" . rawurlencode($oauth1AccessToken->getSecret());
        $authHeaderValue = "OAuth oauth_signature_method=\"PLAINTEXT\""
             . ", oauth_consumer_key=\"" . rawurlencode($this->appInfo->getKey()) . "\""
             . ", oauth_token=\"" . rawurlencode($oauth1AccessToken->getKey()) . "\""
             . ", oauth_signature=\"" . $signature . "\"";

        return RequestUtil::doPostWithSpecificAuth(
            $this->clientIdentifier, $authHeaderValue, $this->userLocale,
            $this->appInfo->getHost()->getApi(),
            $path,
            null);
    }
}
