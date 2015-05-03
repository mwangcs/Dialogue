
<?php
	session_start();
	
	$debug = true;
	$restaurant_list = array();
	if(!$_SESSION['state']){
		$state = "initial";
		$_SESSION['searchArr'] = array (
			'search_query' => "",
			'location' => "",
			);
	}
	else{
		$state = $_SESSION['state'];
	}
	echo "current state: " . $state . "<br>";
	$intent = $_POST['intent'];
	$_SESSION['api_response'] = "";
	$entityArray = array();
	$entityArray[$_POST['entity']] = $_POST['entityvalue'];
	$entityArray[$_POST['entity2']] = $_POST['entityvalue2'];
	print_r($entityArray);
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
	 * @param    $path    The path of the API after the domain
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
	    //$business_id = $response->businesses[0]->id;
	    $restaurant_list = new ArrayObject($response->businesses);
	    display_list($response->businesses);
	}
	function display_list($arr){
		$restaurant_list = $arr;
		print_r($restaurant_list);
		echo "!!!!!!!!!!!!!!!!!!!!!!! <br>";
		foreach($arr as $item){
			// print_r($item);
			// echo "<br>";
			echo "<h1>" . $item->name  ."</h1>";
			echo "<p>" . $item->phone  ."</p>";
			echo "<img src =\"" . $item->image_url . "\" >";
		}
	}
	/*
	 *state control
	 */
	$newstate = $_SESSION['state'];
	/*
	 * current state: initial
	 */
	if($state == 'initial'){
		//empty the slots
		$_SESSION['searchArr'] = array (
			'search_query' => "",
			'location' => "",
		);
		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
			print_r($_SESSION['searchArr']);
			echo "<br>";
		}
		if($intent == 'restaurantSearch'){
			foreach($entityArray as $entity_key => $entity_value){
				foreach($_SESSION['searchArr'] as $key => &$value){
					if($key == $entity_key){
						$value = $entity_value;
					}
					else if($entity_key == "user_location" && $key == "location"){
						$value = $entity_value;
					}
				}
			}
			$array =  new ArrayObject($_SESSION['searchArr']);
			$system_message = "Do you have other preferences?";
			$allslotsfilled = true;
			foreach($array as $key1 => $value1){
				if($value1 === ""){
				 	$system_message = "Do you have any preferences for " . $key1 . "?";
					$allslotsfilled = false;
				}
			}
			if($allslotsfilled){
				$newstate = 'select';
				query_api($array["search_query"], $array["location"]);
				$_SESSION['message'] = "Querying Yelp API. Pick a restaurant from the list!";
				echo "size: " . sizeof($restaurant_list);
			}
			else{
				$newstate = 'filtering';
				$_SESSION['message'] = $system_message;
			}
			if($debug){
				echo "+++++++++++++++ 2 +++++++++++++ <br>";
				print_r($_SESSION['searchArr']);
				echo "<br>";
			}
		}
		else if($intent == 'return'){
			$_SESSION['message'] = "This is the beginning. How can I help you!";
			$newstate = 'initial';
		}
		else if($intent == 'finish'){
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
		else if($intent == 'greet'){
			$_SESSION['message'] = "Hi, how can I help you?";
			$newstate = 'initial';
		}
		else{
			$_SESSION['message'] = "I don't understand your request. How can I help you?";
			$newstate = 'initial';
		}
	}
	/*
	 * current state: filtering
	 */
	else if($state =='filtering'){
		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
			print_r($_SESSION['searchArr']);
			echo "<br>";
		}
		if($intent == 'restaurantSearch'){
			foreach($entityArray as $entity_key => $entity_value){
				foreach($_SESSION['searchArr'] as $key => &$value){
					if($key == $entity_key){
						$value = $entity_value;
					}
					else if($entity_key == "user_location" && $key == "location"){
						$value = $entity_value;
					}
				}
			}
			$array =  new ArrayObject($_SESSION['searchArr']);
			$system_message = "Do you have other preferences?";
			$allslotsfilled = true;
			foreach($array as $key1 => $value1){
				if($value1 === ""){
				 	$system_message = "Do you have any preferences for " . $key1 . "?";
					$allslotsfilled = false;
				}
			}
			if($allslotsfilled){
				$newstate = 'select';
				query_api($array["search_query"], $array["location"]);
				$_SESSION['message'] = "Querying Yelp API. Pick a restaurant from the list!";
				echo "size: " . sizeof($restaurant_list);
			}
			else{
				$newstate = 'filtering';
				$_SESSION['message'] = $system_message;
			}
			if($debug){
				echo "+++++++++++++++ 2 +++++++++++++ <br>";
				print_r($_SESSION['searchArr']);
				echo "<br>";
			}
		}
		else if($intent == 'return'){
			$_SESSION['message'] = "This is the beginning. How can I help you!";
			$newstate = 'initial';
		}
		else if($intent == 'finish'){
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
		else if($intent == 'greet'){
			$_SESSION['message'] = "Hi, how can I help you?";
			$newstate = 'initial';
		}
		else{
			$_SESSION['message'] = "I don't understand your request. How can I help you?";
			$newstate = 'initial';
		}
	}
	/*
	 * current state : select
	 */
	else if($state === 'select'){
		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
		}
		if($intent === 'restaurantSelect'){
			$select_flag = false;
			foreach($entityArray as $entity_key => $entity_value){
				if($entity_key === 'ordinal'){
					$_SESSION['message'] = "You selected" . $entity_value . "restaurant on the list. What information do you want?";
					$newstate = 'getinfo';
					$select_flag = true;
				}
			}
			if(!$select_flag){
				$_SESSION['message'] = "Cannot recognize your selection. Please select a restaurant from the list!";
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
		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
		}
		if($intent === 'restaurantInfo'){
			if($entity === 'phoneRequest'){
				$_SESSION['message'] = "You requested phone number. The phone number is ###-###-####.";
				$newstate = 'options';
			}
			else if($entity === 'ratingRequest'){
				$_SESSION['message'] = "You requested rating. The rating is #.";
				$newstate = 'options';
			}
			else if($entity === 'addressRequest'){
				$_SESSION['message'] = "You requested address. ";
				$newstate = 'options';
			}
			else if($entity === 'reviewRequest'){
				$_SESSION['message'] = "You requested review. ";
				$newstate = 'options';
			}
			else if($entity === 'isOpenRequest'){
				$_SESSION['message'] = "You asked whether it is open. ";
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
		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
		}
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
		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
		}
		if($intent === 'return'){
			$newstate = 'initial';
		}
		else {
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
	}
	echo "+++++++++++Search Slots info: <br>++++++++++++";
 	echo "######1: " . $_SESSION['searchArr']["search_query"] . "<br>";
    echo "######2: " . $_SESSION['searchArr']["location"]  . "<br>";
    echo "######3: " . $_SESSION['searchArr']["deals"] . "<br>";
    echo "######4: " . $_SESSION['searchArr']["sorting"]  . "<br>";
	echo $_SESSION['message'];
	$_SESSION['state'] = $newstate;
	echo "<br>";
	echo $newstate;
	echo $_SESSION['api_response'];
	echo "<br>";
	echo  "<button class=\"btn btn-lg btn-primary btn-block\" id=\"speak\" onclick=\"speechSynthesis.speak(new SpeechSynthesisUtterance('" . $_SESSION['message'] . "'));\" >Speak</button>";
	//echo "<script>setTimeout(\"location.href = 'DMtest.php?words=" . $_SESSION['message'] . "';\",1500);</script>";
?>
