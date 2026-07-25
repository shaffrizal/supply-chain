@extends('layouts.bootstrap5')

@section('title', 'User Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="mb-1 text-dark font-weight-bold"><i class="fas fa-users-cog text-primary mr-2"></i> Identity & Access Management</h1>
        <small class="text-muted">Manage system analysts, administrators, and logisticians access control privileges.</small>
    </div>
    <!-- AKTIF: Tombol ini sekarang memicu modal pop-up dengan ID #modalAddUser -->
    <button class="btn btn-primary shadow-sm font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalAddUser">
        <i class="fas fa-user-plus mr-1"></i> Add New User
    </button>
</div>
@stop

@section('content')

{{-- Notifikasi Sukses/Gagal Profesional --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <h5><i class="icon fas fa-check-circle"></i> Success!</h5>
        {{ session('success') }}
        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <h5><i class="icon fas fa-ban"></i> Whoops! Ada kesalahan input.</h5>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <!-- Ringkasan Statistik User -->
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number">12 Operators</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Now</span>
                <span class="info-box-number">8 Online</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning text-white"><i class="fas fa-user-shield"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Administrators</span>
                <span class="info-box-number">2 Accounts</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-danger"><i class="fas fa-user-slash"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Suspended</span>
                <span class="info-box-number">0 Accounts</span>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Utama -->
<div class="card shadow border-0">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-dark font-weight-bold mb-0">
                <i class="fas fa-list mr-1 text-secondary"></i> System Operators List
            </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search user...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="4%" class="text-center">#</th>
                        <th>User Profile</th>
                        <th>Role Privileges</th>
                        <th>Department</th>
                        <th>Last Activity</th>
                        <th>Status</th>
                        <th width="12%" class="text-center">Actions</th>
                    </tr>
                </thead>
            <tbody>
    @forelse($users as $index => $user)
    <tr>
        <td class="text-center align-middle">{{ $index + 1 }}</td>
        <td class="align-middle">
            <div class="d-flex align-items-center">
                <!-- Membuat avatar otomatis berdasarkan nama user yang didaftarkan -->
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D6EFD&color=fff&rounded=true" alt="Avatar" class="img-circle mr-3" style="width: 40px; height: 40px;">
                <div>
                    <div class="font-weight-bold text-dark">{{ $user->name }}</div>
                    <small class="text-muted">{{ $user->email }}</small>
                </div>
            </div>
        </td>
        <td class="align-middle">
            @if($user->role == 'Admin')
                <span class="badge badge-danger px-2 py-1"><i class="fas fa-shield-alt mr-1"></i> Super Administrator</span>
            @elseif($user->role == 'Analyst')
                <span class="badge badge-warning text-white px-2 py-1"><i class="fas fa-chart-line mr-1"></i> Risk Analyst</span>
            @else
                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-eye mr-1"></i> Logistics Viewer</span>
            @endif
        </td>
        <td class="align-middle font-weight-500 text-secondary">{{ $user->department ?? '-' }}</td>
        <td class="align-middle text-muted" style="font-size: 13px;">{{ $user->created_at->diffForHumans() }}</td>
        <td class="align-middle">
            <span class="badge badge-success"><i class="fas fa-dot-circle mr-1"></i> Active</span>
        </td>
        <td class="text-center align-middle">
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-info" title="Edit Profile" data-bs-toggle="modal" data-bs-target="#editUser{{ $user->id }}"><i class="fas fa-edit"></i></button>
                @if(auth()->id() !== $user->id)<form method="POST" action="{{ route('admin.users.destroy',$user) }}" onsubmit="return confirm('Revoke this user access?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Revoke Access"><i class="fas fa-ban"></i></button></form>@endif
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="7" class="text-center text-muted p-4">
            <i class="fas fa-users fa-2x mb-2 d-block text-secondary"></i> No operators registered yet.
        </td>
    </tr>
    @endforelse
</tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top py-3 clearfix">
       <small class="text-muted float-left">Showing {{ $users->count() }} user account(s).</small>
    </div>
</div>

<!-- MODAL FORM POP-UP (AKSI ADD NEW USER) -->
<div class="modal fade" id="modalAddUser" tabindex="-1" role="dialog" aria-labelledby="modalAddUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalAddUserLabel">
                    <i class="fas fa-user-plus mr-2"></i> Register New System Operator
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            {{-- Form diarahkan ke route admin.users.store dengan metode POST --}}
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="font-weight-bold">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="username@supplychain.id" value="{{ old('email') }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role" class="font-weight-bold">Role Privilege</label>
                                <select name="role" id="role" class="form-control" required>
                                    <option value="Admin">Super Administrator</option>
                                    <option value="Analyst">Risk Analyst</option>
                                    <option value="Viewer">Logistics Viewer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department" class="font-weight-bold">Department</label>
                                <input type="text" name="department" id="department" class="form-control" placeholder="e.g. IT, Procurement" value="{{ old('department') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="font-weight-bold">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimum 8 characters" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Save Operator</button>
                </div>
            </form>
        </div>
    </div>
</div>
@foreach($users as $user)
<div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="POST" action="{{ route('admin.users.update',$user) }}">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit {{ $user->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="form-group"><label>Name</label><input class="form-control" name="name" value="{{ $user->name }}" required></div><div class="form-group"><label>Email</label><input class="form-control" type="email" name="email" value="{{ $user->email }}" required></div><div class="row"><div class="col-md-6 form-group"><label>Role</label><select class="form-control" name="role">@foreach(['Admin','Analyst','Operator','Viewer'] as $role)<option @selected($user->role===$role)>{{ $role }}</option>@endforeach</select></div><div class="col-md-6 form-group"><label>Department</label><input class="form-control" name="department" value="{{ $user->department }}" required></div></div><div class="form-group"><label>New password (optional)</label><input class="form-control" type="password" name="password" minlength="8"></div></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div></form></div></div>
@endforeach
@stop
