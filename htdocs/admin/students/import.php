<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'ছাত্র ইমপোর্ট';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle);
        $success_count = 0;
        $error_count = 0;
        
        $conn->beginTransaction();
        
        try {
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) < 5) {
                    $error_count++;
                    continue;
                }
                
                $name = trim($data[0] ?? '');
                $roll = trim($data[1] ?? '');
                $class = trim($data[2] ?? '');
                $card_id = trim($data[3] ?? '');
                $father_name = trim($data[4] ?? '');
                $mother_name = trim($data[5] ?? '');
                $address = trim($data[6] ?? '');
                $group = trim($data[7] ?? '');
                $blood_group = trim($data[8] ?? '');
                $guardian_phone = trim($data[9] ?? '');
                
                if (empty($name) || empty($roll) || empty($class) || empty($card_id)) {
                    $error_count++;
                    continue;
                }
                
                // Check duplicates
                $stmt = $conn->prepare("SELECT id FROM students WHERE roll = :roll OR card_id = :card_id");
                $stmt->execute(['roll' => $roll, 'card_id' => $card_id]);
                if ($stmt->fetch()) {
                    $error_count++;
                    continue;
                }
                
                $sql = "INSERT INTO students (name, roll, class, card_id, father_name, mother_name, address, group_name, blood_group, guardian_phone) 
                        VALUES (:name, :roll, :class, :card_id, :father_name, :mother_name, :address, :group, :blood_group, :guardian_phone)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $name, ':roll' => $roll, ':class' => $class,
                    ':card_id' => $card_id, ':father_name' => $father_name,
                    ':mother_name' => $mother_name, ':address' => $address,
                    ':group' => $group, ':blood_group' => $blood_group,
                    ':guardian_phone' => $guardian_phone
                ]);
                
                $success_count++;
            }
            
            $conn->commit();
            $_SESSION['success'] = "$success_count জন ছাত্র ইমপোর্ট করা হয়েছে! ($error_count জন ব্যর্থ)";
        } catch (PDOException $e) {
            $conn->rollBack();
            $_SESSION['error'] = 'ইমপোর্ট ত্রুটি: ' . $e->getMessage();
        }
        
        fclose($handle);
    } else {
        $_SESSION['error'] = 'ফাইল খোলা যায়নি';
    }
    
    header('Location: list.php');
    exit();
}
?>
<?php include '../../includes/header.php'; ?>

<style>
.import-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 20px 16px;
}

.import-card {
    background: white;
    border-radius: 16px;
    padding: 28px 32px;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    border: 1px solid #e2e8f0;
}

.import-card h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.import-card h2 i {
    color: #4f46e5;
}

.info-box {
    background: #eff6ff;
    border-left: 4px solid #4f46e5;
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 24px;
}

.info-box h4 {
    font-size: 15px;
    font-weight: 700;
    color: #1e40af;
    margin: 0 0 8px 0;
}

.info-box p {
    font-size: 14px;
    color: #1e40af;
    margin: 4px 0;
}

.info-box .highlight {
    color: #dc2626;
    font-weight: 600;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 4px;
}

.form-group input[type="file"] {
    width: 100%;
    padding: 12px;
    border: 2px dashed #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-group input[type="file"]:hover {
    border-color: #4f46e5;
    background: #f1f5f9;
}

.btn-submit {
    width: 100%;
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

.download-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #4f46e5;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 16px;
}

.download-link:hover {
    color: #3730a3;
    text-decoration: underline;
}

.divider {
    border-top: 1px solid #e2e8f0;
    padding-top: 20px;
    margin-top: 20px;
}

@media (max-width: 640px) {
    .import-card {
        padding: 20px 16px;
    }
}
</style>

<div class="import-container">
    <div class="import-card">
        <h2>
            <i class="fas fa-file-import"></i> ছাত্র ইমপোর্ট
        </h2>

        <div class="info-box">
            <h4>📄 CSV ফর্ম্যাট:</h4>
            <p>নাম, রোল, শ্রেণী, কার্ড আইডি, পিতার নাম, মাতার নাম, ঠিকানা, বিভাগ, রক্তের গ্রুপ, অভিভাবকের মোবাইল</p>
            <p class="highlight">* নাম, রোল, শ্রেণী, কার্ড আইডি অবশ্যই দিতে হবে</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>CSV ফাইল নির্বাচন করুন</label>
                <input type="file" name="csv_file" accept=".csv" required>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-upload"></i> ইমপোর্ট করুন
            </button>
        </form>

        <div class="divider">
            <a href="#" onclick="downloadSample()" class="download-link">
                <i class="fas fa-download"></i> নমুনা CSV ফাইল ডাউনলোড করুন
            </a>
        </div>
    </div>
</div>

<script>
function downloadSample() {
    const headers = ['নাম', 'রোল', 'শ্রেণী', 'কার্ড আইডি', 'পিতার নাম', 'মাতার নাম', 'ঠিকানা', 'বিভাগ', 'রক্তের গ্রুপ', 'অভিভাবকের মোবাইল'];
    const sample = ['মোঃ রাকিব হাসান', '101', '10', 'STU0001', 'মোঃ আব্দুল করিম', 'ফাতেমা বেগম', 'ঢাকা', 'বিজ্ঞান', 'O+', '01712345678'];
    
    let csv = headers.join(',') + '\n' + sample.join(',') + '\n';
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'sample_students.csv';
    link.click();
}
</script>

<?php include '../../includes/footer.php'; ?>