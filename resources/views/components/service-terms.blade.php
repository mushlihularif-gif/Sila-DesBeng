@props(['text'])
<div class="segi-terms text-sm text-gray-700">
@foreach(\App\Support\ServiceTerms::blocks($text) as $block)
    @if($block['type'] === 'list')<ol>@foreach($block['items'] as $line)<li>{{ $line }}</li>@endforeach</ol>
    @else<p>{{ $block['text'] }}</p>@endif
@endforeach
</div>
