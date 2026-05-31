<section class="feature-grid">
    @if($heading !== '')
        <h2>{{ $heading }}</h2>
    @endif
    @if(count($items) > 0)
        <ul>
            @foreach($items as $item)
                <li>
                    @if(($item['title'] ?? '') !== '')
                        <h3>{{ $item['title'] }}</h3>
                    @endif
                    @if(($item['body'] ?? '') !== '')
                        <p>{{ $item['body'] }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
