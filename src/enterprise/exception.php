<?php
namespace wechat\enterprise {
	/** Represents an exception from wechat enterprise api */
  class exception extends \wechat\exception {
    const MISSING_CORPID = 41002;
    const MISSING_CORPSECRET = 41004;
    const INVALID_CORPID = 40013;
    const INVALID_CORPSECRET = 40001;
  }
}