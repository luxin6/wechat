<?php
namespace wechat {
  /** Low level client for mp and enterprise */
  class client {

    protected $host;
    protected $connection;
    protected $cainfo;

    /**
     * Creates a new client
     * @param string $host Hostname
     * @param string $cainfo CA filename
     * @link http://curl.haxx.se/docs/caextract.html
     */
    public function __construct($host, $cainfo = null) {

      $connection = curl_init();
      curl_setopt_array($connection, array(
        CURLOPT_TIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => false
      ));

      // enable ssl verification if cainfo present...
      if (isset($cainfo)) {
        curl_setopt_array($connection, array(
          CURLOPT_SSL_VERIFYPEER => true,
          CURLOPT_CAINFO => $cainfo,
          CURLOPT_SSL_VERIFYHOST => 2
        ));
      }

      $this->host = $host;
      $this->connection = $connection;
      $this->cainfo = $cainfo;
    }

    /** Closes connection and releases resource */
    public function __destruct() {
      if ($this->connection)
        curl_close($this->connection);
    }

    const SEND = CURLOPT_POST;
    const READ = CURLOPT_HTTPGET;

    /**
     * Calls to remote method
     * @param int $method Action
     * @param string $path Path with parameters
     * @param string $payload Data
     *
     * @throws exception Remote exception
     * @throws \InvalidArgumentException Unrecognized action, expected client::READ or client::SEND
     * @throws \LogicException Set data not allowed
     * @return \stdClass
     */
    public function execute($method, $path, $payload = null) {

      // check method...
      if ($method !== self::READ && $method !== self::SEND)
        throw new \InvalidArgumentException('Unrecognized action, expected client::READ or client::SEND');

      $connection = &$this->connection;
      curl_setopt_array($connection, array(
        $method => true,
        CURLOPT_URL => $this->host.$path,
        CURLOPT_HTTPHEADER => array(),
        CURLOPT_POSTFIELDS => null
      ));

      // set request body...
      if (isset($payload)) {
        if ($method === self::READ) throw new \LogicException('Set data not allowed');
        curl_setopt_array($connection, array(
          CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
          CURLOPT_POSTFIELDS => $this->serialize($payload)
        ));
      }

      // send request and parse response as stdClass...
      return $this->response();
    }

    /**
     * Get response
     * @throws exception Server busy
     * @return \sdtClass
     */
    protected function response() {
      $result = curl_exec($this->connection);
      if ($result !== false && curl_getinfo($this->connection, CURLINFO_HTTP_CODE) < 400) return $this->parse($result);
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
     * Serialize array/object as JSON string
     * @param array|object $data Data
     * @return string
     */
    protected function serialize($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
  }
}
