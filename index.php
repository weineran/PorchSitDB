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
    	else if (isset($_POST['update_prof_pic']) && $_POST['update_prof_pic'] == 'upload profile picture'){
    		$tag = 'update_prof_pic';
    	}
    	else if (isset($_POST['get_data']) && $_POST['get_data'] == 'get user data'){
    		$tag = 'get_data';
    	}
    }
 
    // include db handler
    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();
 
    // response Array
    $response = array("tag" => $tag, "error" => FALSE);
 
    /*
     * LOGIN 
     * check for tag type
     */
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

    /*
     * REGISTER 
     * check for tag type
     */
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

    /* UPDATE_PROF_PIC */
    else if ($tag == 'update_prof_pic')
    {
        $uid = $_POST['uid'];
        $image = $_FILES['image']['tmp_name'];

        if($result = $db->update_profile_picture($uid, $image)){
            $response["error"] = FALSE;
        }else{
            $response["error"] = TRUE;
            $response["error_msg"] = "Error while uploading profile picture";
        }
        echo json_encode($response);
    }

    /* UPDATE_LOCATION_TIME */
    else if ($tag == 'update_location_time')
    {
        $uid = $_POST['uid'];
        $location = $_POST['location'];
        $time = $_POST['time'];
        $is_sitting = $_POST['is_sitting'];

        if($result = $db->update_location_time($uid, $location, $time, $is_sitting)){
            $response["error"] = FALSE;
        }else{  
            $response["error"] = TRUE;
            $response["error_msg"] = "Error while updating location and time";
        }
        echo json_encode($response);
    }

    /* GET_NEIGHBOR_DATA */
    else if ($tag == 'get_neighbor_data') 
    {
        $uid = $_POST['uid'];
        if($neighbor_data = $db->getUserData($uid)){
            // an array is returned
            echo json_encode($neighbor_data);
        } else { 
            $response["error"] = TRUE;
            $response["error_msg"] = "Error occured in requesting neighbor data";
            echo json_encode($response);
        }
    } 

    /* FRIENDSHIPS */
    else if ($tag == 'friendships')
    {
        $uid = $_POST['uid'];
        if($result = $db->getFriends($uid)){
            echo json_encode($result);
        }else{
            $response["error"] = TRUE;
            $response["error_msg"] = "Error occured in requesting to get friendship data";
            echo json_encode($response);
        }
    } 

    /* PENDING_FRIENDSHIPS */
    else if ($tag == 'pending_friendships'){
        $uid = $_POST['uid'];

        if($result = $db->getPendingFriends($uid)){
            echo json_encode($result);
        }else{
            $response["error"] = TRUE;
            $response["error_msg"] = "Error occured in requesting pending friendships";
            echo json_encode($response);
        }
    }

    /* REQUEST_FRIEND */ 
    else if ($tag == 'request_friend'){
        $uid1 = $_POST['uid1'];
        $uid2 = $_POST['uid2'];
        if($result = $db->requestFriend($uid1,$uid2)){
            echo json_encode($result);
        }else{
            $response["error"] = TRUE;
            $response["error_msg"] = "Error occured in requesting friendships";
            echo json_encode($response);
        }
    }    

    /* DELETE_FRIEND */ 
    else if ($tag == 'delete_friend'){
        $uid1 = $_POST['uid1'];
        $uid2 = $_POST['uid2'];
        if($result = $db->deleteFriend($uid1,$uid2)){
            echo json_encode($result);
        }else{
            $response["error"] = TRUE;
            $response["error_msg"] = "Error occured in deleting friendships";
            echo json_encode($response);
        }
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
