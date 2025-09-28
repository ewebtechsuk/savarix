<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Dashboard</title>
</head>
<body>
    <h1>Tenant Dashboard</h1>
    <?php if (isset($user) && $user !== null): ?>
        <?php
            $tenantName = null;

            if (is_array($user->data ?? null) && array_key_exists('name', $user->data)) {
                $tenantName = $user->data['name'];
            } elseif (isset($user->email)) {
                $tenantName = $user->email;
            } elseif (isset($user->id)) {
                $tenantName = $user->id;
            }

            $tenantName ??= 'Tenant';
        ?>
        <p>Welcome back, <?php echo htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8'); ?>.</p>
    <?php else: ?>
        <p>You are viewing the dashboard as a guest.</p>
    <?php endif; ?>
    <ul>
        <li>Review your tenancy information.</li>
        <li>Submit maintenance requests.</li>
        <li>Review statements and payments.</li>
    </ul>
</body>
</html>
