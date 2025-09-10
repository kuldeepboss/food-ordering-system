<?php
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';
$active_form = 'register'; // Default to register form first

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
        $active_form = 'login';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['email']; // Use email as display name
                
                // Redirect to the page they were trying to access or home
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                header('Location: ' . $redirect);
                exit();
            } else {
                $error = 'Invalid email or password';
                $active_form = 'login';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
            $active_form = 'login';
        }
    }
}

// Handle Register
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
        $active_form = 'register';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
        $active_form = 'register';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
        $active_form = 'register';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
        $active_form = 'register';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already exists';
                $active_form = 'register';
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password, phone, address) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $hashed_password, $phone, $address]);
                
                $success = 'Account created successfully! You can now login.';
                $active_form = 'login';
                
                // Clear form data
                $email = $phone = $address = '';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
            $active_form = 'register';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register & Login - FoodHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ff6b35 100%);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background Particles */
        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="particles" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"><animate attributeName="opacity" values="0.1;0.3;0.1" dur="3s" repeatCount="indefinite"/></circle></pattern></defs><rect width="100" height="100" fill="url(%23particles)"/></svg>');
            z-index: 0;
            animation: particleFloat 20s ease-in-out infinite;
        }

        @keyframes particleFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Poppins', sans-serif;
        }

        /* 3D Flip Card Container */
        .auth-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 3rem;
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 10;
            transform-style: preserve-3d;
            transition: all 0.4s ease;
        }

        .auth-form:hover {
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.3);
        }

        .auth-form .brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-form .brand i {
            font-size: 4rem;
            color: #ff6b35;
            margin-bottom: 1rem;
            animation: brandPulse 2s ease-in-out infinite;
            text-shadow: 0 8px 16px rgba(255, 107, 53, 0.4);
        }

        @keyframes brandPulse {
            0%, 100% { transform: scale(1) rotateY(0deg); }
            50% { transform: scale(1.1) rotateY(180deg); }
        }

        .auth-form h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
            font-size: 2.2rem;
            font-weight: 700;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            background: linear-gradient(45deg, #ffd23f, #ff6b35, #ef476f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* 3D Tab System */
        .form-tabs {
            display: flex;
            margin-bottom: 2rem;
            border-radius: 15px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-tab {
            flex: 1;
            padding: 1.2rem;
            text-align: center;
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.4s ease;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            position: relative;
            transform-style: preserve-3d;
        }

        .form-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #ff6b35, #ef476f);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }

        .form-tab.active::before,
        .form-tab:hover::before {
            opacity: 1;
        }

        .form-tab.active,
        .form-tab:hover {
            color: white;
            transform: translateY(-3px);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* 3D Form Content with Flip Animation */
        .form-content {
            display: none;
            animation: formSlideIn 0.6s ease-out;
        }

        .form-content.active {
            display: block;
        }

        @keyframes formSlideIn {
            from {
                opacity: 0;
                transform: translateY(30px) rotateX(-15deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0deg);
            }
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: white;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Glassmorphism Input Fields */
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.4s ease;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            font-weight: 500;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b35;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.3);
        }

        /* 3D Password Field */
        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 3.5rem;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.2rem;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 50%;
        }

        .password-toggle:hover {
            color: #ff6b35;
            background: rgba(255, 107, 53, 0.1);
            transform: translateY(-50%) scale(1.1);
        }

        /* 3D Glowing Button */
        .btn-auth {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #ff6b35, #ef476f);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
        }

        .btn-auth::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-auth:hover::before {
            left: 100%;
        }

        .btn-auth:hover {
            transform: translateY(-3px) rotateX(10deg);
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.6);
        }

        /* Enhanced Error/Success Messages */
        .error {
            color: #ff4757;
            text-align: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: rgba(255, 71, 87, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 71, 87, 0.3);
            font-weight: 600;
            animation: errorShake 0.6s ease-in-out;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .success {
            color: #2ed573;
            text-align: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: rgba(46, 213, 115, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(46, 213, 115, 0.3);
            font-weight: 600;
            animation: successGlow 0.6s ease-in-out;
        }

        @keyframes successGlow {
            0%, 100% { box-shadow: 0 0 0 rgba(46, 213, 115, 0.4); }
            50% { box-shadow: 0 0 20px rgba(46, 213, 115, 0.4); }
        }

        .required {
            color: #ff6b35;
            font-weight: 700;
        }

        /* 3D Social Login Icons */
        .liw {
            padding: 1.5rem 0 1rem;
            text-align: center;
            color: white;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .icons a {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            transition: all 0.4s ease;
            transform-style: preserve-3d;
        }

        .icons a:hover {
            background: linear-gradient(135deg, #ff6b35, #ef476f);
            transform: translateY(-5px) rotateY(15deg);
            box-shadow: 0 15px 30px rgba(255, 107, 53, 0.4);
        }

        /* 3D Back Link */
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }

        .back-link a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
        }

        .back-link a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.1);
        }

        /* Switch Button */
        .switch-btn {
            background: linear-gradient(135deg, #06d6a0, #118ab2);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 8px 20px rgba(6, 214, 160, 0.3);
        }

        .switch-btn:hover {
            transform: translateY(-3px) rotateX(10deg);
            box-shadow: 0 12px 30px rgba(6, 214, 160, 0.5);
        }

        .form-switch {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-switch p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1rem;
            font-weight: 500;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .auth-form {
                padding: 2rem;
                margin: 1rem;
            }
            .icons {
                gap: 0.5rem;
            }
            .icons a {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        
        <div class="auth-form">
            <div class="brand">
                <i class="fas fa-utensils"></i>
                <h2>Welcome to FoodHub</h2>
            </div>
            
            <!-- Form Tabs -->
            <div class="form-tabs">
                <button class="form-tab <?php echo $active_form === 'register' ? 'active' : ''; ?>" onclick="switchForm('register')">
                    <i class="fas fa-user-plus"></i> Register
                </button>
                <button class="form-tab <?php echo $active_form === 'login' ? 'active' : ''; ?>" onclick="switchForm('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <!-- Register Form -->
            <div id="registerForm" class="form-content <?php echo $active_form === 'register' ? 'active' : ''; ?>">
                <form method="POST">
                    <input type="hidden" name="action" value="register">    
                    
                    <div class="form-group">
                        <label for="register_email">Email Address <span class="required">*</span></label>
                        <input type="email" id="register_email" name="email" required 
                               value="<?php echo isset($_POST['email']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="register_password">Password <span class="required">*</span></label>
                            <div class="password-field">
                                <input type="password" id="register_password" name="password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('register_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <div class="password-field">
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Delivery Address</label>
                        <textarea id="address" name="address" placeholder="Enter your delivery address"><?php echo isset($_POST['address']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-auth">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
                
                <div class="form-switch">
                    <p>Already have an account?</p>
                    <button class="switch-btn" onclick="switchForm('login')">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </button>
                </div>
            </div>
            
            <!-- Login Form -->
            <div id="loginForm" class="form-content <?php echo $active_form === 'login' ? 'active' : ''; ?>">
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="login_email">Email Address</label>
                        <input type="email" id="login_email" name="email" required 
                               value="<?php echo isset($_POST['email']) && $_POST['action'] === 'login' ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="login_password">Password</label>
                        <div class="password-field">
                            <input type="password" id="login_password" name="password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('login_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-auth">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                
                <div class="form-switch">
                    <p>Don't have an account?</p>
                    <button class="switch-btn" onclick="switchForm('register')">
                        <i class="fas fa-user-plus"></i> Create New Account
                    </button>
                </div>
            </div>
                    <p class="liw">Log in with</p>

                     <div class="icons">
                        <a href="#"><ion-icon name="logo-facebook"></ion-icon></a>
                        <a href="#"><ion-icon name="logo-instagram"></ion-icon></a>
                        <a href="#"><ion-icon name="logo-twitter"></ion-icon></a>
                        <a href="#"><ion-icon name="logo-google"></ion-icon></a>
                        <a href="#"><ion-icon name="logo-github"></ion-icon></a>
                    </div>

            <div class="back-link">
                <a href="index.php">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>        
        function switchForm(formType) {
            // Update tab buttons
            document.querySelectorAll('.form-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Find the correct tab button and activate it
            const tabButtons = document.querySelectorAll('.form-tab');
            if (formType === 'login') {
                tabButtons[1].classList.add('active');
            } else {
                tabButtons[0].classList.add('active');
            }
            
            // Update form content
            document.querySelectorAll('.form-content').forEach(content => {
                content.classList.remove('active');
            });
            
            if (formType === 'login') {
                document.getElementById('loginForm').classList.add('active');
            } else {
                document.getElementById('registerForm').classList.add('active');
            }
        }
        
        // Auto-switch to register form if there's a register error
        <?php if ($active_form === 'register'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            switchForm('register');
        });
        <?php endif; ?>
        
        // Auto-switch to login form if there's a login error
        <?php if ($active_form === 'login'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            switchForm('login');
        });
        <?php endif; ?>
        
        // Password toggle functionality
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.parentElement.querySelector('.password-toggle');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    <script src="https://unpkg.com/ionicons@5.4.0/dist/ionicons.js"></script>
</body>
</html>