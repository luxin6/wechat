<?php
namespace wechat\enterprise {

  /**
   * Foundation of wechat enterprise
   * @link http://qydev.weixin.qq.com/wiki/index.php?title=%E9%A6%96%E9%A1%B5
   */
  class client extends \wechat\client {

    protected $id;
    protected $secret;

    /**
     * Creates a new enterprise client
     * @param string $id corpid
     * @param string $secret Secret of management group
     * @param string $cainfo CA filename
     */
    public function __construct($id, $secret, $cainfo = null) {
      parent::__construct('https://qyapi.weixin.qq.com/cgi-bin', $cainfo);
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

      // get response
      $response = $this->execute(self::READ, '/gettoken?'.http_build_query(array(
        'corpsecret' => $this->secret,
        'corpid' => $this->id
      )));

      // validate response...
      if (!isset($response->access_token) || !preg_match('!^[\w\-\+\=/]+$!', $response->access_token))
        throw new exception('Malformed ACCESS_TOKEN from server',
          exception::MALFORMED_ACCESS_TOKEN);

      // set expiration
      if (isset($response->expires_in)) {
        $value = filter_var($response->expires_in, FILTER_VALIDATE_INT);
        if ($value > 0)
          $expiration = $value;
      }

      // return token
      return $response->access_token;
    }
  }
}