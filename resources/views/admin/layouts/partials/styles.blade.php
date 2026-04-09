<style>
    :root {
        --color-primary: #ffea7c;
        --color-secondary: #b27f2a;
        --color-dark: #111827;
        --color-dark-soft: #1f2937;
        --color-surface: #ffffff;
        --color-bg: #f5f7fb;
        --color-border: #e5e7eb;
        --color-text: #0f172a;
        --color-muted: #64748b;
        --sidebar-width: 280px;
    }

    html {
        scroll-behavior: smooth
    }

    body {
        background: var(--color-bg);
        min-height: 100vh;
        color: var(--color-text)
    }

    .content-shell {
        min-height: 100vh;
        background: linear-gradient(180deg, #f8fafc 0%, #f5f7fb 100%)
    }

    .sidebar-shell {
        width: var(--sidebar-width)
    }

    .material-card {
        background: #fff;
        border: 1px solid var(--color-border);
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05), 0 10px 25px rgba(15, 23, 42, .06)
    }

    .material-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: separate;
        border-spacing: 0
    }

    .material-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f8fafc
    }

    .material-table th,
    .material-table td {
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top
    }

    .table-col {
        width: 160px;
        min-width: 160px;
        max-width: 160px
    }

    .table-col-wide {
        width: 220px;
        min-width: 220px;
        max-width: 220px
    }

    .table-text {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.5
    }

    .scrollbar-thin::-webkit-scrollbar {
        height: 10px;
        width: 10px
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px
    }

    .scrollbar-thin::-webkit-scrollbar-track {
        background: #f1f5f9
    }

    .mobile-card {
        display: none
    }

    .nav-dropdown {
        display: none
    }

    .nav-dropdown.open {
        display: block
    }

    .sidebar-overlay {
        display: none
    }

    @media (max-width: 1023px) {
        .sidebar-shell {
            transform: translateX(-100%);
            transition: transform .25s ease;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 60;
            width: var(--sidebar-width)
        }

        .sidebar-shell.open {
            transform: translateX(0)
        }

        .sidebar-overlay.open {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, .45);
            z-index: 50
        }

        .desktop-offset {
            margin-left: 0 !important
        }

        .material-table {
            display: none
        }

        .mobile-card {
            display: grid
        }
    }

    @media (min-width: 1024px) {
        .desktop-offset {
            margin-left: var(--sidebar-width)
        }
    }

    #sidebar::-webkit-scrollbar {
        width: 4px;
    }

    #sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    #sidebar::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.25);
        border-radius: 20px;
    }

    @media (min-width: 1024px) {
        #sidebar.sidebar-desktop-collapsed {
            width: 80px !important;
            min-width: 80px !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-text,
        #sidebar.sidebar-desktop-collapsed .sidebar-arrow {
            display: none !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-link,
        #sidebar.sidebar-desktop-collapsed .sidebar-collapse-btn {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            gap: 0 !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-icon {
            width: 40px !important;
            height: 40px !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-user-card {
            padding: 0.55rem !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-user-card>div {
            justify-content: center !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-submenu {
            display: none !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-group {
            position: relative;
        }
    }
</style>