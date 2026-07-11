<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'শিক্ষক সম্পাদনা';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = :id");
$stmt->execute(['id' => $id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    $_SESSION['error'] = 'শিক্ষক পাওয়া যায়নি';
    header('Location: list.php');
    exit();
}

$subjects = ['বাংলা','ইংরেজি','গণিত','বিজ্ঞান','সামাজিক বিজ্ঞান','ইসলাম শিক্ষা','শারীরিক শিক্ষা','কম্পিউটার শিক্ষা','চারু ও কারুকলা','সংগীত','কৃষি শিক্ষা','হোম ইকোনমিক্স'];
$designations = ['প্রধান শিক্ষক','সহকারী প্রধান শিক্ষক','সিনিয়র শিক্ষক','সহকারী শিক্ষক','প্রদর্শক','অফিস সহকারী','ল্যাব সহকারী','গ্রন্থাগারিক'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $number = trim($_POST['number'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $card_id = trim($_POST['card_id'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    
    if (empty($name)) $errors[] = 'নাম দিন';
    if (empty($number)) $errors[] = 'মোবাইল নম্বর দিন';
    if (empty($card_id)) $errors[] = 'কার্ড আইডি দিন';
    
    if (!empty($card_id)) {
        $stmt = $conn->prepare("SELECT id FROM teachers WHERE card_id = :card_id AND id != :id");
        $stmt->execute(['card_id' => $card_id, 'id' => $id]);
        if ($stmt->fetch()) $errors[] = 'এই কার্ড আইডি অন্য শিক্ষক ব্যবহার করছে';
    }
    
    $photo_path = $teacher['photo_path'];
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
            $sql = "UPDATE teachers SET name=:name, number=:number, blood_group=:blood_group, 
                    location=:location, card_id=:card_id, photo_path=:photo_path, 
                    subject=:subject, designation=:designation, status=:status WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':name' => $name, ':number' => $number, ':blood_group' => $blood_group,
                ':location' => $location, ':card_id' => $card_id, ':photo_path' => $photo_path,
                ':subject' => $subject, ':designation' => $designation, ':status' => $status,
                ':id' => $id
            ]);
            $_SESSION['success'] = 'শিক্ষক আপডেট করা হয়েছে!';
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
            <i class="fas fa-edit"></i> শিক্ষক সম্পাদনা
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
                <img id="preview" src="../../<?php echo htmlspecialchars($teacher['photo_path'] ?? 'uploads/default.jpg'); ?>" 
                     class="preview" onclick="document.getElementById('photoInput').click()"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($teacher['name']); ?>&background=eab308&color=fff&size=128'">
                <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden" onchange="previewImage(this)">
                <p class="hint">ছবি পরিবর্তন করতে ক্লিক করুন</p>
            </div>

            <div class="form-grid">
                <div class="full-width">
                    <div class="form-group">
                        <label>নাম <span class="required">*</span></label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($teacher['name']); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>মোবাইল <span class="required">*</span></label>
                        <input type="text" name="number" required value="<?php echo htmlspecialchars($teacher['number']); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>কার্ড আইডি <span class="required">*</span></label>
                        <input type="text" name="card_id" required value="<?php echo htmlspecialchars($teacher['card_id']); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>বিষয়</label>
                        <select name="subject">
                            <option value="">নির্বাচন করুন</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $teacher['subject'] == $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>পদবী</label>
                        <select name="designation">
                            <option value="">নির্বাচন করুন</option>
                            <?php foreach ($designations as $d): ?>
                                <option value="<?php echo $d; ?>" <?php echo $teacher['designation'] == $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>রক্তের গ্রুপ</label>
                        <select name="blood_group">
                            <option value="">নির্বাচন করুন</option>
                            <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                                <option value="<?php echo $bg; ?>" <?php echo $teacher['blood_group'] == $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="full-width">
                    <div class="form-group">
                        <label>ঠিকানা</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($teacher['location']); ?>">
                    </div>
                </div>

                <div class="full-width">
                    <div class="form-group">
                        <label>স্ট্যাটাস</label>
                        <select name="status">
                            <option value="active" <?php echo $teacher['status'] == 'active' ? 'selected' : ''; ?>>সক্রিয়</option>
                            <option value="inactive" <?php echo $teacher['status'] == 'inactive' ? 'selected' : ''; ?>>নিষ্ক্রিয়</option>
                            <option value="resigned" <?php echo $teacher['status'] == 'resigned' ? 'selected' : ''; ?>>অবসরপ্রাপ্ত</option>
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