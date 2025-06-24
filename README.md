# aws-ec2-lamp-stack-webapp-project

**complete LAMP stack (Linux, Apache, MySQL/MariaDB, PHP)** on an AWS EC2 instance, deploy a small PHP project, and ensure database connectivity.

---

## 📖 Overview

We’ll cover:

* Launching an EC2 instance (Amazon Linux 2023) | t2.medium
* Installing Apache, MySQL (MariaDB), PHP
* Creating a simple PHP project connecting to a database
* Setting up database schema and user
* Final testing via browser

---

## 📦 EC2 Setup Prerequisites

* AWS account
* Security Group allowing ports `22 (SSH), 80 (HTTP), 3306 (MySQL)`
* Amazon Linux 2023 AMI
* Key Pair for SSH

---

## ✅ Full LAMP Server Setup & Project Code

---

### 📜 1️⃣ Install LAMP Stack on EC2

SSH into your instance:

```bash
ssh -i "your-key.pem" ec2-user@your-ec2-public-ip
sudo su
```

Install Apache, PHP, MariaDB:

```bash
sudo dnf update -y
sudo dnf install -y httpd mariadb105-server php php-mysqlnd
```

# set system password 
```
sudo -i passwd
```
Start & enable services:

```bash
sudo systemctl start httpd
sudo systemctl enable httpd
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

---

### 📜 2️⃣ Secure MariaDB Installation

```bash
sudo mysql_secure_installation
```

Follow prompts (set root password, remove anonymous users, disallow remote root login, remove test db, reload privilege tables).

---

### 📜 3️⃣ Create Database & User

```sql
sudo mysql -u root -p
```

```sql
CREATE DATABASE sampledb;
CREATE USER 'sampleuser'@'localhost' IDENTIFIED BY 'SamplePass123!';
GRANT ALL PRIVILEGES ON sampledb.* TO 'sampleuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### 📜 4️⃣ Create Project Directory

```bash
sudo mkdir /var/www/html/
cd /var/www/html/
```

---

### 📜 5️⃣ PHP Project Code with Database Connection

**`/var/www/html/myproject/index.php`**

```php
<?php
$servername = "localhost";
$username = "sampleuser";
$password = "SamplePass123!";
$dbname = "sampledb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch data
$sql = "SELECT id, name FROM users";
$result = $conn->query($sql);

echo "<h2>User List</h2>";
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "id: " . $row["id"]. " - Name: " . $row["name"]. "<br>";
  }
} else {
  echo "0 results";
}

$conn->close();
?>
```

---

### 📜 6️⃣ Create Table and Insert Sample Data

```sql
sudo mysql -u root -p
```

```sql
USE sampledb;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

INSERT INTO users (name) VALUES ('Atul Kamble'), ('John Doe');
EXIT;
```

---

### 📜 7️⃣ Permissions & Restart Apache

```bash
sudo chown -R apache:apache /var/www/html/
sudo systemctl restart httpd
```

---

### 📜 8️⃣ Test in Browser

Open `http://your-ec2-public-ip`

✅ You should see:

```
User List  
id: 1 - Name: Atul Kamble  
id: 2 - Name: John Doe  
```

---

## 📦 Bonus: Auto Install Script (User Data for EC2)

Paste this in the **EC2 User Data** while launching the instance for auto-setup:

```bash
#!/bin/bash
# LAMP Stack EC2 User Data Script for Amazon Linux 2023

# Update system
dnf update -y

# Install Apache, MariaDB, PHP, PHP MySQL extension
dnf install -y httpd mariadb105-server php php-mysqlnd

# Start & enable Apache and MariaDB services
systemctl start httpd
systemctl enable httpd
systemctl start mariadb
systemctl enable mariadb

# Set root password and secure MariaDB installation (automated)
MYSQL_ROOT_PASSWORD="SampleRootPass123!"

# Secure MariaDB installation
mysql --user=root <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
DELETE FROM mysql.user WHERE User='';
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF

# Create database and user
mysql --user=root --password=${MYSQL_ROOT_PASSWORD} <<EOF
CREATE DATABASE sampledb;
CREATE USER 'sampleuser'@'localhost' IDENTIFIED BY 'SamplePass123!';
GRANT ALL PRIVILEGES ON sampledb.* TO 'sampleuser'@'localhost';
FLUSH PRIVILEGES;
USE sampledb;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);
INSERT INTO users (name) VALUES ('Atul Kamble'), ('John Doe');
EOF

# Create project directory
mkdir -p /var/www/html/myproject

# Create PHP project file with database connection
cat > /var/www/html/myproject/index.php <<EOL
<?php
\$servername = "localhost";
\$username = "sampleuser";
\$password = "SamplePass123!";
\$dbname = "sampledb";

// Create connection
\$conn = new mysqli(\$servername, \$username, \$password, \$dbname);

// Check connection
if (\$conn->connect_error) {
  die("Connection failed: " . \$conn->connect_error);
}

// Fetch data
\$sql = "SELECT id, name FROM users";
\$result = \$conn->query(\$sql);

echo "<h2>User List</h2>";
if (\$result->num_rows > 0) {
  while(\$row = \$result->fetch_assoc()) {
    echo "id: " . \$row["id"]. " - Name: " . \$row["name"]. "<br>";
  }
} else {
  echo "0 results";
}

\$conn->close();
?>
EOL

# Set correct permissions
chown -R apache:apache /var/www/html/

# Restart Apache to apply changes
systemctl restart httpd
```
