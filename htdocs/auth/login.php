<?php
require_once '../config/db.php';

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'ইউজারনেম এবং পাসওয়ার্ড দিন';
    } else {
        // Check database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: home.php');
            exit();
        } else {
            $error = 'ইউজারনেম বা পাসওয়ার্ড ভুল!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Hind Siliguri', sans-serif; }
        body { 
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px;
            margin: 0;
        }
        .login-card { 
            background: white; 
            border-radius: 24px; 
            padding: 40px; 
            max-width: 420px; 
            width: 100%; 
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .fade-in-up {
            animation: fadeInUp 0.5s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-card .school-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 4px;
        }
        .login-card .school-name {
            font-size: 22px;
            font-weight: 700;
            color: #1e1b4b;
            margin: 0;
        }
        .login-card .school-slogan {
            font-size: 13px;
            color: #6b7280;
            margin: 2px 0 16px 0;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
            background: #f9fafb;
        }
        .form-control:focus {
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }
        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        .register-link {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
            color: #6b7280;
        }
        .register-link a {
            color: #4f46e5;
            font-weight: 600;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .default-hint {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 8px;
        }
        
        /* ==========================================
                   ফুটার স্টাইল
                   ========================================== */
        .login-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .login-footer .developer {
            font-size: 14px;
            color: #4b5563;
        }
        .login-footer .developer .name {
            color: #4f46e5;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .login-footer .developer .name:hover {
            color: #3730a3;
            text-decoration: underline;
        }
        .login-footer .social-links {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 8px;
        }
        .login-footer .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 18px;
        }
        .login-footer .social-links .facebook {
            background: #1877f2;
        }
        .login-footer .social-links .facebook:hover {
            background: #0d65d9;
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(24, 119, 242, 0.4);
        }
        .login-footer .social-links .whatsapp {
            background: #25d366;
        }
        .login-footer .social-links .whatsapp:hover {
            background: #1da851;
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
        }
        .login-footer .copyright {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card fade-in-up">
        <!-- স্কুল তথ্য -->
        <div class="text-center">
            <span class="school-icon">🏫</span>
            <h2 class="school-name"><?php echo SITE_NAME; ?></h2>
            <p class="school-slogan"><?php echo SITE_SLOGAN; ?></p>
        </div>
        
        <!-- Error Message -->
        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">ইউজারনেম</label>
                <input type="text" name="username" 
                       class="form-control" 
                       placeholder="ইউজারনেম লিখুন" required>
            </div>
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">পাসওয়ার্ড</label>
                <input type="password" name="password" 
                       class="form-control" 
                       placeholder="পাসওয়ার্ড লিখুন" required>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i> লগইন করুন
            </button>
        </form>
        
        <!-- Register Link -->
        <div class="register-link">
            নতুন? <a href="register.php">অ্যাকাউন্ট তৈরি করুন</a>
        </div>
        <div class="default-hint">
            ডিফল্ট: admin / password
        </div>

        <!-- ==========================================
             ফুটার - ডেভেলপার তথ্য
             ========================================== -->
        <div class="login-footer">
            <div class="developer">
                Developed by 
                <a href="https://www.facebook.com/profile.php?id=61577449474917" 
                   target="_blank" 
                   class="name">
                    Mahmudul Hasan Masum
                </a>
            </div>
            <div class="social-links">
                <a href="https://www.facebook.com/profile.php?id=61577449474917" 
                   target="_blank" 
                   class="facebook" 
                   title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://wa.me/8801728282737" 
                   target="_blank" 
                   class="whatsapp" 
                   title="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - সর্বস্বত্ব সংরক্ষিত
            </div>
        </div>
    </div>
</body>
</html>