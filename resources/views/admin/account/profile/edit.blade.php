@extends('admin.layouts.app')
@section('title', 'Profile')
@section('content')
@include('admin.account.partials.shell', [
    'activeTab' => 'profile',
    'shellTitle' => 'Profile',
    'shellSubtitle' => 'Update your name, email, and profile photo.',
])

<div class="row justify-content-center">
    <div class="col-xl-8 col-xxl-6">
        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Personal details</h2>
                    <p class="acct-panel__desc">This information appears in the admin panel and activity logs.</p>
                </div>
            </div>
            <div class="acct-panel__body">
                <form class="needs-validation" novalidate action="{{ route('admin.account.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="acct-profile-preview mb-20">
                        <span class="acct-profile-preview__avatar" id="profile-avatar-preview">
                            @if(auth('admin')->user()->image)
                                <img src="{{ Storage::url(auth('admin')->user()->image) }}" alt="">
                            @else
                                {{ \App\Support\AccountHubHelper::initials(auth('admin')->user()) }}
                            @endif
                        </span>
                        <div>
                            <strong>{{ auth('admin')->user()->name }}</strong>
                            <small>{{ auth('admin')->user()->email }}</small>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label" for="name">Full name</label>
                            <input type="text" name="name" id="name" class="form-control radius-8 @error('name') is-invalid @enderror" required value="{{ old('name', auth('admin')->user()->name) }}">
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="email">Email address</label>
                            <input type="email" name="email" id="email" class="form-control radius-8 @error('email') is-invalid @enderror" required value="{{ old('email', auth('admin')->user()->email) }}">
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="image">Profile photo</label>
                            <input class="form-control radius-8" type="file" name="image" id="image" accept="image/*">
                            @error('image')<div class="text-danger text-sm mt-4">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="em-settings-footer mt-24 pt-20">
                        <p class="em-settings-footer__hint mb-0">Changes apply to your login only.</p>
                        <button class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" type="submit">
                            <iconify-icon icon="solar:diskette-linear"></iconify-icon> Save profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('image')?.addEventListener('change', function (event) {
    const preview = document.getElementById('profile-avatar-preview');
    const file = event.target.files?.[0];
    if (!preview || !file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        preview.innerHTML = '<img src="' + e.target.result + '" alt="">';
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
