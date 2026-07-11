<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'ছাত্র তালিকা';

$search = $_GET['search'] ?? '';
$class = $_GET['class'] ?? '';

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR card_id LIKE :search OR roll LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($class)) {
    $sql .= " AND class = :class";
    $params[':class'] = $class;
}

$sql .= " ORDER BY class, roll";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$students = $stmt->fetchAll();
?>
<?php include '../../includes/header.php'; ?>

<style>
.list-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px 16px;
}

.list-card {
    background: white;
    border-radius: 16px;
    padding: 24px 28px;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    border: 1px solid #e2e8f0;
}

.list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.list-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.list-header h2 i {
    color: #4f46e5;
}

.list-header .count {
    font-size: 14px;
    font-weight: 400;
    color: #64748b;
    margin-left: 8px;
}

.btn-add {
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    background: #4f46e5;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-add:hover {
    background: #3730a3;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
}

.btn-import {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    background: #22c55e;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-import:hover {
    background: #16a34a;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.filter-form input,
.filter-form select {
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
    background: #f8fafc;
    flex: 1;
    min-width: 160px;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: #4f46e5;
    background: white;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

.filter-form button {
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    background: #4f46e5;
    color: white;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.filter-form button:hover {
    background: #3730a3;
}

.filter-form .btn-reset {
    background: #e2e8f0;
    color: #334155;
}

.filter-form .btn-reset:hover {
    background: #cbd5e1;
}

.table-container {
    overflow-x: auto;
}

.table-custom {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.table-custom thead {
    background: #f1f5f9;
}

.table-custom th {
    padding: 10px 14px;
    text-align: left;
    font-weight: 600;
    color: #334155;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.table-custom td {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-custom tr:hover td {
    background: #f8fafc;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.status-badge {
    display: inline-block;
    padding: 2px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.status-graduated {
    background: #fef3c7;
    color: #92400e;
}

.btn-action {
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-edit {
    color: #eab308;
}

.btn-edit:hover {
    background: #fef3c7;
    color: #ca8a04;
}

.btn-delete {
    color: #ef4444;
}

.btn-delete:hover {
    background: #fef2f2;
    color: #dc2626;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #94a3b8;
}

.empty-state i {
    font-size: 56px;
    display: block;
    margin-bottom: 12px;
    color: #cbd5e1;
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

.action-cell {
    display: flex;
    gap: 6px;
}

@media (max-width: 768px) {
    .list-card {
        padding: 16px;
    }
    
    .list-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .list-header .actions {
        display: flex;
        gap: 10px;
    }
    
    .list-header .actions a {
        flex: 1;
        justify-content: center;
    }
    
    .filter-form input,
    .filter-form select {
        min-width: 120px;
    }
}

@media (max-width: 640px) {
    .filter-form {
        flex-direction: column;
    }
    
    .filter-form input,
    .filter-form select,
    .filter-form button {
        width: 100%;
    }
    
    .table-custom th,
    .table-custom td {
        padding: 8px 10px;
        font-size: 13px;
    }
}
</style>

<div class="list-container">
    <div class="list-card">
        <div class="list-header">
            <h2>
                <i class="fas fa-user-graduate"></i> ছাত্র তালিকা
                <span class="count">(মোট: <?php echo count($students); ?>)</span>
            </h2>
            <div class="actions" style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="import.php" class="btn-import">
                    <i class="fas fa-file-import"></i> ইমপোর্ট
                </a>
                <a href="add.php" class="btn-add">
                    <i class="fas fa-plus"></i> নতুন
                </a>
            </div>
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

        <!-- Filter -->
        <form method="GET" class="filter-form">
            <input type="text" name="search" placeholder="🔍 নাম, রোল বা কার্ড আইডি..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="class">
                <option value="">সব শ্রেণী</option>
                <?php for($i=1; $i<=12; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $class == $i ? 'selected' : ''; ?>>শ্রেণী <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">ফিল্টার</button>
            <a href="list.php" class="btn-reset" style="padding:10px 24px;border-radius:10px;font-weight:600;font-size:14px;border:none;cursor:pointer;background:#e2e8f0;color:#334155;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">রিসেট</a>
        </form>

        <!-- Table -->
        <?php if (count($students) > 0): ?>
        <div class="table-container">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ছবি</th>
                        <th>নাম</th>
                        <th>রোল</th>
                        <th>শ্রেণী</th>
                        <th>কার্ড</th>
                        <th>স্ট্যাটাস</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $index => $row): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <img src="../../<?php echo htmlspecialchars($row['photo_path'] ?? 'uploads/default.jpg'); ?>" 
                                 class="avatar" 
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['name']); ?>&background=4f46e5&color=fff&size=36'">
                        </td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['roll']); ?></td>
                        <td><?php echo htmlspecialchars($row['class']); ?></td>
                        <td><code><?php echo htmlspecialchars($row['card_id']); ?></code></td>
                        <td>
                            <?php if ($row['status'] == 'active'): ?>
                                <span class="status-badge status-active">✅ সক্রিয়</span>
                            <?php elseif ($row['status'] == 'inactive'): ?>
                                <span class="status-badge status-inactive">⛔ নিষ্ক্রিয়</span>
                            <?php else: ?>
                                <span class="status-badge status-graduated">🎓 পাশ</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-cell">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="সম্পাদনা">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('মুছে ফেলতে চান?')" 
                                   class="btn-action btn-delete" title="মুছুন">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>কোনো ছাত্র পাওয়া যায়নি</p>
            <a href="add.php" class="btn-add" style="display:inline-block;margin-top:12px;">নতুন ছাত্র যোগ করুন</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>