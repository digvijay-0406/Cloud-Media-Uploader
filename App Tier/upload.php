<?php
require 'vendor/autoload.php';
use Aws\S3\S3Client;

// AWS S3 configuration
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'ap-south-1',
    'credentials' => [
        'key'    => 'access key',
        'secret' => 'secret keys'
    ]
]);

// Database configuration
$servername = "database-1.cjcoymc2o8xw.ap-south-1.rds.amazonaws.com";
$username = "root";
$password = "password of database";
$dbname = "facebook";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["anyfile"]) && $_FILES["anyfile"]["error"] == 0) {

        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["anyfile"]["name"];
        $filetype = $_FILES["anyfile"]["type"];
        $filesize = $_FILES["anyfile"]["size"];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            die("<div style='color:red;'>Error: Please select a valid file format.</div>");
        }

        $maxsize = 10 * 1024 * 1024;
        if ($filesize > $maxsize) {
            die("<div style='color:red;'>Error: File size is larger than the allowed limit.</div>");
        }

        if (in_array($filetype, $allowed)) {

            $uploadDir = "uploads/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filepath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES["anyfile"]["tmp_name"], $filepath)) {
                try {
                    $result = $s3Client->putObject([
                        'Bucket' => 'phpwala',
                        'Key'    => basename($filepath),
                        'Body'   => fopen($filepath, 'r'),
                        // 'ACL' => 'public-read', // avoid ACL error if Object Ownership is enforced
                    ]);

                    $urls3 = $result->get('ObjectURL');
                    $cfurl = str_replace("https://phpwala.s3.ap-south-1.amazonaws.com", "https://d1mmj85wrpmmu2.cloudfront.net", $urls3);
                    $name = htmlspecialchars($_POST["name"]);

                    // Connect to RDS
                    $conn = mysqli_connect($servername, $username, $password, $dbname);
                    if (!$conn) {
                        die("<div style='color:red;'>Database connection failed: " . mysqli_connect_error() . "</div>");
                    }

                    $sql = "INSERT INTO posts (name, s3url, cdnurl) VALUES ('$name', '$urls3', '$cfurl')";
                    $insertMsg = mysqli_query($conn, $sql) ? "Record saved to database." : "<div style='color:red;'>Database error: " . mysqli_error($conn) . "</div>";
                    mysqli_close($conn);

                    echo "<div style='max-width:600px;margin:50px auto;padding:20px;border-radius:10px;background:#f0fff0;font-family:sans-serif;text-align:center;box-shadow:0 0 10px rgba(0,0,0,0.1);'>";
                    echo "<h2 style='color:#4CAF50;'>Image Uploaded Successfully!</h2>";
                    echo "<p><strong>S3 URL:</strong> <a href='$urls3' target='_blank'>$urls3</a></p>";
                    echo "<p><strong>CloudFront URL:</strong> <a href='$cfurl' target='_blank'>$cfurl</a></p>";
                    echo "<img src='$cfurl' alt='Uploaded Image' style='max-width:100%;margin-top:20px;border-radius:10px;'/>";
                    echo "<p style='margin-top:20px;color:green;'>$insertMsg</p>";
                    echo "</div>";

                } catch (Aws\S3\Exception\S3Exception $e) {
                    echo "<div style='color:red;'>There was an error uploading the file: " . $e->getMessage() . "</div>";
                }
            } else {
                echo "<div style='color:red;'>Error: File could not be uploaded.</div>";
            }
        } else {
            echo "<div style='color:red;'>Invalid file type.</div>";
        }
    } else {
        echo "<div style='color:red;'>Error: " . $_FILES["anyfile"]["error"] . "</div>";
    }
}
?>
