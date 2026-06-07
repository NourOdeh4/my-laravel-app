<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

           <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg mb-6">
    <div class="max-w-xl">
        <h2 class="text-lg font-medium text-gray-900">الصورة الشخصية</h2>
        
        <div class="mt-4">
            @if(auth()->user()->avatar)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" width="150" class="rounded-full">
            @else
                <p>لا توجد صورة شخصية</p>
            @endif
        </div>

        <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            @method('patch')
            <input type="file" name="avatar" class="block w-full text-sm text-gray-500">
            <button type="submit" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded">رفع الصورة</button>
        </form>
    </div>
</div>

<div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <div class="max-w-xl">
        @include('profile.partials.update-password-form')
    </div>
</div>

            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('patch')

    <div class="mt-4">
        <label for="avatar">تغيير الصورة الشخصية:</label>
        <input type="file" name="avatar" id="avatar" class="form-control">
        <button type="submit" class="btn btn-primary mt-2">رفع الصورة</button>
    </div>
</form>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
