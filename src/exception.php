<?php
namespace wechat {
	class exception extends \exception {
		const MALFORMED_ASEKEY = -2;
		const MALFORMED_ENCRYPTION = -3;
		const SERVER_BUSY = -1;
		const MALFORMED_JSON_STRING = -4;
		const MISSING_CORPID = 41002;
		const MISSING_CORPSECRET = 41004;
		const INVALID_CORPID = 40013;
		const INVALID_CORPSECRET = 40001;
		const MALFORMED_ACCESS_TOKEN = 40001;
		const MALFORMED_XML = -5;
	}
}