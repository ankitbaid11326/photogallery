<?php
require_once('database.php');
require_once("functions.php");
require_once("session.php");
require_once("database.php");
require_once("user.php");
require_once("database_object.php");

    class Session{
        private $logged_in=false;
        public $user_id;

        function __construct(){
            session_start();
            $this->check_login();
            if($this->logged_in){
                // say what to do..?
            }
            else{
                // something else to do
            }
        }

        public function is_logged_in(){
            return $this->logged_in;
        }

        public function login($user){
            //database should find user based on username/password
            if($user){
                $this->user_id=$_SESSION['user_id']=$user->id;
                $this->logged_in=true;
            }
        }

        public function logout(){
            unset($_SESSION['user_id']);
            unset($this->user_id);
            $this->logged_in=false;
        }

        private function check_login(){
            if(isset($_SESSION['user_id']))
            {
                $this->user_id=$_SESSION['user_id'];
                $this->logged_in=true;
            }else{
                unset($this->user_id);
                $this->logged_in=false;
            }
        }


    }

$session = new Session();
?>