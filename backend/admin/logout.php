<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_auth.php';

adminLogout();

header('Location: /MUBUGA-TSS/backend/admin/index.php');
exit;
