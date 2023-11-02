@extends('layouts.app', ['language' => $language, 'seo' => $seo])

@section('content')
<div class="container w-full md:max-w-3xl mx-auto pt-20 pb-12">
    <div class="w-full px-4 md:px-6 text-xl text-gray-800 leading-normal" style="font-family:Georgia,serif;">
        <x-markdown>
            {!! $readme !!}
        </x-markdown>
    </div>
</div>
@endsection
