<?php
	session_start();
	ini_set('display_errors',"1");
	set_time_limit(0);
	$debug = false;
	$debug_nlg = true;

	$using_nlg = true;

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
		if($debug){
			var_dump(json_decode($json, true));
			echo "<br>";
		}
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

	if($debug){
		echo "current state: " . $state . "<br>";
	}
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
	$CONSUMER_SECRET = 'EDMTYousZAWnCZxSkTx0V1GvsgQ';
	$TOKEN = 'mBkE08GxFy0UHFGrKb87CmVTe2ylj7Ot';
	$TOKEN_SECRET = 'YZ7zbtveK0b1VbCnfGhYfrmbUJs';

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

		echo "<table class=\"restauranttbl\">";
		foreach($arr as $item){
			// print_r($item);
			// echo "<br>";
			echo " <tr class=\"restaurant\"> ";
			echo " <td class=\"restaurantimg\"><img src=\"" . $item->image_url . "\" alt=\"Restaurant Image\"></td>";
			echo " <td class=\"restauranttxt\">";
			echo "	<p style=\"font-size:90%;font-family: Verdana\";>";
			echo "	<a href=\"" . $item->url . "\"><b>" . $item->name . "</b></a><br>";
			echo  $item->location->display_address[0] . "<br> " . $item->location->display_address[1] . "," . $item->location->display_address[2] . "<br><br> ";
			echo "	Rating: <img src=\"" . $item->rating_img_url . "\" alt=\"" . $item->rating . " stars\"> ";
			echo "	</p></td> </tr> ";
		}
		echo "</table>";
	}

	function display_restaurant($item){

		echo "<table class=\"restauranttbl\">";
		echo " <tr class=\"restaurant\"> ";
		echo " <td class=\"restaurantimg\"><img src=\"" . $item->image_url . "\" alt=\"Restaurant Image\"></td>";
		echo "<td class=\"restauranttxt\">";
		echo "	<p style=\"font-size:90%;font-family: Verdana\";>";
		echo "	<a href=\"" . $item->url . "\"><b>" . $item->name . "</b></a><br>";
		echo  $item->location->display_address[0] . "<br> " . $item->location->display_address[1] . "," . $item->location->display_address[2] . "<br><br> ";
		echo "	Rating: <img src=\"" . $item->rating_img_url . "\" alt=\"" . $item->rating . " stars\"> ";
		echo "	</p></td> </tr> ";
		echo "</table>";
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

	function get_nlg_response($url){
		$url = str_replace(" ","%20", $url);
		$ch = curl_init($url);
		//curl_setopt($ch, CURLOPT_URL, "http://0.0.0.0:5000/analyse_utterance?utterance=eat&type=location");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 200);
		$message = curl_exec($ch);
		if(!$message){
			return false;
		}
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if(!($httpcode=="200")){
			return false;
		}
		if($message == "500 error" || $message == "404 error"){
			return false;
		}
		curl_close($ch);
		return $message;
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
						$value = "Columbia University";
					}
				}
			}

			$array =  new ArrayObject($_SESSION['searchArr']);

			$system_message = "Do you have other preferences?";
			$allslotsfilled = true;
			foreach($array as $key1 => $value1){
				if($value1 === ""){
					if($using_nlg){
						$system_message = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/analyse_utterance?utterance=" . $user_utterance . "&type=" . $key1);
					}
					if(!$using_nlg || !$system_message){
						if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				 		$system_message = "Do you have any preferences for " . $key1 . "?";
				 	}
					$allslotsfilled = false;
				}
			}
			if($allslotsfilled){
				$newstate = 'select';
				query_api($array["search_query"], $array["location"]);
				if($using_nlg){
					$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/displayRestaurant?utterance=" . $user_utterance);
				}
				
				if(!$using_nlg || !$message_temp){
					if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
					$message_temp = "Here is a list of restaurants. Pick a restaurant from the list!";
				}
				$_SESSION['message'] = $message_temp;
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
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/goodbye");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "Good Bye!";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'finish';
		}
		else if($intent == 'greet'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent === 'returnStart'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent === 'UNKNOWN'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/clarification");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "I don't understand your request. Can you repeat that?";
			}
			$_SESSION['message'] = $message_temp;
		}
		else{
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/clarification");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "I don't understand your request. Can you repeat that?";
			}
			$_SESSION['message'] = $message_temp;
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
						$value = "Columbia University";
					}
				}
			}

			$array =  new ArrayObject($_SESSION['searchArr']);

			$system_message = "Do you have other preferences?";
			$allslotsfilled = true;
			foreach($array as $key1 => $value1){
				if($value1 === ""){
				 	if($using_nlg){
						$system_message = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/analyse_utterance?utterance=" . $user_utterance . "&type=" . $key1);
					}
					if(!$using_nlg || !$system_message){
						if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				 		$system_message = "Do you have any preferences for " . $key1 . "?";
				 	}
					$allslotsfilled = false;
				}
			}
			if($allslotsfilled){
				$newstate = 'select';
				query_api($array["search_query"], $array["location"]);
				if($using_nlg){
					$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/displayRestaurant?utterance=" . $user_utterance);
				}
				if(!$using_nlg || !$message_temp){
					if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
					$message_temp = "Here is a list of restaurants. Pick a restaurant from the list!";
				}
				$_SESSION['message'] = $message_temp;
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
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent === 'returnStart'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent == 'finished'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/goodbye");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "Good Bye!";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'finish';
		}
		else if($intent == 'greet'){

			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent === 'UNKNOWN'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/clarification");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "I don't understand your request. Can you repeat that?";
			}
			$_SESSION['message'] = $message_temp;
		}
		else{
			if($using_nlg){
				$message_temp  = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/clarification");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp  = "I don't understand your request. Can you repeat that?";
			}
			$_SESSION['message'] = $message_temp;

			$newstate = 'filtering';
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
					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/selectRestaurant?restaurant=" . $_SESSION['restaurant_list'][$entity_value-1]->name);
					}
					if(!$using_nlg || !$message_temp){
						if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
						$message_temp = "You selected " . $_SESSION['restaurant_list'][$entity_value-1]->name . " on the list. What information do you want?";
					}
					$_SESSION['message'] = $message_temp;

					if($debug){
						echo "+++++++++++++++ display_restaurant +++++++++++++ <br>";
						print_r($_SESSION['restaurant_list'][$entity_value-1]);
						echo $_SESSION['restaurant_list'][$entity_value-1]->name;
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
		else if($intent === 'restaurantInfo'){
			$info_flag = false;
			foreach($entityArray as $entity_key => $entity_value){
				if($debug){
					echo "==============selected restaurant info =================";
					print_r($_SESSION['selected_restaurant']);

				}
				if($entity_key == 'phoneRequest'){
					$phone_number = $_SESSION['selected_restaurant']->display_phone;

					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=phoneRequest&entity_value=" . $phone_number);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp = "You requested phone number. The phone number is: " . $phone_number;
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'ratingRequest'){
					$rating = $_SESSION['selected_restaurant']->rating;
					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=ratingRequest&entity_value=" . $rating);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp  = "You requested rating. The rating is: " . $rating . " stars";
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'addressRequest'){
					// $addressArr = $_SESSION['selected_restaurant']->location;

					// foreach($addressArr as $addressComp){
					// 	$address = $address . $addressComp . " ";
					// }

					$address = $_SESSION['selected_restaurant']->location->display_address[0] .
							" " . $_SESSION['selected_restaurant']->location->display_address[1] .
							" " . $_SESSION['selected_restaurant']->location->display_address[2];

					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=addressRequest&entity_value=" . $address);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp = "You requested address. " . $address;
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'reviewRequest'){
					$review = $_SESSION['selected_restaurant']->snippet_text;

					if($using_nlg){
						$message_temp  = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=reviewRequest&entity_value=" . $review);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp  = "You requested review: " . $review;
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'isOpenRequest'){
					$isOpen = true;

					if($isOpen){
						$isOpenValue = "open";
					}
					else{
						$isOpenValue = "closed";
					}

					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=isOpenRequest&entity_value=" . $isOpenValue);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp = "You asked whether it is open. It is" . $isOpenValue;
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}

			}
			if(!$info_flag){

				if($using_nlg){
					$message_temp  = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=infoError&entity_value=0");
				}
				if(!$using_nlg || !$message_temp){
					$message_temp = "Cannot recognize you request. What do you want to know about this restaurant?";
				}
				$_SESSION['message'] = $message_temp;
			}
		}
		else if($intent === 'return'){
			//display_list($_SESSION['restaurant_list']);
			$newstate = 'initial';
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			//$_SESSION['selected_restaurant']  = array();
		}
		else if($intent === 'returnStart'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent === 'finished'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/goodbye");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "Good Bye!";
			}
			$_SESSION['message'] = $message_temp;
			display_restaurant($_SESSION['restaurant_list'][$entity_value-1]);
			$newstate = 'finish';
		}
		else if($intent === 'UNKNOWN'){
			if($using_nlg){
				$_SESSION['message'] = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/clarification");
			}
			else{
				$_SESSION['message'] = "I don't understand your request. Can you repeat that?";
			}
			$newstate = 'select';
		}
		else{
			$_SESSION['message'] = "Please select a restaurant from the list!";
			$newstate = 'select';
		}
	}

	/*
	 * current state : getinfo
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
					$phone_number = $_SESSION['selected_restaurant']->display_phone;

					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=phoneRequest&entity_value=" . $phone_number);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp = "You requested phone number. The phone number is: " . $phone_number;
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'ratingRequest'){
					$rating = $_SESSION['selected_restaurant']->rating;
					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=ratingRequest&entity_value=" . $rating);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp  = "You requested rating. The rating is: " . $rating . " stars";
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'addressRequest'){
					// $addressArr = $_SESSION['selected_restaurant']->location;

					// foreach($addressArr as $addressComp){
					// 	$address = $address . $addressComp . " ";
					// }

					$address = $_SESSION['selected_restaurant']->location->display_address[0] .
							" " . $_SESSION['selected_restaurant']->location->display_address[1] .
							" " . $_SESSION['selected_restaurant']->location->display_address[2];

					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=addressRequest&entity_value=" . $address);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp = "You requested address. " . $address;
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'reviewRequest'){
					$review = $_SESSION['selected_restaurant']->snippet_text;

					if($using_nlg){
						$message_temp  = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=reviewRequest&entity_value=" . $review);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp  = "You requested review: " . $review;
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}
				else if($entity_key == 'isOpenRequest'){
					$isOpen = true;

					if($isOpen){
						$isOpenValue = "open";
					}
					else{
						$isOpenValue = "closed";
					}

					if($using_nlg){
						$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=isOpenRequest&entity_value=" . $isOpenValue);
					}
					if(!$using_nlg || !$message_temp){
						$message_temp = "You asked whether it is open. It is" . $isOpenValue;
					}
					$_SESSION['message'] = $message_temp;
					$newstate = 'getinfo';
					$info_flag = true;
					break;
				}

			}
			if(!$info_flag){

				if($using_nlg){
					$message_temp  = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=infoError&entity_value=0");
				}
				if(!$using_nlg || !$message_temp){
					$message_temp = "Cannot recognize you request. What do you want to know about this restaurant?";
				}
				$_SESSION['message'] = $message_temp;
			}
		}
		else if($intent === 'return'){
			$newstate = 'select';
			display_restaurant($_SESSION['selected_restaurant']);
			$_SESSION['message'] = "What do you want to know about this restaurant?";
		}
		else if($intent == 'returnStart'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent === 'UNKNOWN'){
			if($using_nlg){
				$_SESSION['message'] = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/restaurant_info?utterance=" . $user_utterance . "&entity_name=infoError&entity_value=0");
			}
			else{
				$_SESSION['message'] = "Cannot recognize you request. What do you want to know about this restaurant?";
			}
			$newstate = 'getinfo';
		}
		else if($intent === 'finished'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/goodbye");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "Good Bye!";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'finish';
		}
		else{
			display_restaurant($_SESSION['selected_restaurant']);
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
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent == 'finished'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/goodbye");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "Good Bye!";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'finish';
		}
		else{
			$_SESSION['message'] = "What do you want to know about this restaurant?";
		}
	}
	else if($state == 'finish'){

		reset_param();

		if($debug){
			echo "+++++++++++++++ 1 +++++++++++++ <br>";
			echo "current state: " . $state . "<br>";
			echo "current intent: " . $intent . "<br>";
		}
		if($intent ==='return'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent ==='returnStart'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/greeting");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ 
					echo "WARNING: Using Built-in responses!!!";
				}
				$message_temp = "Hi, how can I help you?";
			}
			$_SESSION['message'] = $message_temp;
			$newstate = 'initial';
		}
		else if($intent === 'UNKNOWN'){
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/clarification");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "I don't understand your request. Can you repeat that?";
			}
			$_SESSION['message'] = $message_temp;
		}
		else {
			if($using_nlg){
				$message_temp = get_nlg_response("http://ec2-52-7-6-223.compute-1.amazonaws.com/goodbye");
			}
			if(!$using_nlg || !$message_temp){
				if($debug_nlg){ echo "WARNING: Using Built-in responses!!!";}
				$message_temp = "Good Bye!";
			}
			$_SESSION['message'] = $message_temp;
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
	echo "System response: " . $_SESSION['message'];
	$_SESSION['state'] = $newstate;
	if($debug){
		echo "<br>";
		echo "new state: " . $newstate . "<br>";
	}
	if($debug){
		echo $_SESSION['api_response'];
		echo "<br>";
	}

	if($phone_number){
		echo "<table style=\"width: 400px;\" class=\"center\"><tr><td><img src=\"http://www.clker.com/cliparts/0/f/c/2/1195445181899094722molumen_phone_icon.svg.hi.png\" height=80 width =80/></td>";
		echo "<td><a href=\"tel:" . $phone_not_display . "\">" . $phone_number . "</a></td></tr></table>";
	}
	if($address){

	}
	echo "<p style=\"color:black; font-family:monospace;\">";
	if($newstate == "initial" || $newstate=="filtering"){
		echo "Try \"Find me a Starbucks\" or \"Chinese restaurants in San Francisco\"";
	}
	else if($newstate == "select"){
		echo "Try \"Select the first one\" or \"I want to pick the third one\"";
	}
	else if($newstate == "getinfo"){
		echo "Try \"I want to know the address/phone number/reviews/if it's open/ratings\"";
	}
	echo "To go back, say \"go back\" or \"start over\" to start a new search.  Or you can say \"I'm done\" when you are finished.</p><br>";

	$_SESSION['message'] = str_replace("'","\\'", $_SESSION['message']);
	$_SESSION['message'] = str_replace("\"","\\\"", $_SESSION['message']);
	$_SESSION['message']= str_replace("\n", "", $_SESSION['message']);
	$_SESSION['message'] = str_replace("\r", "", $_SESSION['message']);
	$_SESSION['message'] = str_replace("\t", "", $_SESSION['message']);
	//echo  "<script> speechSynthesis.speak(SpeechSynthesisUtterance('Hello World'));</script>";
	echo  "<button id=\"speak\" onclick=\"speechSynthesis.speak(new SpeechSynthesisUtterance('" . $_SESSION['message'] . "'));\" ><img src=\"http://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Speaker_Icon.svg/1024px-Speaker_Icon.svg.png\" height=40 width=40></button>";
	//echo "<script>setTimeout(\"location.href = 'DMtest.php?words=" . $_SESSION['message'] . "';\",1500);</script>";


?>