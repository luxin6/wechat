<?php
namespace wechat {
	/*final*/ class secret {

		protected $id;
		protected $secret;
		protected $salt;
		protected $initialvector;

		/**
		 * 实例化类型
		 * @param string $id CORPID
		 * @param string $secret 应用的经过 Base64 编码的 AES 密钥
		 * @param string $salt 应用的 Token
		 * @throws \InvalidArgumentException 给定 AES 密钥无效或长度不为 32 字节
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
		 * 加密数据
		 * @param string $data 数据明文
		 * @return string 密文
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
		 * 解密数据
		 * @param string $data 密文
		 * @throws exception 无法解密, 给定的密文编码错误/格式错误/无效
		 * @return string 明文
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
		 * 生成数据签名
		 * @param int $timestamp Unix 时间戳
		 * @param string $nonce nonce
		 * @param string $data 数据
		 * @param bool $binary 返回十六进制字符串或二进制字符串
		 * @return string 签名
		 */
		public function generate_signature($timestamp, $nonce, $data, $binary = false) {
			$data = array($this->salt, $timestamp, $nonce, $data);
			sort($data, SORT_STRING);
			return sha1(implode($data), $binary);
		}
	}
}