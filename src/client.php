<?php
namespace wechat {
  /** Low-level client */
  class client {

    protected $id;
    protected $secret;
    protected $host;
    protected $session;

    /**
     * Creates a new client
     * @param string $id CORPID
     * @param string $secret Secret of management group
     * @param string $cainfo CA filename
     * @param string $host Host
     * @link http://curl.haxx.se/docs/caextract.html
     */
    public function __construct($id, $secret, $cainfo = null, $host = null) {

      $session = curl_init();
      curl_setopt_array($session, array(
        CURLOPT_TIMEOUT => 120,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => false
      ));

      // enable ssl verification...
      if (isset($cainfo)) {
        curl_setopt_array($session, array(
          CURLOPT_SSL_VERIFYPEER => true,
          CURLOPT_SSL_VERIFYHOST => 2,
          CURLOPT_CAINFO => $cainfo
        ));
      }

      // init...
      $this->id = $id;
      $this->secret = $secret;
      $this->host = isset($host) ? $host : 'https://qyapi.weixin.qq.com/cgi-bin';
      $this->session = $session;
    }

    /** Closes connection and releases resource */
    public function __destruct() {
      if ($this->session) {
        curl_close($this->session);
      }
    }

    const SEND = CURLOPT_POST;
    const READ = CURLOPT_HTTPGET;

    /**
     * Calls to remote method
     * @param int $method Request type
     * @param string $path Path
     * @param array $queries Array of arguments
     * @param object $payload Data
     *
     * @throws exception Remote method exception
     * @throws \InvalidArgumentException Unrecognized request type, expected client::READ or client::SEND
     * @throws \LogicException Set data not allowed
     * @return \stdClass Result
     */
    public function execute($method, $path, array $queries = array(), $payload = null) {

      // check method...
      if ($method !== self::SEND &&
          $method !== self::READ)
        throw new \InvalidArgumentException('unrecognized method was given');

      // append access_token...
      $queries['access_token'] = $this->access_token();

      $session = $this->session;
      curl_setopt_array($session, array(
        $method => true,
        CURLOPT_URL => $this->host.$path.'?'.http_build_query($queries),
        CURLOPT_HTTPHEADER => array(),
        CURLOPT_POSTFIELDS => null
      ));

      // add payload...
      if (isset($payload)) {
        if ($method === self::READ) throw new \LogicException('set payload not allowed here');
        curl_setopt_array($session, array(
          CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
          CURLOPT_POSTFIELDS => $this->serialize($payload)
        ));
      }

      return $this->response();
    }

    protected function access_token(&$expiration = 0) {

      $session = $this->session;
      curl_setopt_array($session, array(
        CURLOPT_HTTPGET => true,
        CURLOPT_URL => $this->host.'/gettoken?'.http_build_query(array(
          'corpsecret' => $this->secret,
          'corpid' => $this->id
        ))
      ));

      $response = $this->response();
      if (!isset($response->access_token) || !preg_match('!^[\w\-\+\=/]+$!', $response->access_token))
        throw new exception('Malformed ACCESS_TOKEN from server',
          exception::MALFORMED_ACCESS_TOKEN);

      // find & set expiration
      if (isset($response->expires_in)) {
        $value = filter_var($response->expires_in, FILTER_VALIDATE_INT);
        if ($value > 0)
          $expiration = $value;
      }

      return $response->access_token;
    }

    protected function response() {
      $response = curl_exec($this->session);
      if ($response !== false && curl_getinfo($this->session, CURLINFO_HTTP_CODE) < 400) return $this->parse($response);
      throw new exception('Server busy', exception::SERVER_BUSY);
    }

    protected function parse($data) {

      $data = json_decode($data);
      if ($error = json_last_error()) {

        // use constant() to avoid undefined warning...
        switch ($error) {
          case constant('JSON_ERROR_UTF8'): throw new exception('Malformed UTF-8 characters, possibly incorrectly encoded', exception::MALFORMED_JSON);
          case constant('JSON_ERROR_SYNTAX'): throw new exception('Syntax error', exception::MALFORMED_JSON);
          case constant('JSON_ERROR_STATE_MISMATCH'): throw new exception('Invalid or malformed JSON', exception::MALFORMED_JSON);
          case constant('JSON_ERROR_CTRL_CHAR'): throw new exception('Control character error, possibly incorrectly encoded', exception::MALFORMED_JSON);
          case constant('JSON_ERROR_DEPTH'): throw new exception('The maximum stack depth has been exceeded', exception::MALFORMED_JSON);
          case constant('JSON_ERROR_RECURSION'): throw new exception('One or more recursive references in the value to be encoded', exception::MALFORMED_JSON);
          case constant('JSON_ERROR_UNSPPORTED_TYPE'): throw new exception('A value of a type that cannot be encoded was given', exception::MALFORMED_JSON);
          case constant('JSON_ERROR_INF_OR_NAN'): throw new exception('One or more NAN or INF in the value to be encoded', exception::MALFORMED_JSON);
        }
      }

      if (isset($data->errcode) && $data->errcode != 0)
        throw new exception(isset($data->errmsg) ? $data->errmsg : null, $data->errcode);
      return $data;
    }

    protected function serialize($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
  }
}