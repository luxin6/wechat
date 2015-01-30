<?php
namespace wechat\mp {

  /** Multimedia management client */
  class media extends client {

    const VOICE = 'voice';
    const PICTURE = 'image';
    const THUMBNAILS = 'thumb';
    const VIDEO = 'video';

    private $cached_host;

    // switch host to http://file.api.weixin.qq.com/cgi-bin...
    private function fuck_host() {
      $this->cached_host = $this->host;
      $this->host = 'http://file.api.weixin.qq.com/cgi-bin';
    }

    // restore host...
    private function end_host() {
      $this->host = $this->cached_host;
      $this->cached_host = null;
    }

    /**
     * Upload media to wechat server
     * @param string $type Type
     * @param string $resource Stream or filename with "@" prefix
     * @param string $extension File extension name
     *
     * @throws \InvalidArgumentException Can not read file contents
     * @return \stdClass 
     */
    public function upload($type, $resource, $extension = null) {

      $data = $resource;

      // read to memory...
      if (strpos($resource, '@') === 0) {
        $data = file_get_contents(substr($resource, 1));
        if ($data === false) throw new \InvalidArgumentException(error_get_last()['message']);
        $this->extension = pathinfo($resource, PATHINFO_EXTENSION);
      }

      // generate file extension...
      if (strlen($this->extension) === 0) {
        if (isset($extension))
          $this->extension = $extension;
        else {
          switch ($type) {
            case self::PICTURE: $this->extension = 'jpg'; break;
            case self::VOICE: $this->extension = 'amr'; break;
            case self::THUMBNAILS: $this->extension = 'jpg'; break;
            case self::VIDEO: $this->extension = 'mp4'; break;
          }
        }
      }

      $access_token = $this->access_token();
      $this->fuck_host();
      
      // send request...
      $result = $this->execute(self::SEND, '/media/upload?'.http_build_query(array(
        'access_token' => $access_token,
        'type' => $type
      )), $data);

      $this->end_host();
      $this->extension = null;
      return $result;
    }

    /** The shared variable to holds file extension */
    protected $extension;

    /**
     * Set payload
     * @param resource $session HTTP request
     * @param array|object $value Picture binary string
     * @return void
     */
    protected function set_body($session, $value) {
      $boundary = uniqid();
      curl_setopt_array($session, array(
        CURLOPT_HTTPHEADER => array('Content-Type: multipart/form-data; boundary='.$boundary, 'Expect:'),
        CURLOPT_POSTFIELDS => sprintf(
          "--%s\r\nContent-Disposition: form-data; name=media; filename=\"%s\"\r\nContent-Type: application/octet-stream\r\n\r\n%s\r\n--%s--\r\n",
          $boundary, // start
          md5(microtime(true)).".{$this->extension}", // filename
          $value, // binary string
          $boundary // ends
        )
      ));
    }

    /**
     * Generate media url
     * @param string $media_id A MediaId from https://qyapi.weixin.qq.com/cgi-bin/media/upload
     * @return string
     */
    public function generate_url($media_id) {
      return "{$this->host}/media/get?".http_build_query(array(
        'access_token' => $this->access_token(),
        'media_id' => $media_id
      ));
    }
  }
}