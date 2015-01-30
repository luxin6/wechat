<?php
namespace wechat\mp {

  /** Base client */
  class client extends \wechat\client {

    protected $id;
    protected $secret;
    protected $access_token;
    protected $expiration;

    /**
     * Creates a new client
     * @param string $id appid
     * @param string $secret appsecret
     * @param string CA filename
     * @param int $timedout Timedout in seconds
     */
    public function __construct($id, $secret, $cainfo = null, $timedout = 10, $host = 'https://api.weixin.qq.com/cgi-bin') {
      parent::__construct($host, $cainfo, $timedout);
      $this->expiration = 0;
      $this->secret = $secret;
      $this->id = $id;
    }

    /**
     * Get access token
     * @param int $expiration Expiration in seconds
     * @throws exception Malformed response from remote service
     * @return string
     */
    public function access_token(&$expiration = 0) {

      // not found or expired
      if (!isset($this->access_token) || $this->expiration - time() <= 0) {

        // send request...
        $response = $this->execute(self::READ, '/token?'.http_build_query(array(
          'grant_type' => 'client_credential',
          'secret' => $this->secret,
          'appid' => $this->id
        )));

        // validate token
        if (!isset($response->access_token) || preg_match('/^\s*$/', $response->access_token))
          throw new exception('Malformed ACCESS_TOKEN from server',
            exception::INVALID_CREDENTIAL);

        // set expiration
        if (isset($response->expires_in)) {
          $value = filter_var($response->expires_in, FILTER_VALIDATE_INT);
          if ($value > 0)
            $this->expiration = strtotime(sprintf("+%d secs", $value));
        }

        $this->access_token = $response->access_token;
      }

      $expiration = $this->expiration - time();
      return $this->access_token;
    }

    /**
     * Set access token for using in future, set as null to clear it
     * @param string $value Access token
     * @param int $expiration Expiration in seconds
     * @return void
     */
    public function set_access_token($value, $expiration = 0) {
      $this->access_token = $value;
      $this->expiration = isset($value) ? strtotime(sprintf("+%d secs", $expiration)) : 0;
    }
  }
}