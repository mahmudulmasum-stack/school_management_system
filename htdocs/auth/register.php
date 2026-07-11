<?php
require_once '../config/db.php';

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    
    // Validation
    if (empty($username)) $errors[] = 'ইউজারনেম দিন';
    if (strlen($username) < 3) $errors[] = 'ইউজারনেম কমপক্ষে ৩ অক্ষরের হতে হবে';
    if (empty($full_name)) $errors[] = 'পূর্ণ নাম দিন';
    if (empty($password)) $errors[] = 'পাসওয়ার্ড দিন';
    if (strlen($password) < 6) $errors[] = 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে';
    if ($password !== $confirm_password) $errors[] = 'পাসওয়ার্ড মিলছে না';
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'সঠিক ইমেইল দিন';
    
    // Check if username exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $errors[] = 'এই ইউজারনেম ইতিমধ্যে ব্যবহার করা হয়েছে';
        }
    }
    
    // Check if email exists
    if (!empty($email) && empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'এই ইমেইল ইতিমধ্যে ব্যবহার করা হয়েছে';
        }
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password, role, full_name, email) 
                VALUES (:username, :password, :role, :full_name, :email)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':role' => $role,
            ':full_name' => $full_name,
            ':email' => $email
        ]);
        
        $success = 'রেজিস্ট্রেশন সফল! এখন লগইন করুন।';
        
        // Redirect to login after 2 seconds
        header("refresh:2;url=login.php");
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রেজিস্ট্রেশন - <?php echo SITE_NAME; ?></title>
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
        }
        .register-card { 
            background: white; 
            border-radius: 24px; 
            padding: 40px; 
            max-width: 500px; 
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
    </style>
</head>
<body>
    <div class="register-card fade-in-up">
        <div class="text-center mb-6">
            <div class="text-4xl mb-2">🏫</div>
            <h2 class="text-2xl font-bold text-gray-800">নতুন অ্যাকাউন্ট</h2>
            <p class="text-gray-500 text-sm"><?php echo SITE_NAME; ?> এ রেজিস্ট্রেশন করুন</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4 text-sm">
                <?php foreach ($errors as $e): ?>
                    <div><i class="fas fa-times-circle mr-2"></i> <?php echo $e; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4 text-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">ইউজারনেম <span class="text-red-500">*</span></label>
                <input type="text" name="username" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" 
                       placeholder="ইউজারনেম (৩+ অক্ষর)" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">পূর্ণ নাম <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" 
                       placeholder="আপনার পূর্ণ নাম" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">ইমেইল</label>
                <input type="email" name="email" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" 
                       placeholder="ইমেইল ঠিকানা" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">পাসওয়ার্ড <span class="text-red-500">*</span></label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" 
                       placeholder="পাসওয়ার্ড (৬+ অক্ষর)">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">পাসওয়ার্ড নিশ্চিত করুন <span class="text-red-500">*</span></label>
                <input type="password" name="confirm_password" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" 
                       placeholder="আবার পাসওয়ার্ড লিখুন">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">ভূমিকা</label>
                <select name="role" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    <option value="staff" <?php echo ($_POST['role'] ?? '') == 'staff' ? 'selected' : ''; ?>>স্টাফ</option>
                    <option value="teacher" <?php echo ($_POST['role'] ?? '') == 'teacher' ? 'selected' : ''; ?>>শিক্ষক</option>
                    <option value="admin" <?php echo ($_POST['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>অ্যাডমিন</option>
                </select>
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition">
                <i class="fas fa-user-plus mr-2"></i> রেজিস্ট্রেশন করুন
            </button>
        </form>
        
        <p class="text-center text-sm text-gray-500 mt-4">
            ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium">লগইন করুন</a>
        </p>
        
        <p class="text-center text-xs text-gray-400 mt-2">
            <i class="fas fa-shield-alt mr-1"></i> আপনার তথ্য নিরাপদে রাখা হবে
        </p>
    </div>
</body>
</html>