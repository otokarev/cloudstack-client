<?php
namespace CloudStackSimple;

class Exception extends \Exception {
    const ENDPOINT_EMPTY = 1000;
    const ENDPOINT_NOT_URL = 1001;
    const APIKEY_EMPTY = 1002;
    const SECRETKEY_EMPTY = 1003;
    const STRTOSIGN_EMPTY = 1004;
    const NO_COMMAND = 1005;
    const WRONG_REQUEST_ARGS = 1006;
    const NOT_A_CLOUDSTACK_SERVER = 1007;
    const NO_VALID_JSON_RECEIVED = 1008;
    const MISSING_ARGUMENT = 1009;
    const NO_DATA_RECEIVED = 1010;
    static $map = [
        self::ENDPOINT_EMPTY => "No endpoint provided.",
        self::ENDPOINT_NOT_URL => "The endpoint must be a URL (starting by http://): \"%s\"",
        self::APIKEY_EMPTY => "No API key provided.",
        self::SECRETKEY_EMPTY => "No secret key provided.",
        self::STRTOSIGN_EMPTY => "String to sign empty.",
        self::NO_COMMAND => "No command given for the request.",
        self::WRONG_REQUEST_ARGS => "Arguments for the request must be in an array. Given: %s",
        self::NOT_A_CLOUDSTACK_SERVER => "The response is not a CloudStack server response. Check your endpoint. Received: %s",
        self::NO_VALID_JSON_RECEIVED => "The server did not issue a json response.",
        self::MISSING_ARGUMENT => "Argument missing: %s",
        self::NO_DATA_RECEIVED => "No data received",
    ];
    public function __construct($code, $value = null)
    {
        $msg = self::$map[$code];
        if ($value) {
            $msg = sprintf($msg, $value);
        }
        parent::__construct($msg, $code);
    }
}
