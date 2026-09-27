<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/services/GrokService.php';
require_once __DIR__ . '/services/ResumeParser.php';
require_once __DIR__ . '/services/JobMatcher.php';
