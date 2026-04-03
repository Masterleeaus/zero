@extends('titantalk::layouts.master')

@section('title', 'Titan Talk – Conversations')

@section('content')
    <div class="row mb-3">
        <div class="col-md-6">
            <h3 class="page-title">Titan Talk – Conversations</h3>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <input type="text" name="q"
                           class="form-control"
                           placeholder="Search external ref (phone, chat id)"
                           value="{{ request('q') }}">
                </div>
                <div class="form-group mr-2">
                    <select name="channel" class="form-control">
                        <option value="">All channels</option>
                        <option value="web" {{ request('channel')=='web' ? 'selected' : '' }}>Web</option>
                        <option value="whatsapp" {{ request('channel')=='whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="telegram" {{ request('channel')=='telegram' ? 'selected' : '' }}>Telegram</option>
                        <option value="messenger" {{ request('channel')=='messenger' ? 'selected' : '' }}>Messenger</option>
                        <option value="voice" {{ request('channel')=='voice' ? 'selected' : '' }}>Voice</option>
                        <option value="sms" {{ request('channel')=='sms' ? 'selected' : '' }}>SMS</option>
                        <option value="email" {{ request('channel')=='email' ? 'selected' : '' }}>Email</option>
                    </select>
                </div>

                <div class="form-group mr-2">
                    <input type="number" name="client_id" class="form-control" style="width: 100px"
                           placeholder="Client ID" value="{{ request('client_id') }}">
                </div>
                <div class="form-group mr-2">
                    <input type="number" name="project_id" class="form-control" style="width: 100px"
                           placeholder="Project ID" value="{{ request('project_id') }}">
                </div>
                <div class="form-group mr-2">
                    <input type="number" name="ticket_id" class="form-control" style="width: 100px"
                           placeholder="Ticket ID" value="{{ request('ticket_id') }}">
                </div>
                <div class="form-group mr-2">
                    <input type="number" name="task_id" class="form-control" style="width: 100px"
                           placeholder="Task ID" value="{{ request('task_id') }}">
                </div>
                <div class="form-group mr-2">
                    <input type="number" name="invoice_id" class="form-control" style="width: 100px"
                           placeholder="Invoice ID" value="{{ request('invoice_id') }}">
                </div>

                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Channel</th>
                    <th>External Ref</th>
                    <th>Client ID</th>
                    <th>Lead ID</th>
                    <th>Project ID</th>
                    <th>Ticket ID</th>
                    <th>Task ID</th>
                    <th>Invoice ID</th>
                    <th>Updated</th>
                    <th width="260">Links</th>
                </tr>
                </thead>
                <tbody>
                @forelse($conversations as $conversation)
                    <tr>
                        <td>
                            <a href="{{ route('titantalk.conversations.show', $conversation) }}">
                                #{{ $conversation->id }}
                            </a>
                        </td>
                        <td>{{ $conversation->channel }}</td>
                        <td>{{ $conversation->external_ref }}</td>
                        <td>{{ $conversation->client_id }}</td>
                        <td>{{ $conversation->lead_id }}</td>
                        <td>{{ $conversation->project_id }}</td>
                        <td>{{ $conversation->ticket_id }}</td>
                        <td>{{ $conversation->task_id }}</td>
                        <td>{{ $conversation->invoice_id }}</td>
                        <td>{{ $conversation->updated_at }}</td>
                        <td>
                            <form action="{{ route('titantalk.conversations.update-crm', $conversation) }}"
                                  method="POST" class="form-inline">
                                @csrf
                                @method('PUT')
                                <input type="number" name="client_id"
                                       class="form-control form-control-sm mb-1 mr-1"
                                       style="width: 80px"
                                       placeholder="Client"
                                       value="{{ old('client_id', $conversation->client_id) }}">
                                <input type="number" name="lead_id"
                                       class="form-control form-control-sm mb-1 mr-1"
                                       style="width: 80px"
                                       placeholder="Lead"
                                       value="{{ old('lead_id', $conversation->lead_id) }}">
                                <input type="number" name="project_id"
                                       class="form-control form-control-sm mb-1 mr-1"
                                       style="width: 80px"
                                       placeholder="Project"
                                       value="{{ old('project_id', $conversation->project_id) }}">
                                <input type="number" name="ticket_id"
                                       class="form-control form-control-sm mb-1 mr-1"
                                       style="width: 80px"
                                       placeholder="Ticket"
                                       value="{{ old('ticket_id', $conversation->ticket_id) }}">
                                <input type="number" name="task_id"
                                       class="form-control form-control-sm mb-1 mr-1"
                                       style="width: 80px"
                                       placeholder="Task"
                                       value="{{ old('task_id', $conversation->task_id) }}">
                                <input type="number" name="invoice_id"
                                       class="form-control form-control-sm mb-1 mr-1"
                                       style="width: 80px"
                                       placeholder="Invoice"
                                       value="{{ old('invoice_id', $conversation->invoice_id) }}">
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">No conversations yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $conversations->appends(request()->all())->links() }}
        </div>
    </div>
@endsection
