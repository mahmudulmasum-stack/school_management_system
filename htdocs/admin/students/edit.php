<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'ছাত্র সম্পাদনা';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute(['id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    $_SESSION['error'] = 'ছাত্র পাওয়া যায়নি';
    header('Location: list.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $roll = trim($_POST['roll'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $group = trim($_POST['group'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $card_id = trim($_POST['card_id'] ?? '');
    $guardian_phone = trim($_POST['guardian_phone'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    
    if (empty($name)) $errors[] = 'নাম দিন';
    if (empty($roll)) $errors[] = 'রোল দিন';
    if (empty($class)) $errors[] = 'শ্রেণী দিন';
    if (empty($card_id)) $errors[] = 'কার্ড আইডি দিন';
    
    // Check duplicate
    if (!empty($roll)) {
        $stmt = $conn->prepare("SELECT id FROM students WHERE roll = :roll AND id != :id");
        $stmt->execute(['roll' => $roll, 'id' => $id]);
        if ($stmt->fetch()) $errors[] = 'এই রোল অন্য ছাত্র ব্যবহার করছে';
    }
    if (!empty($card_id)) {
        $stmt = $conn->prepare("SELECT id FROM students WHERE card_id = :card_id AND id != :id");
        $stmt->execute(['card_id' => $card_id, 'id' => $id]);
        if ($stmt->fetch()) $errors[] = 'এই কার্ড আইডি অন্য ছাত্র ব্যবহার করছে';
    }
    
    $photo_path = $student['photo_path'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = '../../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_name = uniqid() . '_' . basename($_FILES['photo']['name']);
        $target = $upload_dir . $file_name;
        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp']) && move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            if ($photo_path != 'uploads/default.jpg' && file_exists('../../' . $photo_path)) {
                @unlink('../../' . $photo_path);
            }
            $photo_path = 'uploads/' . $file_name;
        }
    }
    
    if (empty($errors)) {
        try {
            $sql = "UPDATE students SET name=:name, father_name=:father_name, mother_name=:mother_name, 
                    address=:address, roll=:roll, class=:class, group_name=:group, 
                    blood_group=:blood_group, card_id=:card_id, photo_path=:photo_path, 
                    guardian_phone=:guardian_phone, status=:status WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':name' => $name, ':father_name' => $father_name, ':mother_name' => $mother_name,
                ':address' => $address, ':roll' => $roll, ':class' => $class,
                ':group' => $group, ':blood_group' => $blood_group, ':card_id' => $card_id,
                ':photo_path' => $photo_path, ':guardian_phone' => $guardian_phone,
                ':status' => $status, ':id' => $id
            ]);
            $_SESSION['success'] = 'ছাত্র আপডেট করা হয়েছে!';
            header('Location: list.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = 'ডেটাবেস ত্রুটি: ' . $e->getMessage();
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>

<style>
.form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px 16px;
}

.form-card {
    background: white;
    border-radius: 16px;
    padding: 28px 32px;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    border: 1px solid #e2e8f0;
}

.form-card h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-card h2 i {
    color: #eab308;
}

.photo-upload {
    text-align: center;
    margin-bottom: 20px;
}

.photo-upload .preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px dashed #e2e8f0;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 0 auto;
}

.photo-upload .preview:hover {
    border-color: #eab308;
}

.photo-upload .hint {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 8px;
}

.alert-custom {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-danger {
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-grid .full-width {
    grid-column: span 2;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 4px;
}

.form-group label .required {
    color: #ef4444;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #eab308;
    background: white;
    box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.1);
}

.btn-row {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.btn-submit {
    flex: 1;
    padding: 14px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    background: #eab308;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit:hover {
    background: #ca8a04;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(234, 179, 8, 0.3);
}

.btn-cancel {
    flex: 1;
    padding: 14px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    background: #e2e8f0;
    color: #334155;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #cbd5e1;
}

@media (max-width: 640px) {
    .form-card {
        padding: 20px 16px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid .full-width {
        grid-column: span 1;
    }
    
    .btn-row {
        flex-direction: column;
    }
}
</style>

<div class="form-container">
    <div class="form-card">
        <h2>
            <i class="fas fa-edit"></i> ছাত্র সম্পাদনা
        </h2>

        <?php if (!empty($errors)): ?>
            <div class="alert-custom alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo $e; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- Photo Upload -->
            <div class="photo-upload">
                <img id="preview" src="../../<?php echo htmlspecialchars($student['photo_path'] ?? 'uploads/default.jpg'); ?>" 
                     class="preview" onclick="document.getElementById('photoInput').click()"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($student['name']); ?>&background=eab308&color=fff&size=128'">
                <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden" onchange="previewImage(this)">
                <p class="hint">ছবি পরিবর্তন করতে ক্লিক করুন</p>
            </div>

            <div class="form-grid">
                <div class="full-width">
                    <div class="form-group">
                        <label>নাম <span class="required">*</span></label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($student['name']); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>পিতার নাম</label>
                        <input type="text" name="father_name" value="<?php echo htmlspecialchars($student['father_name']); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>মাতার নাম</label>
                        <input type="text" name="mother_name" value="<?php echo htmlspecialchars($student['mother_name']); ?>">
                    </div>
                </div>

                <div class="full-width">
                    <div class="form-group">
                        <label>ঠিকানা</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($student['address']); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>রোল <span class="required">*</span></label>
                        <input type="text" name="roll" required value="<?php echo htmlspecialchars($student['roll']); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>শ্রেণী <span class="required">*</span></label>
                        <select name="class" required>
                            <?php for($i=1; $i<=12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $student['class'] == $i ? 'selected' : ''; ?>>শ্রেণী <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>বিভাগ</label>
                        <select name="group">
                            <option value="">নির্বাচন করুন</option>
                            <option value="Science" <?php echo $student['group_name'] == 'Science' ? 'selected' : ''; ?>>বিজ্ঞান</option>
                            <option value="Arts" <?php echo $student['group_name'] == 'Arts' ? 'selected' : ''; ?>>মানবিক</option>
                            <option value="Commerce" <?php echo $student['group_name'] == 'Commerce' ? 'selected' : ''; ?>>ব্যবসায় শিক্ষা</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>রক্তের গ্রুপ</label>
                        <select name="blood_group">
                            <option value="">নির্বাচন করুন</option>
                            <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                                <option value="<?php echo $bg; ?>" <?php echo $student['blood_group'] == $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>কার্ড আইডি <span class="required">*</span></label>
                        <input type="text" name="card_id" required value="<?php echo htmlspecialchars($student['card_id']); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>অভিভাবকের মোবাইল</label>
                        <input type="text" name="guardian_phone" value="<?php echo htmlspecialchars($student['guardian_phone']); ?>">
                    </div>
                </div>

                <div class="full-width">
                    <div class="form-group">
                        <label>স্ট্যাটাস</label>
                        <select name="status">
                            <option value="active" <?php echo $student['status'] == 'active' ? 'selected' : ''; ?>>সক্রিয়</option>
                            <option value="inactive" <?php echo $student['status'] == 'inactive' ? 'selected' : ''; ?>>নিষ্ক্রিয়</option>
                            <option value="graduated" <?php echo $student['status'] == 'graduated' ? 'selected' : ''; ?>>পাশ</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> আপডেট করুন
                </button>
                <a href="list.php" class="btn-cancel">বাতিল করুন</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../../includes/footer.php'; ?>