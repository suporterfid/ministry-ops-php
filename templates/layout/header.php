<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= Helpers::e($title ?? 'Ministry Ops') ?></title>
    <link rel="stylesheet" href="<?= Helpers::url('public/css/style.css') ?>">
</head>
<body>

<?php if (Auth::check()): ?>
<header class="app-header">
    <a href="<?= Helpers::url('dashboard') ?>" class="brand">
        <div class="brand-icon">M</div>
        <span>Ministry Ops</span>
    </a>

    <?php 
    $currentUser = Auth::user();
    if (!empty($currentUser['memberships'])): 
    ?>
    <form action="<?= Helpers::url('tenant/select') ?>" method="POST" style="margin:0;">
        <select name="tenant_id" class="header-tenant-selector" onchange="this.form.submit()">
            <?php foreach ($currentUser['memberships'] as $m): ?>
                <option value="<?= Helpers::e($m['tenant_id']) ?>" <?= $m['tenant_id'] === $currentUser['current_tenant_id'] ? 'selected' : '' ?>>
                    <?= Helpers::e($m['tenant_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>
</header>
<?php endif; ?>

<main class="container">
    <?php if ($flash = Helpers::getFlash()): ?>
        <div class="alert alert-<?= Helpers::e($flash['type']) ?>">
            <span><?= Helpers::e($flash['message']) ?></span>
        </div>
    <?php endif; ?>
