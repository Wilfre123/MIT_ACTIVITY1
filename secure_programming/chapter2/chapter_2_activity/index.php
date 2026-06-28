<?php
session_start();


if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
    $_SESSION['lock_time'] = 0;
}

if ($_SESSION['attempts'] >= 3 && time() >= $_SESSION['lock_time']) {
    $_SESSION['attempts'] = 0;
    $_SESSION['lock_time'] = 0;
}

$conn = new mysqli("localhost", "root", "", "secureProgrammingDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        echo "Username and password are required.";
    } else {

        if ($_SESSION['attempts'] >= 3 && time() < $_SESSION['lock_time']) {
            $remaining = $_SESSION['lock_time'] - time();
            echo "Too many failed attempts. Please wait " . $remaining . " seconds.";
            echo "<script>
                    let timeLeft = " . $remaining . ";
                    let x = setInterval(function() {
                        timeLeft--;
                        if (timeLeft <= 0) {
                            clearInterval(x);
                            window.location.href = 'index.php';
                        }
                    }, 1000);
                  </script>";
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        if ($stmt === false) {
            die("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $username, $password);
        
        if ($stmt->execute() === false) {
            die("Query execution failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
           
            $_SESSION['attempts'] = 0;
            $_SESSION['lock_time'] = 0;
            
            $_SESSION['username'] = $username;
            $_SESSION['loggedin'] = true;
            
            header("Location: target_page.php");
            exit();
        } else {
          
            if ($_SESSION['attempts'] < 3) {
                $_SESSION['attempts']++;
            }
            
            if ($_SESSION['attempts'] >= 3) {
                $_SESSION['lock_time'] = time() + 5;
            }
            
            echo "Invalid username or password.<br>";
            echo "Failed attempts: " . $_SESSION['attempts'];
        }

        $stmt->close();
    }
}

$conn->close();
?>


 <!DOCTYPE html> 
 <html lang="en"> 
 <head> 
     <meta charset="UTF-8"> 
     <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
     <title>Login Page</title> 
 </head> 
 <body> 
     <h2>Login</h2> 
     <form method="POST" action=""> 
         <label for="username">Username:</label><br> 
         <input type="text" id="username" name="username" required><br> 
         <label for="password">Password:</label><br> 
         <input type="password" id="password" name="password" 
required><br><br> 
         <input type="submit" name="login" value="Login"> 
     </form> 
 </body> 
 </html> 