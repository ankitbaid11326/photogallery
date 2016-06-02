<?php
require_once('database.php');
require_once("functions.php");
require_once("session.php");
require_once("database.php");
require_once("user.php");
require_once("database_object.php");

    class Photograph extends DatabaseObject{
        protected static $table_name="photographs";
        protected static $db_fields=array('id','filename','type','size','caption');
        public $id;
        public $filename;
        public $type;
        public $size;
        public $caption;

        private $temp_path;
        protected $upload_dir="images";
        public $errors=array();

        protected $upload_errors = array(
        // http://www.php.net/manual/en/features.file-upload.errors.php
        UPLOAD_ERR_OK               => "No errors.",
        UPLOAD_ERR_INI_SIZE     => "Larger than upload_max_filesize.",
        UPLOAD_ERR_FORM_SIZE  => "Larger than form MAX_FILE_SIZE.",
        UPLOAD_ERR_PARTIAL        => "Partial upload.",
        UPLOAD_ERR_NO_FILE        => "No file.",
        UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
        UPLOAD_ERR_CANT_WRITE => "Can't write to disk.",
        UPLOAD_ERR_EXTENSION  => "File upload stopped by extension."
        );

        public function attach_file($file){
            //perform error checking on the form parameters

            if(!$file || empty($file) || !is_array($file)){
                // error : nothing uploaded or wrong argument usage
                $this->errors[]="No file was uplodaed";
                return false;
            }
            elseif ($file['error'] != 0){
                //error : report what PHP went wrong
                $this->errors[]=$this->upload_errors[$file['error']];
                return false;
            } 
            else{
                 //set object attributes to the form parameters.

            $this->temp_path=$file['tmp_name'];
            $this->filename = basename($file['name']);
            $this->type = $file['type'];
            $this->size =$file['size'];

            return true;
            }   
        }

        public function save(){
            //A new record won't have an id yet
            if(isset($this->id)){
                //really just to update the caption
                $this->update();
            }else{
                //make sure there are no errors
                //Attempt to move the file
                //Save a corresponding entry to the database
                if(!empty($this->errors)) { return false;}

                //Make sure the caption is not too long for the DB
                if(strlen($this->caption) <= 255){
                    $this->errors[] = "The caption can only be 255 characters long.";
                    return false;
                }

                // Can'tsave without filename and temp location
                 if(empty($this->filename) || empty($this->temp_path)) {
                    $this->errors[]="The File location was not available . ";
                    return false;
                 }
                 //Attempt to move the file
                 //Save a corresponding entry to the database

                 //Determine the target_path
                 $target_path=".././includes/".$this->upload_dir.$this->filename;
                 if(file_exists($target_path))
                 {
                    $this->errors[]="The File ".$this->file_name " already exists";
                    return false;
                 }
                //Attempt to move the file
                 if(move_uploaded_file($this->temp_path, $target_path)){
                    //success
                    //Save a corrosponding entry to the database
                    if($this->create()){
                        // We are done with temp_path,the file ins't there any more
                        unset($this->temp_path);
                        return true;
                    }
                 }
                 else{
                    //fail
                    //file was not moved.
                    $this->errors[]="The file upload failed ,Possibly due to incorrect permissions on the upload Folder. ";
                    return false;
                 }                
            }
        }


        public static function authenticate($username="",$password=""){
            global $database;
            $username=$database->escape_value($username);
            $password=$database->escape_value($password);

            $sql="SELECT * FROM users ";
            $sql .="Where username='{$username}' ";
            $sql .="and password='{$password}' ";
            $sql .="LIMIT 1";
            $result_array=self::find_by_sql($sql);
            return !empty($result_array) ? array_shift($result_array) : false;


        }

        public function full_name(){
            if(isset($this->firstname) && isset($this->lastname)){
                return $this->firstname . " " . $this->lastname;
            }
            else{
                return "";
            }
        }

        public static function find_all(){
            return self::find_by_sql("SELECT * from ".self::$table_name);
        }

        public static function find_by_id($id=0){
            $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");
            foreach($result_array as $user) {
                echo "User: ". $user->username ."<br />";
                echo "Name: ". $user->full_name() ."<br /><br />";
            }
            return !empty($result_array) ? array_shift($result_array) : false;
        }

        public static function find_by_sql($sql="")
        {
            global $database;
            $result_set = $database->query($sql);
            $object_array = array();
            while ($row = $database->fetch_array($result_set)) {
                $object_array[] = self::instantiate($row);
            }
            return $object_array;
        }

        private static function instantiate($record){
            $class_name=get_called_class();
            $user = new $class_name;
            $user->id           =$record[0];
            $user->username     =$record[1];
            $user->password     =$record[2];
            $user->firstname    =$record[3];
            $user->lastname     =$record[4];

            return $user;
        }

        protected function attributes(){
            $attributes=array();
            foreach(self::$db_fields as $field)
            {
                if(property_exists($this,$field)){
                    $attributes[$fields]=$this->$field;
                }
            }
            return $attributes;
        }

        protected function sanitized_attributes(){
            global $database;
            $clean_attributes=array();
            foreach($this->attributes() as $key => $value){
                $clean_attributes[$key] = $database->escape_value($value);
            }
            return $clean_attributes;
        }

        public function create(){
            global $database;

            $attributes = $this->sanitized_attributes();

            $sql="INSERT INTO ".self::$table_name." (";
            $sql.=join(", ",array_keys($attributes));
            $sql.=") values ('";
            $sql.=join("', '",array_values($attributes));
            $sql.="')";
            if($database->query($sql)){
                $this->id=$database->insert_id();
                return true;
            }else{
                return false;
            }
        }

        public function update()
        {
            global $database;
//            echo "" . $this->firstname;
//            echo "<hr>";
//            echo "" . $this->username;

//            use firstname and lastname as $this->first_name and $this->last_name

            $attributes=$this->sanitized_attributes();
            $attribute_pairs=array();
            foreach($attributes as $key => $value){
                $attribute_pairs[]="{$key}='{$value}'";
            }


            $sql = "UPDATE ".self::$table_name." SET ";
            $sql.=join(", ",$attribute_pairs);
            $sql.= " WHERE id=".$database->escape_value($this->id);
            $database->query($sql);
            return ($database->affected_rows() == 1) ? true : false;
        }

        //replace with a custom save()

        // public function save()
        // {
        //     // A new record won't have an id yet.
        //     return isset($this->id) ? $this->update() : $this->create();
        // }

        public function delete(){
            global $database;
            $sql="DELETE FROM ".self::$table_name." ";
            $sql.="WHERE id=". $database->escape_value($this->id);
            $sql.=" LIMIT 1";
            $database->query($sql);
            return ($database->affected_rows() == 1) ? true : false;

        }

    }

?>