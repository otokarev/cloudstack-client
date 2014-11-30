CloudStack PHP Client
=====================

Simple PHP client library for the CloudStack based on code of https://github.com/qpleple/cloudstack-client-generator project

Examples
--------

    namespace CloudStackSimple;

    const API_ENDPOINT = "https://...";
    const API_KEY = "...";
    const SECRET_KEY="...";

    class CloudStackSimpleTestCase extends \PHPUnit_Framework_TestCase
    {
        public function test_simple ()
        {
            $cs = new Client(API_ENDPOINT, API_KEY, SECRET_KEY);
            $result = $cs->request('listVirtualMachines');
            var_dump($result);
        }
    }

