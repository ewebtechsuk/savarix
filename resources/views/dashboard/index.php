<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>
    <?php if (isset($user)): ?>
        <p>Welcome back, <?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>.</p>
    <?php endif; ?>
</body>
</html>
