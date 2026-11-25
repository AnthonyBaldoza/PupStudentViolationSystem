@extends('layouts.app')

@section('title', 'Add Violation Category')

@section('content')
    <div class="card" style="max-width: 720px">
        <div class="card-header">Category Details</div>
        <div class="card-body p-4">
            @include('categories._form', [
                'category' => $category,
                'action' => route('categories.store'),
                'cancel' => route('categories.index'),
            ])
        </div>
    </div>
@endsection