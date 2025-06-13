@extends('layouts.app', ['language' => $language, 'seo' => $seo])

@php
    $writeup = json_encode(app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)->toHtml($writeup)) ?? '';
@endphp

@section('meta')
    <script>
      window.meta = '@json($article ?? "")'
      window.article = {!! $writeup !!}
    </script>
    <script type="module" src="assets/Writeup.js"></script>
@endsection
