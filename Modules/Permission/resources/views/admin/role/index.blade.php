@extends('core::layouts.master')

@section('content')
    <div class="container">
        <h1>مدیریت نقش ها و مجوز ها</h1>
        <table class="table">
            <thead>
            <tr>
                <th>نام</th>
                <th>ایمیل</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->label }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
