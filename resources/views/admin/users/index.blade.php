@extends('admin.base')

@section('content')
<div class="container">
    <br>
    <a href="{{ route('admin.dashboard') }}" class="">
        ← Volver al Panel Admin
    </a>
    
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h2>Gestión de Usuarios</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Crear Usuario</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->is_admin==true)
                                <span class="badge bg-warning text-dark">Admin User</span>
                            @else
                                <span class="badge bg-success">Regular User</span>
                            @endif
          
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection