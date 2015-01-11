<?php
namespace wechat {
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