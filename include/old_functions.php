

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
	