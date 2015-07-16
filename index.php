<?php
 
/**
 * File to handle all API requests
 * Accepts GET and POST
 * 
 * Each request will be identified by TAG
 * Response will be JSON data
 */
 
  /**
 * check for POST request 
 */

/* AW for testing purposes */
function display()
{
    echo "hello ".$_POST["usermail"];
}



/* Execution begins here */
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // get tag
    $tag = $_POST['tag'];

    // AW if the submission is from web form (rather than mobile) we need to prep the tag
    if ($tag == "web") {
        if (isset($_POST['login']) && $_POST['login'] == 'Login') {
            $tag = 'login';
        } else if (isset($_POST['new_user']) && $_POST['new_user'] == 'Register') {
            $tag = 'register';
        } 
        /* ZR testing friendships */
        else if (isset($_POST['friendships']) && $_POST['friendships'] == 'friendships'){
            $tag = 'friendships';
        }
        else if (isset($_POST['pending_friendships']) && $_POST['pending_friendships'] == 'pending_friendships'){
            $tag = 'pending_friendships';
        }
        else if (isset($_POST['request_friend']) && $_POST['request_friend'] == 'request_friend'){
            $tag = 'request_friend';
        }
	else if (isset($_POST['set_data']) && $_POST['set_data'] == 'set user data')
	{
		$tag = 'set_data';
	}
	else if (isset($_POST['get_data']) && $_POST['get_data'] == 'get user data')
	{
		$tag = 'get_data';
	}
    }
 
    // include db handler
    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();
 
    // response Array
    $response = array("tag" => $tag, "error" => FALSE);
 
    // check for tag type
    if ($tag == 'login') {
        // Request type is check Login
        $email = $_POST['email'];
        $password = $_POST['password'];
 
        // check for user
        $user = $db->getUserByEmailAndPassword($email, $password);
        if ($user != false) {
            // user found
            $response["error"] = FALSE;
            $response["uid"] = $user["unique_id"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            $response["user"]["created_at"] = $user["created_at"];
            $response["user"]["updated_at"] = $user["updated_at"];
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = TRUE;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    } 
    else if ($tag == 'register') {
        // Request type is Register new user
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
 
        // check if user is already existed
        if ($db->isUserExisted($email)) {
            // user is already existed - error response
            $response["error"] = TRUE;
            $response["error_msg"] = "User already existed";
            echo json_encode($response);
        } else {
            // store user
            $user = $db->storeUser($name, $email, $password);
            if ($user) {
                // user stored successfully
                $response["error"] = FALSE;
                $response["uid"] = $user["unique_id"];
                $response["user"]["name"] = $user["name"];
                $response["user"]["email"] = $user["email"];
                $response["user"]["created_at"] = $user["created_at"];
                $response["user"]["updated_at"] = $user["updated_at"];
                echo json_encode($response);
            } else {
                // user failed to store
                $response["error"] = TRUE;
                $response["error_msg"] = "Error occured in Registartion";
                echo json_encode($response);
            }
        }
    }
	
	/* 
	 * 	ZR 
	 *	Testing seting data
	*/
	else if ($tag == 'set_data')
	{
	
        
        	$uid = $_POST['uid'];
		$data = array();
		$data['is_sitting'] = $_POST['is_sitting'];
		$data['location'] = $_POST['location'];
		$data['image'] = $_FILES['image'];	
		$data_set = $db->set_data($uid, $data);
		if($data_set)
		{
			echo "sucessfully set data";
		}
		else{
	                $response["error"] = TRUE;
                	$response["error_msg"] = "Error occured in Registartion";
                	echo json_encode($response);
		}
	} 
    /* 
     *   ZR
     *   This is a generic fetch, it will grab all users data 
     *   TODO ONLY FOR TESTING PURPOSES. DO NOT IMPLEMENT IN REAL PORCHSIT 
     */
    else if ($tag == 'get_data') 
    {
        // if tag is get neighbors we want to return all neighbors for said user
        // for testing we are just going to return all users
        
        $uid = $_POST['uid'];
	if($neighbor_data = $db->getUserData($uid))
        {
            echo json_encode($neighbor_data);

        } else { 
            $response["error"] = TRUE;
            $response["error_msg"] = "Error occured in requesting neighbor data";
            echo json_encode($response);
        }
    } 
    /* ZR this gets the users friend array */
    else if ($tag == 'friendships'){

        /* Example of sql to insert default friendship 
         * INSERT INTO friendships(uid1, uid2, accepted, broadcast) VALUES (1, 3, 0, 0);
         */
        $uid = $_POST['uid'];
        $friend_array = $db->getFriends($uid);
        $response = $friend_array;
        echo json_encode($response);
    } 
    else if ($tag == 'pending_friendships'){
        $uid = $_POST['uid'];
        $friend_array = $db->getPendingFriends($uid);
    }
    else if ($tag == 'request_friend'){
        $uid1 = $_POST['uid1'];
        $uid2 = $_POST['uid2'];
        $db->requestFriend($uid1,$uid2);
    }    


    /* ZR default error. Some shit went wrong */
    else {
        // user failed to store
        $response["error"] = TRUE;
        $response["error_msg"] = "Unknow 'tag' value. It should be either 'login' or 'register'";
        $response["tag"] = "Your tag is set as $tag";
	echo json_encode($response);
    }
}

/* AW */
else if( isset($_POST['login']) ) {
	display();
}
/* ^^ AW */

else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameter 'tag' is missing!";
    echo json_encode($response);
}
?>
