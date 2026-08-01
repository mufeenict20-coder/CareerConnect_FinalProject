<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Helper function for user profile picture
function getProfilePicPath($picFileName) {
    if (!empty($picFileName) && file_exists(__DIR__ . '/../uploads/profiles/' . $picFileName)) {
        return 'uploads/profiles/' . htmlspecialchars($picFileName);
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['name'] ?? 'User') . '&background=2563eb&color=fff&size=128';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerConnect - Smart Job & Internship Platform</title>
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --navy-900: #0f172a;
            --navy-800: #1e293b;
            --sapphire-600: #2563eb;
            --sapphire-700: #1d4ed8;
            --sapphire-50:  #eff6ff;
            --slate-800: #1e293b;
            --slate-600: #475569;
        }

        body {
            background: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(56, 189, 248, 0.10) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(30, 41, 59, 0.08) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(239, 246, 255, 0.8) 0px, transparent 50%),
                #f8fafc;
            background-attachment: fixed;
            color: var(--slate-800);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }

        .navbar-slate {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(12px);
            border-bottom: 3px solid var(--sapphire-600);
        }

        .navbar-brand {
            font-weight: 800;
            color: #ffffff !important;
            letter-spacing: 0.5px;
        }

        .navbar-brand i {
            color: #38bdf8;
        }

        .nav-link {
            color: #cbd5e1 !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #ffffff !important;
        }

        .btn-sapphire {
            background-color: var(--sapphire-600);
            color: #ffffff;
            border: none;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
            transition: all 0.2s ease;
        }

        .btn-sapphire:hover {
            background-color: var(--sapphire-700);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.1) !important;
        }

        .vacancy-card {
            background: linear-gradient(145deg, #ffffff 0%, var(--sapphire-50) 100%);
            border-left: 5px solid var(--sapphire-600);
        }

        .bg-corporate-hero {
            background: linear-gradient(135deg, var(--navy-900) 0%, #1e3a8a 100%);
            color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
        }

        .badge-sapphire {
            background-color: var(--sapphire-50);
            color: var(--sapphire-700);
            border: 1px solid #bfdbfe;
            font-weight: 600;
        }

        .nav-profile-img {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--sapphire-600);
        }

        .text-sapphire { color: var(--sapphire-600) !important; }
        .text-navy { color: var(--navy-900) !important; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark navbar-slate shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
        <i class="bi bi-briefcase-fill me-2 fs-4"></i>CareerConnect
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center me-2" href="dashboard.php">
                    <img src="<?= getProfilePicPath($_SESSION['profile_pic'] ?? '') ?>" class="nav-profile-img me-2">
                    <span class="badge badge-sapphire me-1"><?= htmlspecialchars($_SESSION['role']) ?></span> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="btn btn-outline-light btn-sm px-3 rounded-pill" href="logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <li class="nav-item ms-lg-2">
                <a class="btn btn-sapphire btn-sm px-3 rounded-pill" href="register.php">Register</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-4 flex-grow-1">