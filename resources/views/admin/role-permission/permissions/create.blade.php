@extends('admin.layouts.app')

@section('title') Add Permission @endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title text-primary mb-0"><span class="icon">
                <iconify-icon icon="fa-solid:save"></iconify-icon>
            </span> Add Permissions</h5>

        @can('view role')
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-primary btn-sm">← Back</a>
        @endcan
    </div>
    <div class="card-body">
        <form class="row gy-3 needs-validation" novalidate action="{{ route('admin.permissions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('POST')


            <div class="col-md-12">
                 <label>Permission Name</label>
                <div class="has-validation">
                    <input type="text" name="name" class="form-control" placeholder="Enter permission name">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="col-md-12 text-end">
                @can('view role')
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-primary btn-sm">← Back</a>
                @endcan

                <button class="btn btn-sm btn-success-600" type="submit"><span class="icon">
                        <iconify-icon icon="fa-solid:save"></iconify-icon>
                    </span> Save</button>
            </div>
        </form>
    </div>
</div>

@endsection