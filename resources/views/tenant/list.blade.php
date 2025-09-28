<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Directory</title>
</head>
<body>
    <h1>Tenant Directory</h1>
    <p>The following tenants are currently registered in Ressapp:</p>
    <ul>
        @foreach ($tenants as $tenant)
            <li>
                <strong>{{ $tenant['name'] }}</strong>
                ({{ $tenant['slug'] }})
                <ul>
                    @foreach ($tenant['domains'] as $domain)
                        <li>{{ $domain }}</li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
</body>
</html>
