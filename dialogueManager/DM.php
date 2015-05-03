<?php
	session_start();
	
	$debug = false;

	if(!$_SESSION['state']){
		$state = "initial";
		$_SESSION['searchArr'] = array (
			'search_query' => "",
			'location' => "",
			// "deals" => "",
			// "sorting" => ""
			);
	}
	else{
		$state = $_SESSION['state'];
	}

	

	$intent = $_POST['intent'];
	// $entity = $_POST['entity'];
	// $entityvalue = $_POST['entityvalue'];
	$_SESSION['api_response'] = "";

	$entityArray = array();
	// $entityArray[$_POST['entity']] = $_POST['entityvalue'];
	// $entityArray[$_POST['entity2']] = $_POST['entityvalue2'];

	if(isset($_POST['nlu'])) {
		$json = $_POST['nlu'];
		var_dump(json_decode($json, true));
		echo "<br>";
		$jfo = Json_decode($json);
		$outcome = $jfo->outcome;
		$user_utterance = $jfo->msg_body;
		$intent = $outcome->intent;
		if($debug){
			echo "<br>";
			echo "intent: " . "<br>";
			echo $intent;
			echo "<br>";
		}
		$entities = $outcome->entities;
		foreach ($entities as $entityName => $entityArr){
			if($debug){
				echo "entity: " . "<br>";
				echo $entityName;
				echo "<br>";
			}
			$entity = $entityName;
			$entityvalue = $entityArr->value;
			$entityArray[$entity] = $entityvalue;
			if($debug){
				echo "entityvalue: " . "<br>";
				echo $entityvalue;
				echo "<br>";
			}
		}	
		//echo "Yes!";
	} 
	else {
		echo "Didn't get anything from Wit.AI!!";
	}

	echo "current state: " . $state . "<br>";
	echo "User utterance: " . $user_utterance . "<br>";

	if($debug){
		print_r($entityArray);
	}

	/*
	 * For querying yelp api
	 */
	// Enter the path that the oauth library is in relation to the php file
	require_once('lib/OAuth.php');

	// Set your OAuth credentials here  
	// These credentials can be obtained from the 'Manage API Access' page in the
	// developers documentation (http://www.yelp.com/developers)
	$CONSUMER_KEY = 'e9NrxKYFM-wVFsBx3uJi2g';
	$CONSUMER_SECRET = 'HIDDEN';
	$TOKEN = 'mBkE08GxFy0UHFGrKb87CmVTe2ylj7Ot';
	$TOKEN_SECRET = 'HIDDEN';


	$API_HOST = 'api.yelp.com';
	$DEFAULT_TERM = 'restaurant';
	$DEFAULT_LOCATION = 'Columbia University';
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
	    //$url_params['cll'] = "40.7988405, -73.96091559999999";
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
	    $_SESSION['restaurant_list'] = new ArrayObject($response->businesses);
	    display_list($_SESSION['restaurant_list']);
	}

	function display_list($arr){

		foreach($arr as $item){
			// print_r($item);
			// echo "<br>";
			echo "<a target=\"_blank\" href=\"" . $item->url  ."\" ><h3>" . $item->name  ."</h3> </a>";
			echo "<p>" . $item->phone  ."</p>";
			echo "<img src =\"" . $item->rating_img_url . "\" > <br>";
			echo "<img src =\"" . $item->image_url . "\" >";
			echo "<br> <hr>";
		}
	}

	function display_restaurant($item){

		echo "<a target=\"_blank\" href=\"" . $item->url  ."\" ><h3>" . $item->name  ."</h3> </a>";
		echo "<p>" . $item->phone  ."</p>";
		echo "<img src =\"" . $item->rating_img_url . "\" > <br>";
		echo "<img src =\"" . $item->image_url . "\" >";
		echo "<br>";
	}
	function reset_param(){
		//empty the slots
		$_SESSION['searchArr'] = array (
			'search_query' => "",
			'location' => "",
			// "deals" => "",
			// "sorting" => ""
		);
		$_SESSION['restaurant_list'] = array();
		$_SESSION['selected_restaurant']  = array();
	}


	/*
	 *state control
	 */
	$newstate = $_SESSION['state'];

	/*
	 * current state: initial
	 */

	if($state == 'initial'){

		reset_param();

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
		else if($intent == 'finished'){
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

					if($debug){
						echo "+++++++++++++++ display_restaurant +++++++++++++ <br>";
						print_r($_SESSION['restaurant_list'][$entity_value-1]);
					}

					display_restaurant($_SESSION['restaurant_list'][$entity_value-1]);
					$_SESSION['selected_restaurant'] = $_SESSION['restaurant_list'][$entity_value-1];

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
			display_list($_SESSION['restaurant_list']);
			$newstate = 'select';
			$_SESSION['message'] = "Pick a restaurant from the list!";
			$_SESSION['selected_restaurant']  = array();
		}
		else if($intent === 'finished'){
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
		else{
			$_SESSION['message'] = "Please select a restaurant from the list!";
			$newstate = 'select';
		}
	}

	/*
	 * current state : gerInfo
	 */

	else if($state === 'getinfo'){
		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
		}
		if($intent === 'restaurantInfo'){

			$info_flag = false;
			foreach($entityArray as $entity_key => $entity_value){
				if($debug){
					echo "==============selected restaurant info =================";
					print_r($_SESSION['selected_restaurant']);

				}
				if($entity_key == 'phoneRequest'){
					$phone_number = $_SESSION['selected_restaurant']->phone;
					$_SESSION['message'] = "You requested phone number. The phone number is: " . $phone_number;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'ratingRequest'){
					$rating = $_SESSION['selected_restaurant']->rating;
					$_SESSION['message'] = "You requested rating. The rating is: " . $rating . " stars";
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'addressRequest'){
					$_SESSION['message'] = "You requested address. ";
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'reviewRequest'){
					$review = $_SESSION['selected_restaurant']->snippet_text;
					$_SESSION['message'] = "You requested review: " . $review;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'isOpenRequest'){
					$_SESSION['message'] = "You asked whether it is open. ";
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}

			}
			if(!$info_flag){
				$_SESSION['message'] = "Cannot recognize you request. What do you want to know about this restaurant?";
				$newstate = 'getinfo';
			}
		}
		else if($intent === 'return'){
			$newstate = 'getinfo';
			display_restaurant($_SESSION['selected_restaurant']);
			$_SESSION['message'] = "What do you want to know about this restaurant?";
		}
		else if($intent === 'startover'){
			$newstate = 'initial';
		}
		else if($intent === 'finished'){
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
		if($intent == 'return'){
			$newstate = 'initial';
		}
		else if($intent == 'finished'){
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}
		else{
			$_SESSION['message'] = "What do you want to know about this restaurant?";
		}
	}
	else if($state == 'finished'){

		reset_param();

		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
		}
		if($intent ==='return'){
			$newstate = 'initial';
		}
		else {
			$_SESSION['message'] = "Good Bye!";
			$newstate = 'finish';
		}

	}

	if($debug){
		echo "+++++++++++Search Slots info: <br>++++++++++++";
	 	echo "######1: " . $_SESSION['searchArr']["search_query"] . "<br>";
	    echo "######2: " . $_SESSION['searchArr']["location"]  . "<br>";
	    echo "######3: " . $_SESSION['searchArr']["deals"] . "<br>";
	    echo "######4: " . $_SESSION['searchArr']["sorting"]  . "<br>";
	}
	echo $_SESSION['message'];
	$_SESSION['state'] = $newstate;
	echo "<br>";
	echo "new state: " . $newstate . "<br>";
	if($debug){
		echo $_SESSION['api_response'];
		echo "<br>";
	}
	//echo  "<script> speechSynthesis.speak(SpeechSynthesisUtterance('Hello World'));</script>";
	echo  "<button class=\"btn btn-lg btn-primary btn-block\" id=\"speak\" onclick=\"speechSynthesis.speak(new SpeechSynthesisUtterance('" . $_SESSION['message'] . "'));\" >Speak</button>";
	//echo "<script>setTimeout(\"location.href = 'DMtest.php?words=" . $_SESSION['message'] . "';\",1500);</script>";


?>