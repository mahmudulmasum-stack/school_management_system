<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'নতুন ছাত্র যোগ';

// Auto generate roll and card ID
$stmt = $conn->query("SELECT MAX(roll) as max_roll FROM students");
$row = $stmt->fetch();
$auto_roll = $row['max_roll'] ? intval($row['max_roll']) + 1 : 1;
$auto_card_id = 'STU' . str_pad($auto_roll, 4, '0', STR_PAD_LEFT);

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
    
    if (empty($name)) $errors[] = 'নাম দিন';
    if (empty($roll)) $errors[] = 'রোল দিন';
    if (empty($class)) $errors[] = 'শ্রেণী দিন';
    if (empty($card_id)) $errors[] = 'কার্ড আইডি দিন';
    
    // Check duplicate
    if (!empty($roll)) {
        $stmt = $conn->prepare("SELECT id FROM students WHERE roll = :roll");
        $stmt->execute(['roll' => $roll]);
        if ($stmt->fetch()) $errors[] = 'এই রোল ইতিমধ্যে ব্যবহার করা হয়েছে';
    }
    if (!empty($card_id)) {
        $stmt = $conn->prepare("SELECT id FROM students WHERE card_id = :card_id");
        $stmt->execute(['card_id' => $card_id]);
        if ($stmt->fetch()) $errors[] = 'এই কার্ড আইডি ইতিমধ্যে ব্যবহার করা হয়েছে';
    }
    
    // Photo upload
    $photo_path = 'uploads/default.jpg';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = '../../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_name = uniqid() . '_' . basename($_FILES['photo']['name']);
        $target = $upload_dir . $file_name;
        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp']) && move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $photo_path = 'uploads/' . $file_name;
        }
    }
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO students (name, father_name, mother_name, address, roll, class, group_name, blood_group, card_id, photo_path, guardian_phone) 
                    VALUES (:name, :father_name, :mother_name, :address, :roll, :class, :group, :blood_group, :card_id, :photo_path, :guardian_phone)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':name' => $name, ':father_name' => $father_name, ':mother_name' => $mother_name,
                ':address' => $address, ':roll' => $roll, ':class' => $class,
                ':group' => $group, ':blood_group' => $blood_group, ':card_id' => $card_id,
                ':photo_path' => $photo_path, ':guardian_phone' => $guardian_phone
            ]);
            $_SESSION['success'] = 'ছাত্র সফলভাবে যোগ করা হয়েছে!';
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
    color: #4f46e5;
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
    border-color: #4f46e5;
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
    border-color: #4f46e5;
    background: white;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
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
    background: #4f46e5;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit:hover {
    background: #3730a3;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
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
            <i class="fas fa-user-plus"></i> নতুন ছাত্র যোগ
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
                <img id="preview" src="https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff&size=128" 
                     class="preview" onclick="document.getElementById('photoInput').click()">
                <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden" onchange="previewImage(this)">
                <p class="hint">ছবি আপলোড করতে ক্লিক করুন</p>
            </div>

            <div class="form-grid">
                <div class="full-width">
                    <div class="form-group">
                        <label>নাম <span class="required">*</span></label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>পিতার নাম</label>
                        <input type="text" name="father_name" value="<?php echo htmlspecialchars($_POST['father_name'] ?? ''); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>মাতার নাম</label>
                        <input type="text" name="mother_name" value="<?php echo htmlspecialchars($_POST['mother_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="full-width">
                    <div class="form-group">
                        <label>ঠিকানা</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>রোল <span class="required">*</span></label>
                        <input type="text" name="roll" required value="<?php echo htmlspecialchars($_POST['roll'] ?? $auto_roll); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>শ্রেণী <span class="required">*</span></label>
                        <select name="class" required id="classSelect">
                            <option value="">নির্বাচন করুন</option>
                            <?php for($i=1; $i<=12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($_POST['class'] ?? '') == $i ? 'selected' : ''; ?>>শ্রেণী <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div id="groupContainer" style="display: <?php echo (($_POST['class'] ?? 0) >= 9 && ($_POST['class'] ?? 0) <= 12) ? 'block' : 'none'; ?>">
                    <div class="form-group">
                        <label>বিভাগ</label>
                        <select name="group">
                            <option value="">নির্বাচন করুন</option>
                            <option value="Science" <?php echo ($_POST['group'] ?? '') == 'Science' ? 'selected' : ''; ?>>বিজ্ঞান</option>
                            <option value="Arts" <?php echo ($_POST['group'] ?? '') == 'Arts' ? 'selected' : ''; ?>>মানবিক</option>
                            <option value="Commerce" <?php echo ($_POST['group'] ?? '') == 'Commerce' ? 'selected' : ''; ?>>ব্যবসায় শিক্ষা</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>রক্তের গ্রুপ</label>
                        <select name="blood_group">
                            <option value="">নির্বাচন করুন</option>
                            <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                                <option value="<?php echo $bg; ?>" <?php echo ($_POST['blood_group'] ?? '') == $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>কার্ড আইডি <span class="required">*</span></label>
                        <input type="text" name="card_id" required value="<?php echo htmlspecialchars($_POST['card_id'] ?? $auto_card_id); ?>">
                    </div>
                </div>

                <div class="full-width">
                    <div class="form-group">
                        <label>অভিভাবকের মোবাইল</label>
                        <input type="text" name="guardian_phone" value="<?php echo htmlspecialchars($_POST['guardian_phone'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> ছাত্র যোগ করুন
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

document.getElementById('classSelect').addEventListener('change', function() {
    const val = parseInt(this.value);
    const container = document.getElementById('groupContainer');
    if (val >= 9 && val <= 12) {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
});
</script>

<?php include '../../includes/footer.php'; ?>