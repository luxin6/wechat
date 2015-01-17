<?php
namespace wechat {
  /** Represents an exception from remote method */
  class exception extends \exception {
    const SERVER_BUSY = -1;
    const INVALID_ENCRYPTION = -40007;
    const MALFORMED_JSON = 47001;
    const MALFORMED_ACCESS_TOKEN = 40001;
  }
}