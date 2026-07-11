<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'অফ ডে ব্যবস্থাপনা';

// Delete off day with CSRF protection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM off_days WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $_SESSION['success'] = 'অফ ডে মুছে ফেলা হয়েছে!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'ত্রুটি: ' . $e->getMessage();
    }
    header('Location: off_days.php');
    exit();
}

// Get all off days
$stmt = $conn->query("SELECT * FROM off_days ORDER BY date DESC");
$off_days = $stmt->fetchAll();
?>
<?php include '../../includes/header.php'; ?>

<style>
.offdays-container {
    max-width: 1024px;
    margin: 0 auto;
    padding: 20px 16px;
}

.offdays-card {
    background: white;
    border-radius: 16px;
    padding: 24px 28px;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    border: 1px solid #e2e8f0;
}

.offdays-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.offdays-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.offdays-header h2 i {
    color: #ef4444;
}

.btn-back {
    background: #4f46e5;
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back:hover {
    background: #3730a3;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
}

.btn-back-home {
    background: #4f46e5;
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back-home:hover {
    background: #3730a3;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
}

.alert-custom {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #f0fdf4;
    border-left: 4px solid #22c55e;
    color: #166534;
}

.alert-danger {
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.table-container {
    overflow-x: auto;
}

.table-custom {
    width: 100%;
    border-collapse: collapse;
}

.table-custom thead {
    background: #f1f5f9;
}

.table-custom th {
    padding: 12px 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    border-bottom: 2px solid #e2e8f0;
}

.table-custom td {
    padding: 12px 16px;
    font-size: 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-custom tr:hover td {
    background: #f8fafc;
}

.status-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: #fee2e2;
    color: #991b1b;
}

.btn-delete {
    color: #ef4444;
    background: none;
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 14px;
}

.btn-delete:hover {
    background: #fef2f2;
    color: #dc2626;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
}

.empty-state i {
    font-size: 48px;
    display: block;
    margin-bottom: 12px;
    color: #cbd5e1;
}

.back-home-wrapper {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

@media (max-width: 640px) {
    .offdays-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-back, .btn-back-home {
        justify-content: center;
    }
    
    .table-custom th,
    .table-custom td {
        padding: 8px 10px;
        font-size: 13px;
    }
}
</style>

<div class="offdays-container">
    <div class="offdays-card">
        <div class="offdays-header">
            <h2>
                <i class="fas fa-calendar-times"></i> অফ ডে তালিকা
            </h2>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> স্ক্যানারে ফিরে যান
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-custom alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-custom alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <?php if (count($off_days) > 0): ?>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>তারিখ</th>
                            <th>কারণ</th>
                            <th>স্ট্যাটাস</th>
                            <th>অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($off_days as $index => $row): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo date('d F Y', strtotime($row['date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['reason'] ?: '—'); ?></td>
                            <td><span class="status-badge">🔴 অফ ডে</span></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" onclick="return confirm('মুছে ফেলতে চান?')" 
                                            class="btn-delete" title="মুছুন">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-plus"></i>
                    <p>কোনো অফ ডে নেই</p>
                    <small>উপরে ফিরে গিয়ে অফ ডে মার্ক করুন</small>
                </div>
            <?php endif; ?>
        </div>

        <!-- বেক টু হোম -->
        <div class="back-home-wrapper">
            <a href="../home.php" class="btn-back-home">
                <i class="fas fa-home"></i> ড্যাশবোর্ডে ফিরে যান
            </a>
            <a href="index.php" class="btn-back-home" style="background:#22c55e;">
                <i class="fas fa-qrcode"></i> স্ক্যানারে ফিরে যান
            </a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>