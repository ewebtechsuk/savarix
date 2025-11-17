<!DOCTYPE html>
<html>
<head>
    <title>Savarix Admin</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.css">
</head>
<body>
<header style="display:flex;align-items:center;justify-content:space-between;">
    <h2>Savarix Admin Panel</h2>
    @auth
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @endauth
</header>

<main>
    @yield('content')
</main>
</body>
</html>
