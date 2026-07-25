@csrf
@if($port->exists) @method('PUT') @endif
<div class="row">
    <div class="col-lg-8">
        <section class="sc-card form-panel">
            <div class="form-panel-head"><span><i class="fas fa-anchor"></i></span><div><small>FACILITY IDENTITY</small><h2>Port information</h2><p>Use official port and location information.</p></div></div>
            <div class="row">
                <div class="col-md-8 form-group"><label>Port name <b>*</b></label><input class="form-control" name="port_name" value="{{ old('port_name',$port->port_name) }}" required maxlength="150"></div>
                <div class="col-md-4 form-group"><label>Port code</label><input class="form-control text-uppercase" name="port_code" value="{{ old('port_code',$port->port_code) }}" maxlength="12" placeholder="IDJKT"></div>
                <div class="col-md-5 form-group"><label>Country <b>*</b></label><input class="form-control" name="country" value="{{ old('country',$port->country) }}" required></div>
                <div class="col-md-3 form-group"><label>ISO code</label><input class="form-control text-uppercase" name="country_code" value="{{ old('country_code',$port->country_code) }}" maxlength="5" placeholder="ID"></div>
                <div class="col-md-4 form-group"><label>City</label><input class="form-control" name="city" value="{{ old('city',$port->city) }}"></div>
                <div class="col-md-6 form-group"><label>Latitude <b>*</b></label><input type="number" step="0.0000001" min="-90" max="90" class="form-control" name="latitude" value="{{ old('latitude',$port->latitude) }}" required></div>
                <div class="col-md-6 form-group"><label>Longitude <b>*</b></label><input type="number" step="0.0000001" min="-180" max="180" class="form-control" name="longitude" value="{{ old('longitude',$port->longitude) }}" required></div>
            </div>
        </section>
    </div>
    <div class="col-lg-4">
        <section class="sc-card form-panel">
            <div class="form-panel-head"><span><i class="fas fa-sliders-h"></i></span><div><small>OPERATIONS</small><h2>Status & exposure</h2></div></div>
            <div class="form-group"><label>Facility type <b>*</b></label><select class="custom-select" name="port_type" required>@foreach(['Seaport','Container Terminal','Harbor','Marina','River Port','Dry Port'] as $type)<option @selected(old('port_type',$port->port_type ?: 'Seaport')===$type)>{{ $type }}</option>@endforeach</select></div>
            <div class="form-group"><label>Annual capacity (TEU)</label><input type="number" min="0" class="form-control" name="annual_capacity" value="{{ old('annual_capacity',$port->annual_capacity) }}"></div>
            <div class="form-group"><label>Operational status <b>*</b></label><select class="custom-select" name="status" required>@foreach(['Active','Limited','Inactive'] as $status)<option @selected(old('status',$port->status ?: 'Active')===$status)>{{ $status }}</option>@endforeach</select></div>
            <div class="form-group"><label>Risk score <b>*</b></label><input type="number" min="0" max="100" class="form-control" name="risk_index" value="{{ old('risk_index',$port->risk_index ?? 0) }}" required><small class="form-hint">0–39 low, 40–69 medium, 70–100 high.</small></div>
        </section>
    </div>
</div>
<div class="form-actions"><a href="{{ route('admin.ports.index') }}" class="btn sc-btn sc-btn-light"><i class="fas fa-arrow-left"></i> Cancel</a><button class="btn sc-btn sc-btn-primary"><i class="fas fa-save"></i> {{ $port->exists ? 'Save changes' : 'Add port facility' }}</button></div>
