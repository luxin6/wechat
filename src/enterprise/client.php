<?php
namespace wechat\enterprise {

  /** Base client */
  class client extends \wechat\client {

    protected $id;
    protected $secret;
    protected $access_token;
    protected $access_token_expiration;

    /**
     * Creates a new enterprise client
     * @param string $id corpid
     * @param string $secret Secret of management group
     * @param string $cainfo CA filename
     * @param int $timedout Timedout in seconds
     */
    public function __construct($id, $secret, $cainfo = null, $timedout = 10, $host = 'https://qyapi.weixin.qq.com/cgi-bin') {
      parent::__construct($host, $cainfo, $timedout);
      $this->access_token_expiration = 0;
      $this->secret = $secret;
      $this->id = $id;
    }

    /**
     * Get access token
     * @param int $expiration Expiration
     * @throws exception Malformed token from server
     * @return string
     */
    public function access_token(&$expiration = 0) {

      // not found or expired
      if (!isset($this->access_token) || $this->access_token_expiration - time() <= 0) {

        // get response
        $response = $this->execute(self::READ, '/gettoken?'.http_build_query(array(
          'corpsecret' => $this->secret,
          'corpid' => $this->id
        )));

        // validate response...
        if (!isset($response->access_token) || preg_match('/^\s*$/', $response->access_token))
          throw new exception('Malformed ACCESS_TOKEN from server',
            exception::MALFORMED_ACCESS_TOKEN);

        // set expiration
        if (isset($response->expires_in)) {
          $value = filter_var($response->expires_in, FILTER_VALIDATE_INT);
          if ($value > 0)
            $this->access_token_expiration = strtotime(sprintf("+%d secs", $value));
        }

        $this->access_token = $response->access_token;
      }

      $expiration = $this->access_token_expiration - time();
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