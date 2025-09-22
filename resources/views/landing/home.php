<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ressapp &mdash; Modern Estate Agency Software</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="/css/landing.css" rel="stylesheet">
    </head>
    <body class="bg-white text-dark">
        <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
            <div class="container">
                <a class="navbar-brand fw-bold" href="/">ressapp</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                        <li class="nav-item ms-lg-3"><a class="btn btn-primary" href="/login">Login</a></li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a class="btn btn-outline-success" href="/register">Sign Up</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            <section class="hero py-5 text-center">
                <div class="container">
                    <h1 class="display-4 fw-bold mb-3">Modern Estate Agency Software</h1>
                    <p class="lead mb-4">Run your agency from anywhere. All-in-one CRM, lettings, diary, and more. First user free, just £10 per additional user.</p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="/register" class="btn btn-success btn-lg px-4">Get Started Free</a>
                        <a href="#features" class="btn btn-outline-primary btn-lg px-4">See Features</a>
                    </div>
                </div>
            </section>

            <section id="features" class="features py-5">
                <div class="container">
                    <div class="row g-4 text-center">
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-semibold">All-in-One CRM</h5>
                                    <p class="card-text text-muted">Manage contacts, properties, viewings, and more in one place.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-semibold">Powerful Diary</h5>
                                    <p class="card-text text-muted">Schedule appointments, viewings, and reminders with ease.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-semibold">Lettings &amp; Admin</h5>
                                    <p class="card-text text-muted">Lettings, compliance, and admin tools built for UK agents.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="about" class="py-5 bg-white border-top">
                <div class="container text-center">
                    <h2 class="fw-bold mb-3">Everything you need to run your agency</h2>
                    <p class="lead text-muted about-copy">Automations, portal feeds, and digital document signing are all included so your team can focus on winning new instructions instead of chasing admin.</p>
                </div>
            </section>

            <section id="pricing" class="cta text-white py-5 text-center">
                <div class="container">
                    <h2 class="fw-bold mb-3">Ready to get started?</h2>
                    <p class="mb-4">Sign up today and get your first user free. Only £10 per additional user.</p>
                    <a href="/register" class="btn btn-light btn-lg px-4">Start Free</a>
                </div>
            </section>
        </main>

        <footer id="contact" class="bg-dark text-white py-4 mt-5">
            <div class="container text-center small">
                &copy; <?php echo date('Y'); ?> ressapp. Not affiliated with Apex27. All rights reserved.
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>
