<?php
 
class DB_Functions {
 
    private $db;
 
    //put your code here
    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $this->db = new DB_Connect();
        $this->db->connect();
    }
 
    // destructor
    function __destruct() {
         
    }
 
    /**
     * STOREUSER()
     * @param 
     *      name - new user's name
     *      email - new user's email
     *      password - new user's password
     * Description: Storing new user and returns user details
     */
    public function storeUser($name, $email, $password) 
    {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
        $result = mysql_query("INSERT INTO users(unique_id, name, email, encrypted_password, salt, created_at) VALUES('$uuid', '$name', '$email', '$encrypted_password', '$salt', NOW())");
        // check for successful store
        if ($result) {
            // get user details 
            $uid = mysql_insert_id(); // last inserted id
            $result = mysql_query("SELECT * FROM users WHERE uid = $uid");
            // return user details
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }
    


    /**
     * GETUSERSBYEMAILANDPASSWORD()
     * @param 
     *      email - user's email
     *      password - user's password
     * Description: Get user's info by email and password
     */
    public function getUserByEmailAndPassword($email, $password) 
    {
        $result = mysql_query("SELECT * FROM users WHERE email = '$email'") or die(mysql_error());
        // check for result 
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysql_fetch_array($result);
            $salt = $result['salt'];
            $encrypted_password = $result['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $result;
            }
        } else {
            // user not found
            return false;
        }
    }


    /**
     * UPDATE_PROFILE_PICTURE
     * @param 
     *      uid - users's uid
     *      image - image to upload (should be normalized by this point 96x96 pixels)
     * Description: Updates profile picture
     */
    public function update_profile_picture($uid, $image)
    {
        $image = file_get_contents($image);
        $image = base64_encode($image);
        $sql = "UPDATE users SET image='$image' WHERE uid='$uid'";
        $result = mysql_query($sql);
        if($result)
            return true;
        else
            return false;
    }


    /**
     * UPDATE_LOCATION_TIME
     * @param 
     *      email - user's email
     *      location - user's location
     *      time - user's time to sit
     *      is_sitting - flag whether user is sitting or not
     * Description: Checks if a user exists using their email.
     */
    public function update_location_time_($uid, $location, $time, $is_sitting)
    {
        if($is_sitting='0'){
            $sql = "UPDATE users SET is_sitting = 0 WHERE uid='$uid'";
            $result = mysql_query($sql);
            if($result)
                return true;
            else
                return false;
        }else{
            //update sitting
        }
    }


    /**
     * GET_NEIGHBOR_DATA()
     * @param 
     *      uid - user's uid
     * Description: 
     */
    public function get_neighbor_data($uid)
    {

    }



	public function set_data($uid, $data)
	{	
		echo "uid: $uid";
		//$sql = "UPDATE users SET 'location' = $data['location'], 'is_sitting' = $data['is_sitting'], 'image' = $data['image'] WHERE 'uid' = $uid";   
		$location = $data['location'];
		$is_sitting = $data['is_sitting'];
		$image = $data['image'];
		$image = file_get_contents($image);
		$image = base64_encode($image);
				    
		$sql = "UPDATE users SET location='$location' ,is_sitting='$is_sitting',image='$image' WHERE uid='$uid'";
		$result = mysql_query($sql);
		if($result)
		{	
			echo "sucessfully update from local";
			echo '<img height="300" width="300" src="data:image;base64,'.$image.' "> ';
            echo "\r\n";
            echo "image from database request:";

            $sql = "SELECT image FROM users WHERE uid='$uid'";
            $result = mysql_query($sql);
            $row = mysql_fetch_array($result);
            echo '<img height="300" width="300" src="data:image;base64,'.$row['image'].' "> ';

            return true;
		}else{
			return false;
		}


	}




    /*
     * 
     */ 
    public function getUserData($uid)
    {
        $db = new DB_CONNECT();
	
        // array for json response
        $response["user"] = array();
         
        // Grab user row we are interested in 
        $result = mysql_query("SELECT * FROM users WHERE uid = '$uid'") or die(mysql_error());
        
        while($row = mysql_fetch_array($result)){    	
		    // temporary array to create single category
            $tmp = array();
            $tmp["uid"] = $row["uid"];
            $tmp["is_sitting"] = $row["is_sitting"];
            $tmp["location"] = $row["location"];
            $tmp["image"] = $row["image"];
            $tmp["name"] = $row["name"];		
            // push category to final json array
            array_push($response["user"], $tmp);
        }
        return $response;
    }
	


    /**
     * ISUSEREXISTED()
     * @param 
     *      email - user's email
     * Description: Checks if a user exists using their email.
     */
    public function isUserExisted($email) 
    {
        $result = mysql_query("SELECT email from users WHERE email = '$email'");
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) {
            // user existed 
            return true;
        } else {
            // user not existed
            return false;
        }
    }
 

    /**
     * HASHSSHA()
     * @param 
     *      password - user supplied password we salt and encrypt
     * Description: returns salt and encrypted password
     */
    public function hashSSHA($password) 
    {
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 


    /**
     * CHECKHASHSSHA()
     * @param 
     *      salt - user's salt from db
     *      password - user's password
     * returns hash string of passsword
     */
    public function checkhashSSHA($salt, $password) 
    {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }


    /**
     * GETFRIENDS()
     * @param
     *      $uid1 - The user who wants to view his/her friends. 
     * Description:  We use this function to get the data to display to a user's 'neighbors list'
     * ~ZR
     */
    public function getFriends($uid1)
    {
        $result = mysql_query("SELECT * FROM friendships WHERE uid1 = '$uid1' AND accepted = 1 ");
        $response["friendships"] = array(); 

        // grabing friends
        while($row = mysql_fetch_array($result)){   
            $uid2 = $row["uid2"];
            $friend_result = mysql_query("SELECT * FROM friendships WHERE uid1 = '$uid2' AND uid2 = '$uid1' AND accepted = 1");
            while($row2 = mysql_fetch_array($friend_result))
            {
                $tmp = array();
                $tmp["uid1"] = $uid1;
                $tmp["uid2"] = $uid2;
                echo "$uid1 and $uid2 are friends";
                array_push($response["friendships"], $tmp);    
            }
        }
        // all accepted friends for uid1 
        return $response;
    }


    /** 
     * GETPENDINGFRIENDS()
     * @param
     *      $uid1 - The user who wants to view pending friend requests
     * Description: $uid1 wants to view pending friends
     * ~ZR
     */
    public function getPendingFriends($uid1)
    {
        $result = mysql_query("SELECT * FROM friendships WHERE uid1 = '$uid1' AND accepted = 0 ");
        $response["pending_friendships"] = array(); 

        // TODO DONT MAKE REQUESTS INSIDE LOOPS
        // grabing pending friends
        while($row = mysql_fetch_array($result)){   
            $uid2 = $row["uid2"];
            $pending_result = mysql_query("SELECT * FROM friendships WHERE uid1 = '$uid2' AND uid2 = '$uid1' AND accepted = 1");
            while($row2 = mysql_fetch_array($pending_result))
            {
                $tmp = array();
                $tmp["uid1"] = $uid1;
                $tmp["uid2"] = $uid2;
                echo "$uid2 wants to be friends with $uid1";
                array_push($response["pending_friendships"], $tmp);
            }
        }
        // all pending/accepted friends for uid1 
        return $response;
    }
    

    /** 
     * REQUESTFRIEND()
     * @param
     *      $uid1 - The user that is making the friend request
     *      $uid2 - $uid1's requested friend
     * Description: $uid1 wants to be friends with $uid2
     * ~ZR
     */
    public function requestFriend($uid1, $uid2)
    {
        // checking if uid2 exists (the friend uid1 is requesting)
        $result = mysql_query("SELECT * FROM users WHERE uid = '$uid2'") or die(mysql_error());
        $no_of_rows = mysql_num_rows($result);

        // if uid2 exist, then create link in friendship table
        if($no_of_rows > 0 )
        {
            $result = mysql_query("INSERT INTO friendships(uid1, uid2, accepted, broadcast) VALUES ($uid1, $uid2, 1, 0)") or die(mysql_error());
            //check result for error? 
            $result2 = mysql_query("INSERT INTO friendships(uid1, uid2, accepted, broadcast) VALUES ($uid2, $uid1, 0, 0)") or die(mysql_error());
        }
    }

}
 
?>
