@extends('layouts.app')

@section('title', 'Edit Violation Category')

@section('content')
    <div class="card" style="max-width: 720px">
        <div class="card-header">Edit {{ $category->name }}</div>
        <div class="card-body p-4">
            @include('categories._form', [
                'category' => $category,
                'action' => route('categories.update', $category),
                'cancel' => route('categories.index'),
            ])
        </div>
    </div>
@endsection