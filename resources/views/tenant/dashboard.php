<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Dashboard</title>
</head>
<body>
    <h1>Tenant Dashboard</h1>
    <?php if (isset($user) && $user !== null): ?>
        <p>Welcome back, <?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>.</p>
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
