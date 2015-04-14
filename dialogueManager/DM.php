<?php
	session_start();
	//require_once('yelapi.php');
	if(!$_SESSION['state']){
		$state = $_GET['state'];
		$_SESSION['searchArr'] = array (
			"cuisine" => "",
			"location" => "",
			"deals" => "",
			"sorting" => "");
	}
	else{
		$state = $_SESSION['state'];
	}
	$intent = $_POST['intent'];
	$entity = $_POST['entity'];
	$entityvalue = $_POST['entityvalue'];
	$_SESSION['api_response'] = "";

	/*
	 * For querying yelp api
	 */
	// Enter the path that the oauth library is in relation to the php file
	require_once('lib/OAuth.php');

	// Set your OAuth credentials here  
	// These credentials can be obtained from the 'Manage API Access' page in the
	// developers documentation (http://www.yelp.com/developers)
	$CONSUMER_KEY = 'e9NrxKYFM-wVFsBx3uJi2g';
	$CONSUMER_SECRET = 'EDMTYousZAWnCZxSkTx0V1GvsgQ';
	$TOKEN = 'mBkE08GxFy0UHFGrKb87CmVTe2ylj7Ot';
	$TOKEN_SECRET = 'YZ7zbtveK0b1VbCnfGhYfrmbUJs';


	$API_HOST = 'api.yelp.com';
	$DEFAULT_TERM = 'dinner';
	$DEFAULT_LOCATION = 'San Francisco, CA';
	$SEARCH_LIMIT = 5;
	$SEARCH_PATH = '/v2/search/';
	$BUSINESS_PATH = '/v2/business/';


	/** 
	 * Makes a request to the Yelp API and returns the response
	 * 
	 * @param    $host    The domain host of the API 
	 * @param    $path    The path of the APi after the domain
	 * @return   The JSON response from the request      
	 */
	function request($host, $path) {
	    $unsigned_url = "http://" . $host . $path;

	    // Token object built using the OAuth library
	    $token = new OAuthToken($GLOBALS['TOKEN'], $GLOBALS['TOKEN_SECRET']);

	    // Consumer object built using the OAuth library
	    $consumer = new OAuthConsumer($GLOBALS['CONSUMER_KEY'], $GLOBALS['CONSUMER_SECRET']);

	    // Yelp uses HMAC SHA1 encoding
	    $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

	    $oauthrequest = OAuthRequest::from_consumer_and_token(
	        $consumer, 
	        $token, 
	        'GET', 
	        $unsigned_url
	    );
	    
	    // Sign the request
	    $oauthrequest->sign_request($signature_method, $consumer, $token);
	    
	    // Get the signed URL
	    $signed_url = $oauthrequest->to_url();
	    
	    // Send Yelp API Call
	    $ch = curl_init($signed_url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    $data = curl_exec($ch);
	    curl_close($ch);
	    
	    return $data;
	}

	/**
	 * Query the Search API by a search term and location 
	 * 
	 * @param    $term        The search term passed to the API 
	 * @param    $location    The search location passed to the API 
	 * @return   The JSON response from the request 
	 */
	function search($term, $location) {
	    $url_params = array();
	    
	    $url_params['term'] = $term ?: $GLOBALS['DEFAULT_TERM'];
	    $url_params['location'] = $location?: $GLOBALS['DEFAULT_LOCATION'];
	    $url_params['limit'] = $GLOBALS['SEARCH_LIMIT'];
	    $search_path = $GLOBALS['SEARCH_PATH'] . "?" . http_build_query($url_params);
	    
	    return request($GLOBALS['API_HOST'], $search_path);
	}

	/**
	 * Query the Business API by business_id
	 * 
	 * @param    $business_id    The ID of the business to query
	 * @return   The JSON response from the request 
	 */
	function get_business($business_id) {
	    $business_path = $GLOBALS['BUSINESS_PATH'] . $business_id;
	    
	    return request($GLOBALS['API_HOST'], $business_path);
	}

	/**
	 * Queries the API by the input values from the user 
	 * 
	 * @param    $term        The search term to query
	 * @param    $location    The location of the business to query
	 */
	function query_api($term, $location) {     
	    $response = json_decode(search($term, $location));
	    $business_id = $response->businesses[0]->id;
	    
	    print sprintf(
	        "%d businesses found, querying business info for the top result \"%s\"\n\n",         
	        count($response->businesses),
	        $business_id
	    );
	    
	    $response = get_business($business_id);
	    
	    print sprintf("Result for business \"%s\" found:\n", $business_id);
	    $_SESSION['api_response'] =  $_SESSION['api_response'] . "Result for business " . $business_id . "found:<br>";

	    print "$response\n";

	    $_SESSION['api_response'] =  $_SESSION['api_response'] . $response . "<br> <br>";
	}


	/*
	 *state control
	 */
	$newstate = $_SESSION['state'];
	if($state == 'initial'){
		if($intent == 'restaurantSearch'){
			$array =  new ArrayObject($_SESSION['searchArr']);
			if($entity == 'cuisine'){
				foreach($array as $key => &$value){
					if($key == "cuisine"){
						$value = $entityvalue;
					}
				}
				$_SESSION['searchArr'] = new ArrayObject($array);  
				//$_SESSION['searchArr']["cuisine"] = $entityvalue;
				$_SESSION['message'] = "Do you have any preferences for location?";
				$newstate = 'filtering';
			}
			else if($entity == "location"){
				foreach($array as $key => &$value){
					if($key == "location"){
						$value = $entityvalue;
					}
				}
				$_SESSION['searchArr'] = new ArrayObject($array); 
				//$_SESSION['searchArr']["location"] = $entityvalue;
				$_SESSION['message'] = "Do you have any preferences for deals?";
				$newstate = 'filtering';
			}
			else if($entity == "deals"){
				foreach($array as $key => &$value){
					if($key == "deals"){
						$value = $entityvalue;
					}
				}
				$_SESSION['searchArr'] = new ArrayObject($array); 
				//$_SESSION['searchArr']["deals"] = $entityvalue;
				$_SESSION['message'] = "How do you want to sort the results? ";
			}
			else if($entity == "sorting"){
				foreach($array as $key => &$value){
					if($key == "sorting"){
						$value = $entityvalue;
					}
				}
				$_SESSION['searchArr'] = new ArrayObject($array); 
				//$_SESSION['searchArr']["sorting"] = $entityvalue;
				$_SESSION['message'] = "Do you have any preferences for cuisine?";
				$newstate = 'filtering';
			}
			else{
				$_SESSION['message'] = "Hi, how can I help you?";
				$newstate = 'filtering';
			}
		}
		else if($intent == 'return'){
			$newstate = 'initial';
		}
		else if($intent == 'finish'){
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
		else{
			$_SESSION['message'] = "Hi, how can I help you?";
			$newstate = 'initial';

		}
	}
	else if($state =='filtering'){
		if($intent == 'restaurantSearch'){
			$array =  new ArrayObject($_SESSION['searchArr']);
			if($entity == "cuisine"){
				foreach($array as $key => &$value){
					if($key == "cuisine"){
						$value = $entityvalue;
					}
				}
				$_SESSION['searchArr'] = new ArrayObject($array);  
				//$_SESSION['searchArr']["cuisine"] = $entityvalue;
				$newstate = 'filtering';
				$array =  new ArrayObject($_SESSION['searchArr']);
			
				foreach($array as $key => &$value){
					if($key == "cuisine" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for cuisine1?";
						break;
					}
					else if($key == "location" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for location1?";
						break;
					}
					else if($key == "deals" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for deals1?";	
						break;
					}
					else if($key == "sorting" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for sorting1?";	
						break;
					}
					else{
						$_SESSION['message'] = "Querying Yelp API for results!";
						$newstate = 'select';
						break;
					}
				}
				// if($array["cuisine"] === ""){
				// 	$_SESSION['message'] = "Do you have any preferences for cuisine1?";
				// }
				// else if($array["location"] === ""){
				// 	$_SESSION['message'] = "Do you have any preferences for location1?";
				// }
				// else if($array["deals"] === ""){
				// 	$_SESSION['message'] = "Do you have any preferences for deals1?";					
				// }
				// else if($array["sorting"] == ""){
				// 	$_SESSION['message'] = "Do you have any preferences for cuisine1?";					
				// }
				// else{
				// 	$_SESSION['message'] = "Querying Yelp API for results!";
				// 	$newstate = 'select';
				// }
			}
			else if($entity == "location"){

				foreach($array as $key => &$value){
					if($key == "location"){
						$value = $entityvalue;
					}
				}
				$_SESSION['searchArr'] = new ArrayObject($array);  

				//$_SESSION['searchArr']["location"] = $entityvalue;
				$newstate = 'filtering';

				$array = $_SESSION['searchArr'];
				foreach($array as $key => &$value){
					if($key == "cuisine" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for cuisine2?";
						break;
					}
					else if($key == "location" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for location2?";
						break;
					}
					else if($key == "deals" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for deals2?";	
						break;
					}
					else if($key == "sorting" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for sorting2?";	
						break;
					}
					else{
						$_SESSION['message'] = "Querying Yelp API for results! Please select a restaurant from the list!";

						$term_user = "";
						$location_user = "";
						foreach($array as $key => &$value){
							if($key == "cuisine"){
								$term_user = $value;
							}
							else if($key == "location" ){
								$location_user = $value;
							}
						}

						query_api($term_user, $location_user);

						$newstate = 'select';
						break;
					}
				}
			}
			else if($entity == "deals"){

				foreach($array as $key => &$value){
					if($key == "deals"){
						$value = $entityvalue;
					}
				}
				$_SESSION['searchArr'] = new ArrayObject($array); 

				//$_SESSION['searchArr']["deals"] = $entityvalue;
				$newstate = 'filtering';
				$array = $_SESSION['searchArr'];
				foreach($array as $key => &$value){
					if($key == "cuisine" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for cuisine3?";
						break;
					}
					else if($key == "location" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for location3?";
						break;
					}
					else if($key == "deals" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for deals3?";	
						break;
					}
					else if($key == "sorting" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for sorting3?";	
						break;
					}
					else{
						$_SESSION['message'] = "Querying Yelp API for results! ";
						$newstate = 'select';
						break;
					}
				}
			}
			else if($entity == "sorting"){

				foreach($array as $key => &$value){
					if($key == "sorting"){
						$value = $entityvalue;
					}
				}
				$_SESSION['searchArr'] = new ArrayObject($array); 

				//$_SESSION['searchArr']["sorting"] = $entityvalue;
				$newstate = 'filtering';
				$array = $_SESSION['searchArr'];
				foreach($array as $key => &$value){
					if($key == "cuisine" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for cuisine4?";
						break;
					}
					else if($key == "location" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for location4?";
						break;
					}
					else if($key == "deals" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for deals4?";	
						break;
					}
					else if($key == "sorting" && $value == ""){
						$_SESSION['message'] = "Do you have any preferences for sorting4?";	
						break;
					}
					else{
						$_SESSION['message'] = "Querying Yelp API for results!";
						$newstate = 'select';
						break;
					}
				}
			}
			else{
				$_SESSION['message'] = "This filter is not supported.";
				$newstate = 'Filtering';
			}
		}
		else{
			$_SESSION['message'] = "Hi, how can I help you?";
			$newstate = 'initial';

		}
	}
	else if($state === 'select'){
		if($intent === 'restaurantSelect'){
			if($entity === 'ordinal'){
				$_SESSION['message'] = "You selected" . $entityvalue . "restaurant on the list. What information do you want?";
				$newstate = 'getinfo';
			}
			else{
				$_SESSION['message'] = "Cannot recognize you selection. Please select a restaurant from the list!";
				$newstate = 'select';
			}
		}
		else if($intent === 'return'){
			$newstate = 'initial';
		}
		else if($intent === 'finish'){
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
		else{
			$_SESSION['message'] = "Please select a restaurant from the list!";
			$newstate = 'select';
		}
	}
	else if($state === 'getinfo'){
		if($intent === 'restaurantInfo'){
			if($entity === 'phoneRequest'){
				$_SESSION['message'] = "You requested phone number. The phone number is ###-###-####.";
				$newstate = 'options';
			}
			else if($entity === 'ratingRequest'){
				$_SESSION['message'] = "You requested rating. The phone number is #.";
				$newstate = 'options';
			}
			else{
				$_SESSION['message'] = "Cannot recognize you request. What do you want to know about this restaurant?";
				$newstate = 'getinfo';
			}
		}
		else if($intent === 'return'){
			$newstate = 'initial';
		}
		else if($intent === 'finish'){
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
		else{
			$_SESSION['message'] = "What do you want to know about this restaurant?";
			$newstate = 'getinfo';
		}

	}
	else if($state === 'options'){
		if($intent === 'return'){
			$newstate = 'initial';
		}
		else if($intent === 'finish'){
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
		else{
			$_SESSION['message'] = "What do you want to know about this restaurant?";
		}
	}
	else if($state === 'finish'){
		if($intent === 'return'){
			$newstate = 'initial';
		}
		else {
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}

	}

	echo "Search Slots info: <br>";
 	echo "######1: " . $_SESSION['searchArr']["cuisine"] . "<br>";
    echo "######2: " . $_SESSION['searchArr']["location"]  . "<br>";
    echo "######3: " . $_SESSION['searchArr']["deals"] . "<br>";
    echo "######4: " . $_SESSION['searchArr']["sorting"]  . "<br>";
	echo $_SESSION['message'];
	$_SESSION['state'] = $newstate;
	echo "<br>";
	echo $newstate;
	echo "<script>setTimeout(\"location.href = 'DMtest.php';\",1500);</script>";


?>