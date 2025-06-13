@extends('layouts.app', ['language' => $language, 'seo' => $seo])

@php
    $article = json_encode(app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)->toHtml($article)) ?? '';
@endphp

@section('meta')
    <script>
      window.article = {!! $article !!}
    </script>
    <script type="module" src="assets/Documentation.js"></script>
@endsection
