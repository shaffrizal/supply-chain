@extends('layouts.bootstrap5')

@section('title', 'Port Dataset Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div><h1 class="mb-1 font-weight-bold"><i class="fas fa-database text-primary mr-2"></i>Port Dataset Management</h1><small class="text-muted">Authorized workspace for creating, updating, and removing global port records.</small></div>
    <div><a href="{{ route('ports.index') }}" class="btn btn-default mr-2"><i class="fas fa-eye mr-1"></i> Public Directory</a><a href="{{ route('admin.ports.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Add Facility</a></div>
</div>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button class="close" data-bs-dismiss="alert" aria-label="Close">&times;</button><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif
<div class="row">
@foreach([['Total records',$totalPorts,'database','primary'],['Active',$activePorts,'check-circle','success'],['Limited',$limitedPorts,'exclamation-circle','warning'],['High risk',$highRiskPorts,'shield-alt','danger']] as [$label,$value,$icon,$tone])
<div class="col-lg-3 col-6"><div class="small-box bg-{{ $tone }}"><div class="inner"><h3>{{ number_format($value) }}</h3><p>{{ $label }}</p></div><div class="icon"><i class="fas fa-{{ $icon }}"></i></div></div></div>
@endforeach
</div>
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-anchor mr-2"></i>Facilities</h3><form class="card-tools d-flex" method="GET"><input class="form-control form-control-sm mr-2" style="width:240px" type="search" name="search" value="{{ $search }}" placeholder="Search name, code, country, city" autocomplete="off"><select class="form-control form-control-sm mr-2" name="status"><option value="">All statuses</option>@foreach(['Active','Limited','Inactive'] as $option)<option @selected($status===$option)>{{ $option }}</option>@endforeach</select><button class="btn btn-primary btn-sm" title="Apply filters"><i class="fas fa-search"></i><span>Filter</span></button>@if($search||$status)<a class="btn btn-default btn-sm ml-1" href="{{ route('admin.ports.index') }}" title="Reset filters"><i class="fas fa-times"></i></a>@endif</form></div>
    <div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap"><thead><tr><th>Code</th><th>Facility</th><th>Country / City</th><th>Type</th><th>Status</th><th>Risk</th><th class="text-right">Actions</th></tr></thead><tbody>
    @forelse($ports as $port)<tr><td><code>{{ $port->port_code ?: '—' }}</code></td><td><strong>{{ $port->port_name }}</strong></td><td>{{ $port->country }}<small class="d-block text-muted">{{ $port->city ?: 'Unknown city' }}</small></td><td>{{ $port->port_type }}</td><td><span class="badge badge-{{ $port->status==='Active'?'success':($port->status==='Limited'?'warning':'secondary') }}">{{ $port->status }}</span></td><td><span class="badge badge-{{ $port->risk_index>=70?'danger':($port->risk_index>=40?'warning':'success') }}">{{ $port->risk_index }}/100</span></td><td class="text-right"><a href="{{ route('ports.show',$port) }}" class="btn btn-default btn-xs" title="View"><i class="fas fa-eye"></i></a><a href="{{ route('admin.ports.edit',$port) }}" class="btn btn-warning btn-xs" title="Edit"><i class="fas fa-pen"></i></a><form class="d-inline" method="POST" action="{{ route('admin.ports.destroy',$port) }}">@csrf @method('DELETE')<button class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('Delete this port facility?')"><i class="fas fa-trash"></i></button></form></td></tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-5">No port records match the current filter.</td></tr>@endforelse
    </tbody></table></div>
    @if($ports->hasPages())<div class="card-footer clearfix">{{ $ports->links('pagination::bootstrap-5') }}</div>@endif
</div>
@stop
