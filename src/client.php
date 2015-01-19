<?php
namespace wechat {
  /** Low level client for mp and enterprise */
  class client {

    protected $host;
    protected $timeout;
    protected $cainfo;

    /**
     * Creates new client
     * @param string $host Host
     * @param string $cainfo CA filename
     * @param bool $timedout Timedout in seconds
     * @link http://curl.haxx.se/docs/caextract.html
     */
    public function __construct($host, $cainfo = null, $timedout = 10) {
      $this->host = $host;
      $this->timedout = $timedout;
      $this->cainfo = $cainfo;
    }

    private function init() {

      $session = curl_init();
      curl_setopt_array($session, array(
        CURLOPT_TIMEOUT => $this->timedout,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => false
      ));

      // enable ssl verification if cainfo present...
      if (isset($this->cainfo)) {
        curl_setopt_array($session, array(
          CURLOPT_SSL_VERIFYPEER => true,
          CURLOPT_CAINFO => $this->cainfo,
          CURLOPT_SSL_VERIFYHOST => 2
        ));
      }

      return $session;
    }

    const SEND = CURLOPT_POST;
    const READ = CURLOPT_HTTPGET;

    /**
     * Calls to remote method
     * @param int $method Action
     * @param string $path Path with parameters
     * @param string $data Data
     *
     * @throws exception Remote exception
     * @throws \InvalidArgumentException Unrecognized action, expected client::READ or client::SEND
     * @throws \LogicException Set data not allowed
     * @return \stdClass
     */
    public function execute($method, $path, $data = null) {

      // check method...
      if ($method !== self::READ && $method !== self::SEND)
        throw new \InvalidArgumentException('Unrecognized action, expected client::READ or client::SEND');

      $session = $this->init();
      curl_setopt_array($session, array(
        $method => true,
        CURLOPT_URL => $this->host.$path,
        CURLOPT_HTTPHEADER => array('Expect:')
      ));

      // set request body...
      if (isset($data)) {
        if ($method === self::READ) throw new \LogicException('Set data not allowed');
        $this->set_body($session, $data);
      }

      $result = $this->get_response($session);
      curl_close($session);
      return $result;
    }

    /**
     * Get response
     * @param resource $session HTTP request
     * @throws exception Server busy
     * @return \sdtClass
     */
    protected function get_response($session) {
      $result = curl_exec($session);
      if ($result !== false && curl_getinfo($session, CURLINFO_HTTP_CODE) < 400) return $this->parse($result);
      throw new exception('Server busy', exception::SERVER_BUSY);
    }

    /**
     * Parse json as stdClass, and check remote exception
     * @param string $data JSON string
     * @throws exception Remote exception or Malformed json found
     * @return \stdClass
     */
    protected function parse($data) {

      $data = json_decode($data);
      if ($errorcode = json_last_error()) {

        // use constant() to avoid undefined warning...
        switch ($errorcode) {
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

    /**
     * Set payload
     * @param resource $session HTTP request
     * @param array|object $value Data
     * @return void
     */
    protected function set_body($session, $value) {
      curl_setopt_array($session, array(
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_POSTFIELDS => json_encode($value,
          JSON_UNESCAPED_UNICODE)
      ));
    }
  }
}
