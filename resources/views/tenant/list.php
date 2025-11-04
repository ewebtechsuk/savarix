<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Directory</title>
</head>
<body>
    <h1>Tenant Directory</h1>
    <p>The following tenants are currently registered in Savirix:</p>
    <ul>
        <?php foreach ($tenants as $tenant): ?>
            <li>
                <strong><?php echo htmlspecialchars($tenant['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                (<?php echo htmlspecialchars($tenant['slug'], ENT_QUOTES, 'UTF-8'); ?>)
                <ul>
                    <?php foreach ($tenant['domains'] as $domain): ?>
                        <li><?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
