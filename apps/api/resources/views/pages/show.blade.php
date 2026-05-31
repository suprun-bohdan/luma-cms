<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    @if($metaDescription !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:title" content="{{ $metaTitle }}">
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
        main { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem; }
        .hero { padding: 3rem 0; text-align: center; }
        .hero img { max-width: 100%; height: auto; border-radius: 0.5rem; margin-bottom: 1rem; }
        .hero h1 { font-size: 2.25rem; margin: 0 0 0.5rem; }
        .hero p { color: #475569; font-size: 1.125rem; }
        .rich-text { line-height: 1.7; margin: 2rem 0; }
        .cta { text-align: center; margin: 2rem 0; }
        .cta a { display: inline-block; background: #0f172a; color: #fff; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; }
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
</body>
</html>
