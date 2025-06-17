# Cloud Media Uploader

This project is a **3-tier web application** hosted on **AWS** that allows users to upload an image and some text. The image is stored in **Amazon S3**, the text data is saved in **Amazon RDS**, and the image is delivered via **CloudFront** for faster access.

---

## 🌐 Project Architecture

- **Web Tier**: `fileupload.html` – Frontend interface where the user uploads image and text.
- **App Tier**: `upload.php` – Handles file upload, S3 storage, and inserts metadata into RDS.
- **DB Tier**: `db.txt` – Contains RDS database connection credentials.
- **VPC**: Custom Virtual Private Cloud with public and private subnets.
- **Load Balancer**: Internet-facing ALB for web tier and internal ALB for app tier.
- **Auto Scaling**: EC2 instances scale automatically based on traffic.
- **AMIs**: Pre-configured Amazon Machine Images are used for launching identical EC2 instances.
- **VPC Endpoints**: Used for secure and private access to S3 and RDS without internet gateway/NAT.

---
## ☁️ AWS Services Used

| Service        | Purpose                                               |
|----------------|-------------------------------------------------------|
| **EC2**         | Hosts the web and app tiers                          |
| **S3**          | Stores uploaded images                               |
| **RDS (MySQL)** | Stores text data                                     |
| **IAM**         | Roles for EC2 to access S3 securely                  |
| **CloudFront**  | Delivers images globally with low latency            |
| **VPC/Subnets** | Isolated network structure with public/private zones |
| **ALB (Load Balancer)** | Distributes traffic to EC2 instances           |
| **Auto Scaling**| Automatically scales EC2 instances based on load     |
| **AMIs**        | Ensures consistent EC2 launches with pre-installed code |
| **VPC Endpoints** | Enables private access to AWS services like S3     |

---

## 🛠️ Features

- Upload an image with related text
- Store images in **Amazon S3**
- Store text in **Amazon RDS (MySQL)**
- Display the **CloudFront URL** for fast image access
- Works in a **3-tier architecture** with:
  - **Internet-facing ALB** for the frontend
  - **Internal ALB** for secure app backend
  - **Auto Scaling Groups** with custom **AMIs**
- Private S3 access via **VPC Endpoints**

---

## 🧑‍💻 How It Works

1. User opens `fileupload.html` and selects an image + enters some text.
2. On clicking **Upload**, it sends the request to `upload.php`.
3. `upload.php`:
   - Uploads the image to an **S3 bucket**
   - Inserts the text and S3 image link into the **RDS database**
4. The uploaded image is then served via **CloudFront URL**

---

## 🚀 How to Run

> **Step 1:** Launch EC2s using AMIs in your **Auto Scaling Group**  
> **Step 2:** Set up **Internet-facing ALB** (for web) and **Internal ALB** (for app)  
> **Step 3:** Configure **VPC Endpoints** for private S3 access  
> **Step 4:** Make sure EC2s have IAM roles to access S3  
> **Step 5:** Access app via ALB DNS name and test upload

---

## 🔒 Security

- IAM roles for secure service access
- Private subnets for RDS and app layer
- Security groups limit access to only needed ports
- VPC Endpoints ensure no S3 traffic goes over the internet

---

## 🖼️ Sample Screenshot

```markdown
![Upload Page](Images/Screenshot_(183).png)
![S3 Output](Images/Screenshot_(184).png)
