<?php
namespace wechat {
  /**
   * Encryption and signature management
   * @link http://qydev.weixin.qq.com/wiki/index.php?title=%E5%8A%A0%E8%A7%A3%E5%AF%86%E6%96%B9%E6%A1%88%E7%9A%84%E8%AF%A6%E7%BB%86%E8%AF%B4%E6%98%8E
   */
  class secret {

    protected $id;
    protected $secret;
    protected $salt;
    protected $initialvector;

    /**
     * Creates a new secret
     * @param string $id CORPID
     * @param string $secret base64 encoded AES secret (aka EncodingAESKey)
     * @param string $salt Token
     * @throws \InvalidArgumentException Malformed secret, not a base64 string/length not equals 32
     */
    public function __construct($id, $secret, $salt) {

      // validate secret...
      $secret = base64_decode($secret);
      if ($secret === false || strlen($secret) !== 32)
        throw new \InvalidArgumentException('Malformed secret, see document for more information');

      $this->id = $id;
      $this->secret = $secret;
      $this->salt = $salt;
      $this->initialvector = substr($secret, 0, 16);
    }

    // add PKCS7 Padding
    protected function pad($data) {
      $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
      $character = $size - (strlen($data) % $size);
      return $data.str_repeat(chr($character), $character);
    }

    /**
     * Encrypt plaintext
     * @param string $data Plaintext
     * @return string Ciphertext
     */
    public function encrypt($data) {
      return base64_encode(mcrypt_encrypt(
        MCRYPT_RIJNDAEL_128,
        $this->secret,
        $this->pad(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM).pack('N', strlen($data)).$data.$this->id),
        MCRYPT_MODE_CBC,
        $this->initialvector
      ));
    }

    /**
     * Decrypt ciphertext
     * @param string $data Ciphertext
     * @throws exception Decryption failed, not a base64 string or data structure invalid
     * @return string Plaintext
     */
    public function decrypt($data) {

      // unpack data...
      if (($data = base64_decode($data, true)) === false)
        throw new exception('Illegal base64 string present', exception::INVALID_ENCRYPTION);

      // decrypt...
      $data = mcrypt_decrypt(
        MCRYPT_RIJNDAEL_128, // algorithm
        $this->secret, // secret
        $data, // data
        MCRYPT_MODE_CBC, // mode
        $this->initialvector // initial verctor
      );

      if ($data !== false) {
        $length = strlen($data);
        if ($length >= 20) {
          $size = unpack('N', substr($data, 16, 4))[1];
          if ($length - 20 < $size) throw new exception('Malformed data', exception::INVALID_ENCRYPTION);
          return substr($data, 20, $size);
        }
      }

      throw new exception('Decryption failed',
        exception::INVALID_ENCRYPTION);
    }

    /**
     * Generate signature
     * @param int $timestamp Unix timestamp
     * @param string $nonce nonce
     * @param string $data data
     * @param bool $binary returns binary string instead of hex string
     * @return string
     */
    public function generate_signature($timestamp, $nonce, $data, $binary = false) {
      $data = array($this->salt, $timestamp, $nonce, $data);
      sort($data, SORT_STRING);
      return sha1(implode($data), $binary);
    }
  }
}