<section class="hero">
    @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="">
    @endif
    @if($headline !== '')
        <h1>{{ $headline }}</h1>
    @endif
    @if($subheadline !== '')
        <p>{{ $subheadline }}</p>
    @endif
</section>
