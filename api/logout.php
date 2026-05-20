<?php
require_once __DIR__ . '/db.php';

session_destroy();
jsonResponse(['success' => true]);
