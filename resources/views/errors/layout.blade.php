<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - SegiDaerah</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('Admin/img/illustrations/logodomain.webp') }}" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Boxicons & Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #0099ff;
            --primary-hover: #0080d9;
            --primary-soft: rgba(0, 153, 255, 0.08);
            --gold: #f59e0b;
            --gold-soft: rgba(245, 158, 11, 0.12);
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --bg-page: #f8fafd;
            --card-border: rgba(226, 232, 240, 0.8);
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Background Glow */
        .ambient-blob {
            position: absolute;
            border-radius: 9999px;
            filter: blur(80px);
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
        }
        .blob-1 {
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(0, 153, 255, 0.35) 0%, rgba(56, 189, 248, 0.1) 70%);
            top: -60px;
            left: -60px;
        }
        .blob-2 {
            width: 340px;
            height: 340px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.25) 0%, rgba(251, 191, 36, 0.05) 70%);
            bottom: -40px;
            right: -40px;
        }
        .blob-3 {
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, transparent 70%);
            top: 40%;
            right: 15%;
        }

        /* Main Container */
        .error-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 680px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 2rem;
            border: 1px solid var(--card-border);
            box-shadow: 0 20px 45px -15px rgba(0, 153, 255, 0.12),
                        0 0 0 1px rgba(255, 255, 255, 0.9) inset;
            padding: 2.75rem 2rem 2.25rem 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Brand Header */
        .brand-pill {
            display: inline-flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            padding: 0.35rem 0.85rem 0.35rem 0.5rem;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-dark);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
        }
        .brand-pill img {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            object-fit: contain;
        }
        .brand-pill .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background-color: var(--primary);
        }

        /* Illustration Wrapper */
        .illustration-wrapper {
            position: relative;
            width: 100%;
            max-width: 440px;
            margin: 0.5rem auto 1.5rem auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .illustration-img {
            max-width: 100%;
            max-height: 280px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 14px 24px rgba(0, 153, 255, 0.12));
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            user-select: none;
            -webkit-user-drag: none;
        }
        .illustration-img:hover {
            transform: scale(1.03) translateY(-4px);
        }

        /* Error Code Badge */
        .code-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 0.8125rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid rgba(0, 153, 255, 0.2);
            margin-bottom: 0.85rem;
        }
        .code-badge i {
            font-size: 0.95rem;
        }

        /* Headings & Texts */
        .error-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.25;
            margin-bottom: 0.65rem;
            letter-spacing: -0.02em;
        }
        .error-desc {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 520px;
            margin-bottom: 1.75rem;
            font-weight: 500;
        }

        /* Action Buttons */
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.85rem;
            width: 100%;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 700;
            padding: 0.8rem 1.6rem;
            border-radius: 9999px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.25s ease;
            outline: none;
            border: none;
            white-space: nowrap;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0099ff 0%, #0077e6 100%);
            color: #ffffff;
            box-shadow: 0 10px 20px -6px rgba(0, 153, 255, 0.45);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0088ee 0%, #0066cc 100%);
            transform: translateY(-2px);
            box-shadow: 0 14px 25px -6px rgba(0, 153, 255, 0.55);
            color: #ffffff;
        }
        .btn-secondary {
            background: #ffffff;
            color: #475569;
            border: 1.5px solid #cbd5e1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }
        .btn-secondary:hover {
            background: #f8fafc;
            color: var(--text-dark);
            border-color: #94a3b8;
            transform: translateY(-2px);
        }

        /* Footer Info */
        .card-footer {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(226, 232, 240, 0.6);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            font-size: 0.8125rem;
            color: #94a3b8;
        }
        .card-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .card-footer a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .error-card {
                padding: 2rem 1.25rem 1.75rem 1.25rem;
                border-radius: 1.5rem;
            }
            .illustration-img {
                max-height: 210px;
            }
            .error-title {
                font-size: 1.4rem;
            }
            .error-desc {
                font-size: 0.9375rem;
                margin-bottom: 1.5rem;
            }
            .btn-group {
                flex-direction: column;
                width: 100%;
            }
            .btn {
                width: 100%;
            }
            .card-footer {
                justify-content: center;
                text-align: center;
            }
        }
    </style>
    @yield('extra-styles')
</head>
<body>
    <!-- Background Ambient Glow -->
    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>
    <div class="ambient-blob blob-3"></div>

    <!-- Main Card -->
    <main class="error-card" role="main">
        <!-- SegiDaerah Brand Pill -->
        <div class="brand-pill">
            <img src="{{ asset('Admin/img/illustrations/logodomain.webp') }}" alt="Logo SegiDaerah" onerror="this.style.display='none'">
            <span>SegiDaerah</span>
            <span class="badge-dot"></span>
            <span style="font-weight: 500; color: #64748b;">Kecamatan Bengkalis</span>
        </div>

        <!-- Mascot Illustration -->
        <div class="illustration-wrapper">
            <img
                src="@yield('image', asset('Admin/img/halamantidakberfungsi/SegiDaerah2.png'))"
                alt="@yield('title')"
                class="illustration-img"
                loading="eager"
                decoding="async"
                onerror="this.onerror=null;this.src='{{ asset('Admin/img/halamantidakberfungsi/SegiDaerah2.png') }}'"
            >
        </div>

        <!-- Error Code Badge -->
        <div class="code-badge">
            <i class="@yield('badge-icon', 'bx bx-error-circle')"></i>
            <span>Error @yield('code', '404')</span>
        </div>

        <!-- Heading -->
        <h1 class="error-title">@yield('heading', 'Terjadi Kendala')</h1>

        <!-- Description -->
        <p class="error-desc">
            @yield('message', 'Mohon maaf, halaman yang Anda tuju tidak dapat dimuat saat ini.')
        </p>

        <!-- Actions -->
        <div class="btn-group">
            @yield('actions')
        </div>

        <!-- Footer Help -->
        <footer class="card-footer">
            <span>&copy; {{ date('Y') }} SegiDaerah Bengkalis. Hak Cipta Dilindungi.</span>
            <a href="{{ route('beranda') }}">
                <i class="bx bx-home-alt"></i> Beranda Utama
            </a>
        </footer>
    </main>
</body>
</html>
