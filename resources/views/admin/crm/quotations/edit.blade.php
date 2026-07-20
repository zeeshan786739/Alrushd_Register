@extends('admin.layouts.app')
@section('title', 'Edit Quotation')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Edit Quotation','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Quotations','url'=>route('admin.crm.quotations.index')],['label'=>$quotation->quotation_number,'url'=>route('admin.crm.quotations.show',$quotation)],['label'=>'Edit']]])
    <form method="POST" action="{{ route('admin.crm.quotations.update',$quotation) }}">@csrf @method('PUT')
        <div class="card radius-12 shadow-2 border-0 mb-24"><div class="card-body p-24"><div class="row g-3">
            <div class="col-md-4"><label class="form-label">Customer *</label><select name="customer_id" class="form-select radius-8" required>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id',$quotation->customer_id)==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Project</label><select name="project_id" class="form-select radius-8"><option value="">None</option>@foreach($projects as $p)<option value="{{ $p->id }}" @selected(old('project_id',$quotation->project_id)==$p->id)>{{ $p->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select radius-8">@foreach(['draft','sent','accepted','rejected','expired'] as $s)<option value="{{ $s }}" @selected(old('status',$quotation->status)==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Quotation Date *</label><input type="date" name="quotation_date" class="form-control radius-8" value="{{ old('quotation_date',$quotation->quotation_date->format('Y-m-d')) }}" required></div>
            <div class="col-md-4"><label class="form-label">Valid Until</label><input type="date" name="valid_until" class="form-control radius-8" value="{{ old('valid_until',optional($quotation->valid_until)->format('Y-m-d')) }}"></div>
            <div class="col-md-2"><label class="form-label">Tax %</label><input type="number" step="0.01" name="tax_percentage" class="form-control radius-8" value="{{ old('tax_percentage',$quotation->tax_percentage) }}"></div>
            <div class="col-md-2"><label class="form-label">Discount %</label><input type="number" step="0.01" name="discount_percentage" class="form-control radius-8" value="{{ old('discount_percentage',$quotation->discount_percentage) }}"></div>
            <div class="col-12"><label class="form-label">Terms</label><textarea name="terms" class="form-control radius-8" rows="2">{{ old('terms',$quotation->terms) }}</textarea></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control radius-8" rows="2">{{ old('notes',$quotation->notes) }}</textarea></div>
        </div></div></div>
        @include('admin.crm.partials.line-items-form', ['items' => $quotation->items])
        <div class="d-flex gap-12"><button class="btn btn-primary-600 radius-8 px-24 py-11">Save Changes</button><a href="{{ route('admin.crm.quotations.show',$quotation) }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a></div>
    </form>
</div>
@endsection
