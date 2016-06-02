<?php
require_once('database.php');
require_once("functions.php");
require_once("session.php");
require_once("database.php");
require_once("database_object.php");

    class User{

        protected static $table_name="users";
        public $id;
        public static $db_fields=array('id', 'username', 'password', 'first_name', 'last_name');
        public $username;
        public $password;
        public $first_name;
        public $last_name;

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

        public function save()
        {
            // A new record won't have an id yet.
            return isset($this->id) ? $this->update() : $this->create();
        }

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