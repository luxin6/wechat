<?php
namespace wechat {
    /**
     * Represents an exception from remote method
     * @link http://qydev.weixin.qq.com/wiki/index.php?title=%E5%85%A8%E5%B1%80%E8%BF%94%E5%9B%9E%E7%A0%81%E8%AF%B4%E6%98%8E
     */
  class exception extends \exception {
    const SERVER_BUSY = -1;
    const INVALID_ENCRYPTION = -40007;
    const MALFORMED_JSON = 47001;
    const MALFORMED_ACCESS_TOKEN = 40001;
    const MISSING_CORPID = 41002;
    const MISSING_CORPSECRET = 41004;
    const INVALID_CORPID = 40013;
    const INVALID_CORPSECRET = 40001;
  }
}