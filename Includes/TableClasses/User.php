<?php

class User
{

    private $con;
    private $no;
    private $id;
    private $username;
    private $firstname;
    private $lastname;
    private $email;
    private $password;
    private $signupDate;
    private $profilePic;
    private $songsPlayed;
    private $status;
    private $mwRole;

    public function __construct($con, $id)
    {
        $this->con = $con;
        $this->id = $id;

        $query = mysqli_query($this->con, "SELECT `no`, `id`, `username`, `firstName`, `lastName`, `email`, `password`, `signUpDate`, `profilePic`, `songsplayed`, `status`, `mwRole` FROM users WHERE id='$this->id'");
        $user_data = mysqli_fetch_array($query);


        if (mysqli_num_rows($query) < 1) {

            $this->no = null;
            $this->id = null;
            $this->username = null;
            $this->firstname = null;
            $this->lastname = null;
            $this->email = null;
            $this->password = null;
            $this->signupDate = null;
            $this->profilePic = null;
            $this->songsPlayed = null;
            $this->status = null;
            $this->mwRole = null;
        } else {


            $this->no = $user_data['no'];
            $this->id = $user_data['id'];
            $this->username = $user_data['username'];
            $this->firstname = $user_data['firstName'];
            $this->lastname = $user_data['lastName'];
            $this->email = $user_data['email'];
            $this->password = $user_data['password'];
            $this->signupDate = $user_data['signUpDate'];
            $this->profilePic = $user_data['profilePic'];
            $this->songsPlayed = $user_data['songsplayed'];
            $this->status = $user_data['status'];
            $this->mwRole = $user_data['mwRole'];
        }
    }

    /**
     * @return mixed|null
     */
    public function getNo()
    {
        return $this->no;
    }

    /**
     * @return mixed|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed|null
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return mixed|null
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return mixed|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed|null
     */
    public function getSignupDate()
    {
        return $this->signupDate;
    }

    /**
     * @return mixed|null
     */
    public function getProfilePic()
    {
        return $this->profilePic;
    }

    /**
     * @return mixed|null
     */
    public function getSongsPlayed()
    {
        return $this->songsPlayed;
    }

    /**
     * @return mixed|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed|null
     */
    public function getMwRole()
    {
        return $this->mwRole;
    }


}