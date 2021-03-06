<?php
namespace wechat\enterprise {

  /** Multimedia management client */
  class media extends client {

    const VIDEO = 'video';
    const PICTURE = 'image';
    const FILE = 'file';
    const RECORDING = 'voice';

    /**
     * Upload media to wechat server
     * @param string $type Filetype of media
     * @param string $resource Stream or filename with "@" prefix
     * @param string $extension File extension name
     * @throws \InvalidArgumentException Can not read file contents
     * @return \stdClass Result
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
            case self::VIDEO: $this->extension = 'mp4'; break;
            case self::RECORDING: $this->extension = 'amr'; break;
            case self::FILE: $this->extension = 'bin'; break;
            case self::PICTURE: $this->extension = 'jpg'; break;
          }
        }
      }

      // send request...
      $result = $this->execute(self::SEND, '/media/upload?'.http_build_query(array(
        'access_token' => $this->access_token(),
        'type' => $type
      )), $data);

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