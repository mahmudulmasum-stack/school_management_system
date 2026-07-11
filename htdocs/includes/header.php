<?php
// This file is included at the top of every page
// Make sure db.php is included before this file
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Hind Siliguri', 'Segoe UI', sans-serif;
            box-sizing: border-box;
        }
        body {
            background: #f1f5f9;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .fade-in-up {
            animation: fadeInUp 0.5s ease;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        /* ============================================
           টপ নেভবার
           ============================================ */
        .top-nav {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .top-nav .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
        }
        .top-nav .brand:hover {
            color: white;
        }
        .top-nav .brand .icon {
            font-size: 28px;
        }
        .top-nav .brand .brand-name {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }
        .top-nav .brand .brand-sub {
            font-size: 11px;
            opacity: 0.7;
            display: block;
            font-weight: 400;
        }
        .top-nav .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        /* ইউজার ব্যাজ + লগআউট */
        .top-nav .user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px 4px 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.08);
            margin-left: 4px;
        }
        .top-nav .user-badge .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 14px;
        }
        .top-nav .user-badge .user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        .top-nav .user-badge .user-name {
            color: white;
            font-size: 13px;
            font-weight: 600;
        }
        .top-nav .user-badge .user-role {
            color: rgba(255,255,255,0.5);
            font-size: 10px;
        }
        .top-nav .user-badge .logout-link {
            color: rgba(255,255,255,0.5);
            padding: 4px 6px;
            border-radius: 6px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 16px;
        }
        .top-nav .user-badge .logout-link:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.2);
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 16px;
        }

        /* ============================================
           বেক টু হোম / ফিরে যান বাটন স্টাইল
           ============================================ */
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            background: #4f46e5;
            color: white;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-back-home:hover {
            background: #3730a3;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
        }
        .btn-back-home.green {
            background: #22c55e;
        }
        .btn-back-home.green:hover {
            background: #16a34a;
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
        }
        .btn-back-home.yellow {
            background: #eab308;
        }
        .btn-back-home.yellow:hover {
            background: #ca8a04;
            box-shadow: 0 4px 20px rgba(234, 179, 8, 0.3);
        }
        .btn-back-home.red {
            background: #ef4444;
        }
        .btn-back-home.red:hover {
            background: #dc2626;
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
        }

        .back-home-wrapper {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* মোবাইল রেসপন্সিভ */
        @media (max-width: 768px) {
            .top-nav {
                padding: 8px 12px;
            }
            .top-nav .brand .brand-name {
                font-size: 16px;
            }
            .top-nav .brand .brand-sub {
                font-size: 10px;
            }
            .top-nav .user-badge .user-info {
                display: none;
            }
            .btn-back-home {
                padding: 8px 16px;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .top-nav .brand .icon {
                font-size: 20px;
            }
            .top-nav .brand .brand-name {
                font-size: 14px;
            }
            .top-nav .user-badge {
                padding: 2px 8px 2px 6px;
            }
            .top-nav .user-badge .avatar {
                width: 24px;
                height: 24px;
                font-size: 11px;
            }
            .back-home-wrapper {
                flex-direction: column;
            }
            .btn-back-home {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- ==========================================
     টপ নেভবার
     ========================================== -->
<nav class="top-nav">
    <!-- ব্র্যান্ড / লোগো -->
    <a href="../admin/home.php" class="brand">
        <span class="icon">🏫</span>
        <div>
            <div class="brand-name"><?php echo defined('SITE_NAME') ? SITE_NAME : 'School'; ?></div>
            <span class="brand-sub"><?php echo defined('SITE_SLOGAN') ? SITE_SLOGAN : ''; ?></span>
        </div>
    </a>

    <!-- ইউজার ব্যাজ + লগআউট -->
    <div class="nav-links">
        <div class="user-badge">
            <div class="avatar">
                <?php 
                $fullName = function_exists('getFullName') ? getFullName() : 'User';
                echo strtoupper(substr($fullName, 0, 1)); 
                ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($fullName); ?></span>
                <span class="user-role"><?php echo function_exists('getRole') ? htmlspecialchars(getRole() ?? 'staff') : 'staff'; ?></span>
            </div>
            <a href="../admin/auth/logout.php" class="logout-link" title="লগআউট">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>

<div class="main-content">