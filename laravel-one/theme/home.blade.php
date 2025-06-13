@extends('layouts.app', ['language' => $language, 'seo' => $seo])

@section('meta')
    <script>
      window.projects = JSON.parse('@json($portfolio)').projects;
    </script>
    <script type="module" src="assets/Home.js"></script>
@endsection
