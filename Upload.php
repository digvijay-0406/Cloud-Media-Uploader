<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/debug.txt');

error_log("Upload script started");

require 'vendor/autoload.php';
use Aws\S3\S3Client;

// AWS S3 configuration
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'ap-south-1',
    'credentials' => [
        'key'    => 'Access key',
        'secret' => 'Secret Key'
    ]
]);

// Database configuration
$servername = "image-uploader-db.cjcoymc2o8xw.ap-south-1.rds.amazonaws.com";
$username = "root";
$password = "password of database";
$dbname = "facebook";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        error_log("File received.");

        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            error_log("Invalid file format.");
            die("<div style='color:red;'>Error: Please select a valid file format.</div>");
        }

        $maxsize = 10 * 1024 * 1024;
        if ($filesize > $maxsize) {
            error_log("File too large.");
            die("<div style='color:red;'>Error: File size is larger than the allowed limit.</div>");
        }

        if (in_array($filetype, $allowed)) {
            $uploadDir = "uploads/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filepath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $filepath)) {
                error_log("File moved to uploads folder: $filepath");

                try {
                    // Upload to S3
                    error_log("Uploading to S3...");
                    $result = $s3Client->putObject([
                        'Bucket' => 'phpwala',
                        'Key'    => basename($filepath),
                        'Body'   => fopen($filepath, 'r'),
                    ]);
                    $urls3 = $result->get('ObjectURL');
                    $cfurl = str_replace("https://phpwala.s3.ap-south-1.amazonaws.com", "https://d12mcfckxsbztf
.cloudfront.net", $urls3);
                    error_log("Uploaded to S3: $urls3");

                    // Get name from input
                    $name = isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : "Anonymous";

                    // Insert into RDS
                    error_log("Connecting to RDS...");
                    $conn = mysqli_connect($servername, $username, $password, $dbname);
                    if (!$conn) {
                        error_log("RDS connection failed: " . mysqli_connect_error());
                        die("<div style='color:red;'>Database connection failed: " . mysqli_connect_error() . "
</div>");
                    }

                    $sql = "INSERT INTO posts (name, s3url, cdnurl) VALUES ('$name', '$urls3', '$cfurl')";
                    if (mysqli_query($conn, $sql)) {
                        error_log("Record inserted into RDS.");
                        $insertMsg = "Record saved to database.";
                    } else {
                        error_log("Database error: " . mysqli_error($conn));
                        $insertMsg = "<div style='color:red;'>Database error: " . mysqli_error($conn) . "</div>
";
                    }
                    mysqli_close($conn);

                    // Display success
                    echo "<div style='max-width:600px;margin:50px auto;padding:20px;border-radius:10px;backgrou
nd:#f0fff0;font-family:sans-serif;text-align:center;box-shadow:0 0 10px rgba(0,0,0,0.1);'>";
                    echo "<h2 style='color:#4CAF50;'>Image Uploaded Successfully!</h2>";
                    echo "<p><strong>S3 URL:</strong> <a href='$urls3' target='_blank'>$urls3</a></p>";
                    echo "<p><strong>CloudFront URL:</strong> <a href='$cfurl' target='_blank'>$cfurl</a></p>";
                    echo "<img src='$cfurl' alt='Uploaded Image' style='max-width:100%;margin-top:20px;border-r
adius:10px;'/>";
                    echo "<p style='margin-top:20px;color:green;'>$insertMsg</p>";
                    echo "</div>";
                     } catch (Aws\S3\Exception\S3Exception $e) {
                    error_log("S3 Upload Failed: " . $e->getMessage());
                    echo "<div style='color:red;'>There was an error uploading the file to S3: " . $e->getMessa
ge() . "</div>";
                }

            } else {
                error_log("Failed to move file to uploads folder.");
                echo "<div style='color:red;'>Error: File could not be uploaded locally.</div>";
            }

        } else {
            error_log("Invalid file type after validation.");
            echo "<div style='color:red;'>Invalid file type.</div>";
        }
    } else {
        error_log("No file uploaded or file error: " . $_FILES["image"]["error"]);
        echo "<div style='color:red;'>Error: " . $_FILES["image"]["error"] . "</div>";
    }
}
?>
