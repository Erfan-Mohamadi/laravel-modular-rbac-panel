@extends('core::layouts.master')

@section('content')
    <div class="container">
        <h1>لیست ادمین‌ها</h1>
        <table class="table">
            <thead>
            <tr>
                <th>نام</th>
                <th>ایمیل</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($admins as $admin)
                <tr>
                    <td>{{ $admin->name }}</td>
                    <td>{{ $admin->email }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
