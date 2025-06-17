# 🖼️ Image Upload App – AWS 3-Tier Architecture

This project is a **secure, scalable, and highly available image upload web application** deployed on AWS using a 3-tier architecture. It allows users to upload images and accompanying text. The images are stored in **Amazon S3**, the text metadata is stored in **Amazon RDS (MySQL)**, and a **CloudFront URL** is generated for each uploaded image.

---

## 🧰 AWS Services Used

- **Amazon EC2** – For hosting Web and App tiers
- **Amazon RDS (MySQL)** – For storing metadata (text)
- **Amazon S3** – For storing image files
- **Amazon CloudFront** – For distributing uploaded images globally
- **Elastic Load Balancer (ELB)** – Internet-facing and internal
- **Auto Scaling Groups** – For scaling EC2s in Web and App tiers
- **IAM Roles** – For secure access to S3
- **Amazon VPC** – Custom networking with private/public subnets
- **VPC Endpoint (S3 Gateway)** – To allow private subnets access to S3
- **Amazon AMIs** – Custom AMIs used to launch EC2s
- **CloudWatch** – For monitoring and logs (optional)
- **Jump Server** – For secure internal testing and troubleshooting

---

## 🏗️ Architecture

> _(Add architecture diagram image here with: ![Architecture](path_to_image))_

