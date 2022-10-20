<?php
//getting the database connection
include_once 'includedFiles.php';
//an array to display response
$response = array();

//if it is an api call
//that means a get parameter named api call is set in the URL
//and with this parameter we are concluding that it is an api call
if (isset($_GET['apicall'])) {

    switch ($_GET['apicall']) {

        case 'signup':

            //checking the parameters required are available or not
            if (isTheseParametersAvailable(array('username', 'full_name', 'email', 'phone', 'password'))) {

                //getting the values
                $username = $_POST['username'];
                $full_name = $_POST['full_name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $password = md5($_POST['password']);
                $profilePic = "assets/images/profile-pics/user.png";
                $first_three_letters = substr($username, 0, 3);
                $id = "mw" . uniqid() . $first_three_letters;
                $date = date('Y-m-d');
                $user_regStatus = "registered";
                //checking if the user is already exist with this username or email
                //as the email and username should be unique for every user
                $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR Email = ? OR phone = ?");
                $stmt->bind_param("sss", $username, $email, $phone);
                $stmt->execute();
                $stmt->store_result();


                //if the user already exist in the database
                if ($stmt->num_rows > 0) {
                    $response['error'] = true;
                    $response['message'] = 'User already registered';
                    $stmt->close();
                } else {

                    //if user is new creating an insert query
                    $stmt = $db->prepare("INSERT INTO users (`id`,`username`,`firstName`,`email`,`phone`,`Password`,`signUpDate`,`profilePic`,`status`) VALUES (?, ?, ?, ?,?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssssss", $id, $username, $full_name, $email,$phone, $password, $date, $profilePic, $user_regStatus);

                    //if the user is successfully added to the database
                    if ($stmt->execute()) {

                        //fetching the user back
                        $stmt = $db->prepare("SELECT `id`, `username`, `firstName`, `email`,`phone`,`password`, `signUpDate`, `profilePic`, `status`, `mwRole` FROM users WHERE email = ? AND password = ?");
                        $stmt->bind_param("ss", $email, $password);
                        $stmt->execute();
                        $stmt->bind_result($id, $username, $full_name, $email,$phone, $password, $signUpDate, $profilePic, $status, $mwRole);

                        $stmt->fetch();


                        $user = array(
                            'id' => $id,
                            'username' => $username,
                            'full_name' => $full_name,
                            'email' => $email,
                            'phone' => $phone,
                            'password' => $password,
                            'signUpDate' => $signUpDate,
                            'profilePic' => $profilePic,
                            'status' => $status,
                            'mwRole' => $mwRole,
                        );


                        $stmt->close();

                        //adding the user data in response
                        $response['error'] = false;
                        $response['message'] = 'User registered successfully';
                        $response['user'] = $user;
                    }
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'required parameters are not available';
            }

            break;

        case 'login':

            //for login we need the username and password
            if (isTheseParametersAvailable(array('username', 'password'))) {
                //getting values
                $username = $_POST['username'];
                $password = md5($_POST['password']);

                $check_email = Is_email($username);
                if ($check_email) {
                    // email & password combination
                    $stmt = $db->prepare("SELECT `id`, `username`, `firstName`, `email`,`phone`, `password`, `signUpDate`, `profilePic` , `status`, `mwRole` FROM users WHERE email = ? AND password = ?");
                    $stmt->bind_param("ss", $username, $password);

                } else {
                    // username & password combination
                    $stmt = $db->prepare("SELECT `id`, `username`, `firstName`, `email`,`phone`, `password`, `signUpDate`, `profilePic`, `status`, `mwRole` FROM users WHERE (username = ? OR phone = ?) AND password = ?");
                    $stmt->bind_param("sss", $username,$username, $password);

                }

                //creating the query

                $stmt->execute();

                $stmt->store_result();

                //if the user exist with given credentials
                if ($stmt->num_rows > 0) {

                    $stmt->bind_result($id, $username, $full_name, $email, $phone, $password, $signUpDate, $profilePic, $status, $mwRole);
                    $stmt->fetch();

                    $user = array(
                        'id' => $id,
                        'username' => $username,
                        'full_name' => $full_name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $password,
                        'signUpDate' => $signUpDate,
                        'profilePic' => $profilePic,
                        'status' => $status,
                        'mwRole' => $mwRole,
                    );

                    $response['error'] = false;
                    $response['message'] = 'Login successfull';
                    $response['user'] = $user;
                } else {
                    //if the user not found
                    $response['error'] = true;
                    $response['message'] = 'Invalid username or password';
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'required parameters are not available';
            }

            break;

        default:
            $response['error'] = true;
            $response['message'] = 'Invalid Operation Called';
    }
} else {
    //if it is not api call
    //pushing appropriate values to response array
    $response['error'] = true;
    $response['message'] = 'Invalid API Call';
}

//displaying the response in json structure 
echo json_encode($response);

//function validating all the paramters are available
//we will pass the required parameters to this function 
function isTheseParametersAvailable($params)
{

    //traversing through all the parameters
    foreach ($params as $param) {
        //if the paramter is not available
        if (!isset($_POST[$param])) {
            //return false
            return false;
        }
    }
    //return true if every param is available
    return true;
}

function Is_email($user)
{
    //If the username input string is an e-mail, return true
    if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}
