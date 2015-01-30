<?php
namespace wechat\mp {
  class exception extends \wechat\exception {
    const INVALID_CREDENTIAL = 40001;
    const INVALID_AUTHORZATION_TYPE = 40002;
    const MISSING_APPID = 41002;
    const MISSING_APPSECRET = 41004;
    const EMPTY_MEDIA = 41005;
    const INVALID_MEDIA_TYPE = 40004;
  }
}