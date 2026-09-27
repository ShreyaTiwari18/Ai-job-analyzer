<?php
// Run once from the project root after setting up the database and .env:
//   php database/seed_admin.php

require_once __DIR__ . '/../src/Database.php';

fwrite(STDOUT, "Admin name: ");
$name = trim(fgets(STDIN));

fwrite(STDOUT, "Admin email: ");
$email = trim(fgets(STDIN));

fwrite(STDOUT, "Admin password: ");
$password = trim(fgets(STDIN));

if ($name === '' || $email === '' || $password === '') {
    fwrite(STDERR, "All fields are required.\n");
    exit(1);
}

$db = Database::connection();
$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    fwrite(STDERR, "A user with this email already exists.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $hash, 'admin']);

fwrite(STDOUT, "Admin account created for $email\n");
