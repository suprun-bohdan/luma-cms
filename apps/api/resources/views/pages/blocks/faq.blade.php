<section class="faq">
    @if($heading !== '')
        <h2>{{ $heading }}</h2>
    @endif
    @if(count($items) > 0)
        <dl>
            @foreach($items as $item)
                @if(($item['question'] ?? '') !== '')
                    <dt>{{ $item['question'] }}</dt>
                @endif
                @if(($item['answer'] ?? '') !== '')
                    <dd>{{ $item['answer'] }}</dd>
                @endif
            @endforeach
        </dl>
    @endif
</section>
