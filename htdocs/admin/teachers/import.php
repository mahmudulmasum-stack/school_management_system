<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'শিক্ষক ইমপোর্ট';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle);
        $success_count = 0;
        $error_count = 0;
        
        $conn->beginTransaction();
        
        try {
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) < 4) {
                    $error_count++;
                    continue;
                }
                
                $name = trim($data[0] ?? '');
                $number = trim($data[1] ?? '');
                $card_id = trim($data[2] ?? '');
                $subject = trim($data[3] ?? '');
                $designation = trim($data[4] ?? '');
                $blood_group = trim($data[5] ?? '');
                $location = trim($data[6] ?? '');
                
                if (empty($name) || empty($number) || empty($card_id)) {
                    $error_count++;
                    continue;
                }
                
                $stmt = $conn->prepare("SELECT id FROM teachers WHERE card_id = :card_id");
                $stmt->execute(['card_id' => $card_id]);
                if ($stmt->fetch()) {
                    $error_count++;
                    continue;
                }
                
                $sql = "INSERT INTO teachers (name, number, card_id, subject, designation, blood_group, location) 
                        VALUES (:name, :number, :card_id, :subject, :designation, :blood_group, :location)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $name, ':number' => $number, ':card_id' => $card_id,
                    ':subject' => $subject, ':designation' => $designation,
                    ':blood_group' => $blood_group, ':location' => $location
                ]);
                
                $success_count++;
            }
            
            $conn->commit();
            $_SESSION['success'] = "$success_count জন শিক্ষক ইমপোর্ট করা হয়েছে! ($error_count জন ব্যর্থ)";
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
    color: #22c55e;
}

.info-box {
    background: #f0fdf4;
    border-left: 4px solid #22c55e;
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 24px;
}

.info-box h4 {
    font-size: 15px;
    font-weight: 700;
    color: #166534;
    margin: 0 0 8px 0;
}

.info-box p {
    font-size: 14px;
    color: #166534;
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
    border-color: #22c55e;
    background: #f1f5f9;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    background: #22c55e;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit:hover {
    background: #16a34a;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
}

.download-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #22c55e;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 16px;
}

.download-link:hover {
    color: #16a34a;
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
            <i class="fas fa-file-import"></i> শিক্ষক ইমপোর্ট
        </h2>

        <div class="info-box">
            <h4>📄 CSV ফর্ম্যাট:</h4>
            <p>নাম, মোবাইল, কার্ড আইডি, বিষয়, পদবী, রক্তের গ্রুপ, ঠিকানা</p>
            <p class="highlight">* নাম, মোবাইল, কার্ড আইডি অবশ্যই দিতে হবে</p>
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
    const headers = ['নাম', 'মোবাইল', 'কার্ড আইডি', 'বিষয়', 'পদবী', 'রক্তের গ্রুপ', 'ঠিকানা'];
    const sample = ['মোঃ কামাল হোসেন', '01711111111', 'TCH001', 'গণিত', 'সহকারী শিক্ষক', 'O+', 'ঢাকা'];
    
    let csv = headers.join(',') + '\n' + sample.join(',') + '\n';
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'sample_teachers.csv';
    link.click();
}
</script>

<?php include '../../includes/footer.php'; ?>