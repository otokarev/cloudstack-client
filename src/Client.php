<?php

namespace CloudStackSimple;

class Client {
    public $apiKey;
    public $secretKey;
    public $endpoint; // Does not ends with a "/"
    private $assoc = false;
	
	public function __construct($endpoint, $apiKey, $secretKey) {
	    // API endpoint
	    if (empty($endpoint)) {
	        throw new Exception("No endpoint provided.");
	    }
	    
	    if (!preg_match("|^https*://.*$|", $endpoint)) {
	        throw new Exception(
                "The endpoint must be a URL (starting by http://): "
                . json_encode($endpoint)
            );
	    }
	    
	    // $endpoint does not ends with a "/"
	    $this->endpoint = substr($endpoint, -1) == "/" ? substr($endpoint, 0, -1) : $endpoint;
	    
	    // API key
	    if (empty($apiKey)) {
	        throw new Exception("No API key provided.");
	    }
		$this->apiKey = $apiKey;
		
		// API secret
		if (empty($secretKey)) {
		    throw new Exception("No secret key provided.");
		}
		$this->secretKey = $secretKey;
	}

    public function setAssoc($v) {
        $this->assoc = $v;
    }
	
    public function getAssoc() {
        return $this->assoc;
    }
	
    public function getSignature($queryString) {
        if (empty($queryString)) {
            throw new Exception("String to sign empty.");
        }
        
        $hash = @hash_hmac("SHA1", $queryString, $this->secretKey, true);
        $base64encoded = base64_encode($hash);
        return urlencode($base64encoded);
    }

    public function request($command, $args = array()) {
        if (empty($command)) {
            throw new Exception("No command given for the request.");
        }
        
        if (!is_array($args)) {
            throw new Exception(
                "Arguments for the request must be in an array. Given: "
                . json_encode($args)
            );
        }
        
        foreach ($args as $key => $value) {
            if ($value == "") {
                unset($args[$key]);
            }
        }
        
        // Building the query
        $args['apikey'] = $this->apiKey;
        $args['command'] = $command;
        $args['response'] = "json";
        ksort($args);
        $query = http_build_query($args);
        $query = str_replace("+", "%20", $query);
        $query .= "&signature=" . $this->getSignature(strtolower($query));
        $url = $this->endpoint . "?" . $query;

        $curl = \curl_init();
        \curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
        ));
        $data = \curl_exec($curl);
        \curl_close($curl);
        
        if (empty($data)) {
            throw new Exception("No data received");
        }
        $result = @json_decode($data);
        if (empty($result)) {
            throw new Exception("The server did not issue a json response.");
        }
        
        $propertyResponse = strtolower($command) . "response";
        
        if (!property_exists($result, $propertyResponse)) {
            if (property_exists($result, "errorresponse") && property_exists($result->errorresponse, "errortext")) {
                throw new Exception($result->errorresponse->errortext);
            } else {
                throw new Exception(sprintf("Unable to parse the response. Got code %d and message: %s", $code, $data['body']));
            }
        }
        
        $response = $result->{$propertyResponse};
        
        // list handling : most of lists are on the same pattern as listVirtualMachines :
        // { "listvirtualmachinesresponse" : { "virtualmachine" : [ ... ] } }
        preg_match('/list(\w+)s/', strtolower($command), $listMatches);
        if (!empty($listMatches)) {
            $objectName = $listMatches[1];
            if (property_exists($response, $objectName)) {
                $resultArray = $response->{$objectName};
                if (is_array($resultArray)) {
                    return $resultArray;
                }
            } else {
                // sometimes, the 's' is kept, as in :
                // { "listasyncjobsresponse" : { "asyncjobs" : [ ... ] } }
                $objectName = $listMatches[1] . "s";
                if (property_exists($response, $objectName)) {
                    $resultArray = $response->{$objectName};
                    if (is_array($resultArray)) {
                        return $resultArray;
                    }
                }
            }
        }
        if ($this->assoc) {
            $response = json_decode(json_encode($response), true);
        }
        return $response;
    }
}
