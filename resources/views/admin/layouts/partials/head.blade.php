<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'FalconDrive Admin') }}</title>
    <meta name="robots" content="noindex, nofollow" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                screens: {
                    sm: '640px',
                    md: '768px',
                    lg: '1000px',
                    xl: '1280px',
                    '2xl': '1536px',
                },
                extend: {
                    boxShadow: {
                        material: '0 12px 30px rgba(15,23,42,.08), 0 2px 10px rgba(15,23,42,.06)',
                        soft: '0 4px 16px rgba(15,23,42,.06)',
                        card: '0 1px 2px rgba(15,23,42,.06), 0 8px 24px rgba(15,23,42,.06)'
                    }
                }
            }
        }
    </script>
    <link href="{{ asset('images/favicon.webp') }}" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    @stack('styles')

    @include('admin.layouts.partials.styles')
</head>
