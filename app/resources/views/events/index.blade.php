@extends('layouts.app')
@section('title', 'Calendario')

@section('content')
<div class="page-head">
    <div><h1>Calendario académico</h1><div class="breadcrumb-mini">Eventos, feriados, exámenes y actividades</div></div>
    <button class="btn btn-brand btn-icon" data-bs-toggle="modal" data-bs-target="#mEvent" onclick="prepCreate()"><i class="bi bi-plus-lg"></i> Nuevo evento</button>
</div>

@php
    $prev = $cursor->copy()->subMonth()->format('Y-m');
    $next = $cursor->copy()->addMonth()->format('Y-m');
    $dows = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
@endphp

<div class="grid-2" style="grid-template-columns:3fr 1fr">
    <div class="card">
        <div class="card-header">
            <span class="title"><i class="bi bi-calendar3"></i> {{ $cursor->translatedFormat('F Y') }}</span>
            <div class="d-flex gap-1">
                <a href="{{ route('events.index', ['ym'=>$prev]) }}" class="btn btn-sm btn-light"><i class="bi bi-chevron-left"></i></a>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-light">Hoy</a>
                <a href="{{ route('events.index', ['ym'=>$next]) }}" class="btn btn-sm btn-light"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0" style="table-layout:fixed">
                <thead><tr>@foreach($dows as $d)<th class="text-center small text-muted">{{ $d }}</th>@endforeach</tr></thead>
                <tbody>
                @foreach($weeks as $week)
                    <tr>
                    @foreach($week as $day)
                        @php
                            $inMonth = $day->month === $monthStart->month;
                            $isToday = $day->isToday();
                            $dayEvents = $events->get($day->toDateString(), collect());
                        @endphp
                        <td style="height:104px;vertical-align:top;{{ $inMonth ? '' : 'background:#fafbfc' }}" class="px-1 py-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small {{ $inMonth ? '' : 'text-muted' }} {{ $isToday ? 'badge bg-success rounded-circle' : '' }}" style="{{ $isToday ? 'width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center' : '' }}">{{ $day->day }}</span>
                                <button class="btn btn-sm btn-link p-0 text-muted" title="Agregar" onclick="prepCreate('{{ $day->toDateString() }}')" data-bs-toggle="modal" data-bs-target="#mEvent" style="font-size:11px"><i class="bi bi-plus-circle"></i></button>
                            </div>
                            @foreach($dayEvents as $ev)
                                <div class="mb-1" style="cursor:pointer" onclick='prepEdit(@json($ev))' data-bs-toggle="modal" data-bs-target="#mEvent">
                                    <span style="display:block;background:{{ $ev->color }};color:#fff;border-radius:4px;padding:1px 5px;font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $ev->title }}</span>
                                </div>
                            @endforeach
                        </td>
                    @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header"><span class="title"><i class="bi bi-tags"></i> Tipos</span></div>
            <div class="card-body">
                @foreach($colors as $type=>$color)
                    <div class="d-flex align-items-center gap-2 mb-2"><span style="width:14px;height:14px;border-radius:3px;background:{{ $color }};display:inline-block"></span><span class="text-capitalize small">{{ $type }}</span></div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="title"><i class="bi bi-clock"></i> Próximos eventos</span></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                    @forelse($upcoming as $ev)
                        <tr><td class="ps-3">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $ev->color }};display:inline-block;margin-right:6px"></span>
                            <strong class="small">{{ $ev->title }}</strong>
                            <div class="small text-muted">{{ $ev->date->translatedFormat('d M') }}@if($ev->end_date) – {{ $ev->end_date->translatedFormat('d M') }}@endif · {{ ucfirst($ev->type) }}</div>
                        </td></tr>
                    @empty<tr><td class="empty-state">Sin eventos próximos</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mEvent"><div class="modal-dialog"><div class="modal-content">
    <form id="eventForm" method="POST">@csrf
        <input type="hidden" name="_method" id="ev_method" value="POST">
        <div class="modal-header"><h5 class="modal-title" id="ev_title">Nuevo evento</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Título <span class="text-danger">*</span></label><input name="title" id="ev_name" class="form-control" required></div>
            <div class="row g-2">
                <div class="col-6"><label class="form-label">Fecha <span class="text-danger">*</span></label><input type="date" name="date" id="ev_date" class="form-control" required></div>
                <div class="col-6"><label class="form-label">Fecha fin (opcional)</label><input type="date" name="end_date" id="ev_end" class="form-control"></div>
                <div class="col-12"><label class="form-label">Tipo</label>
                    <select name="type" id="ev_type" class="form-select">
                        @foreach($colors as $type=>$color)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach
                    </select></div>
                <div class="col-12"><label class="form-label">Descripción</label><textarea name="description" id="ev_desc" class="form-control" rows="3"></textarea></div>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" id="ev_delete" class="btn btn-outline-danger btn-sm" style="display:none" onclick="submitDelete()"><i class="bi bi-trash"></i> Eliminar</button>
            <button class="btn btn-brand">Guardar</button>
        </div>
    </form>
    <form id="deleteForm" method="POST" class="d-none">@csrf @method('DELETE')</form>
</div></div></div>
@endsection

@push('scripts')
<script>
const storeUrl = "{{ route('events.store') }}";
const baseUrl = "{{ url('events') }}";
function prepCreate(date=''){
    document.getElementById('ev_title').textContent='Nuevo evento';
    document.getElementById('eventForm').action=storeUrl;
    document.getElementById('ev_method').value='POST';
    document.getElementById('ev_name').value='';
    document.getElementById('ev_date').value=date||'{{ now()->toDateString() }}';
    document.getElementById('ev_end').value='';
    document.getElementById('ev_type').value='actividad';
    document.getElementById('ev_desc').value='';
    document.getElementById('ev_delete').style.display='none';
}
function prepEdit(ev){
    document.getElementById('ev_title').textContent='Editar evento';
    document.getElementById('eventForm').action=baseUrl+'/'+ev.id;
    document.getElementById('ev_method').value='PUT';
    document.getElementById('ev_name').value=ev.title;
    document.getElementById('ev_date').value=ev.date ? ev.date.substring(0,10) : '';
    document.getElementById('ev_end').value=ev.end_date ? ev.end_date.substring(0,10) : '';
    document.getElementById('ev_type').value=ev.type;
    document.getElementById('ev_desc').value=ev.description||'';
    const del=document.getElementById('ev_delete');
    del.style.display='inline-block';
    document.getElementById('deleteForm').action=baseUrl+'/'+ev.id;
}
function submitDelete(){ if(confirm('¿Eliminar este evento?')) document.getElementById('deleteForm').submit(); }
</script>
@endpush
