<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <h1>Ressapp Dashboard</h1>
    <p>Welcome, {{ Auth::user()->name }}!</p>
    <p>This is your dashboard. Use the navigation to explore the app features.</p>
</body>
</html>
