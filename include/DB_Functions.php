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
     * Storing new user
     * returns user details
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
     * Get user's info by email and password
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




/*
 * Function used to fetch user data (generic right now)
 */ 
    public function getUserData()
    {
        $db = new DB_CONNECT();

        // array for json response
        $response = array();
        $response["users"] = array();
         
        // Mysql select query
        $result = mysql_query("SELECT * FROM users");
         
        while($row = mysql_fetch_array($result)){
           
            // temporary array to create single category
            $tmp = array();
            //$tmp["user_id"] = $row["user_id"];
            //$tmp["is_sitting"] = $row["is_sitting"];
            $tmp["name"] = $row["name"];
             
            // push category to final json array
            array_push($response["users"], $tmp);
        }
        return $response;
    }



    /**
     * Check user is existed or not
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
     * Encrypting password
     * @param password
     * returns salt and encrypted password
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
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) 
    {
 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    }

    /** 
     * ZR at the very least, we know the @param name exists in user (otherwise they wouldnt be able to log on)
     * Get User's uid1's friends
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
                echo "\r\n";
                array_push($response["friendships"], $tmp);    
            }
        }
        // all accepted friends for uid1 
        return $response;
    }

    /* ZR get pending friends */
    public function getPendingFriends($uid1)
    {
        $result = mysql_query("SELECT * FROM friendships WHERE uid1 = '$uid1' AND accepted = 0 ");
        $response["pending_friendships"] = array(); 

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
    
    /* This is our friend request function */
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