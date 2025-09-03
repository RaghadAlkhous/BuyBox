@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-6">
        <a href="./stores/create.blade.php" class="btn btn-success mb-3">Add Store</a>
        <a href="./products/create.blade.php" class="btn btn-primary mb-3">Add Product</a>
    </div>
</div>
<div class="row">
    <h4>Stores</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stores as $store)
            <tr>
                <td>{{ $store->id }}</td>
                <td>{{ $store->name }}</td>
                <td>{{ $store->description }}</td>
                <td>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
</div>
@endsection