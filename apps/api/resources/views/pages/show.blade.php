<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if($metaDescription !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($metaDescription !== '')
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; color: #0f172a; background: #fff; }
        .site-header { border-bottom: 1px solid #e2e8f0; padding: 1rem 1.5rem; }
        .site-header nav { display: flex; gap: 1rem; flex-wrap: wrap; max-width: 960px; margin: 0 auto; }
        .site-header a { color: #334155; text-decoration: none; font-weight: 500; }
        .site-header a:hover { color: #0f172a; }
        .site-footer { border-top: 1px solid #e2e8f0; padding: 1.5rem; margin-top: 3rem; }
        .site-footer nav { display: flex; gap: 1rem; flex-wrap: wrap; max-width: 960px; margin: 0 auto; justify-content: center; }
        .site-footer a { color: #64748b; text-decoration: none; font-size: 0.875rem; }
        .site-footer a:hover { color: #0f172a; }
        main { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem; }
        .hero { padding: 3rem 0; text-align: center; }
        .hero img { max-width: 100%; height: auto; border-radius: 0.5rem; margin-bottom: 1rem; }
        .hero h1 { font-size: 2.25rem; margin: 0 0 0.5rem; }
        .hero p { color: #475569; font-size: 1.125rem; }
        .rich-text { line-height: 1.7; margin: 2rem 0; }
        .cta { text-align: center; margin: 2rem 0; }
        .cta a { display: inline-block; background: #0f172a; color: #fff; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; }
        .contact-form { margin: 2rem 0; padding: 2rem; border: 1px solid #e2e8f0; border-radius: 0.75rem; }
        .contact-form h2 { margin: 0 0 1.5rem; font-size: 1.5rem; }
        .contact-form__field { margin-bottom: 1rem; }
        .contact-form label { display: block; margin-bottom: 0.35rem; font-weight: 500; font-size: 0.875rem; }
        .contact-form input, .contact-form textarea { width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; font: inherit; }
        .contact-form button { margin-top: 0.5rem; background: #0f172a; color: #fff; border: 0; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; font: inherit; }
        .contact-form__success { color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; }
        .contact-form__errors { color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; list-style: none; }
        .contact-form__hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }
    </style>
</head>
<body>
    <header class="site-header">
        <nav>
            @foreach($menuItems as $item)
                <a href="{{ $item->resolved_url }}">{{ $item->label }}</a>
            @endforeach
        </nav>
    </header>
    <main>
        @foreach($blocks as $blockHtml)
            {!! $blockHtml !!}
        @endforeach
    </main>
    @if($footerMenuItems->isNotEmpty())
        <footer class="site-footer">
            <nav>
                @foreach($footerMenuItems as $item)
                    <a href="{{ $item->resolved_url }}">{{ $item->label }}</a>
                @endforeach
            </nav>
        </footer>
    @endif
</body>
</html>
