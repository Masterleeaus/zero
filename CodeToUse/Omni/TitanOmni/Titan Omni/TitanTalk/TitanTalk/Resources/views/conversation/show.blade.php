@extends('titantalk::layouts.master')

@section('title', 'Conversation #'.$conversation->id.' – Titan Talk')

@section('content')
    <div class="row mb-3">
        <div class="col-md-8">
            <h3 class="page-title">
                Conversation #{{ $conversation->id }}
                <small class="text-muted">({{ $conversation->channel }})</small>
            </h3>
            <p class="text-muted mb-0">
                External ref: {{ $conversation->external_ref }}
            </p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('titantalk.conversations.index', request()->only('q','channel','client_id','project_id','lead_id','ticket_id','task_id','invoice_id')) }}"
               class="btn btn-secondary">
                Back to list
            </a>
        </div>
    </div>

    {{-- Titan strip – compact entity badges --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card p-2">
                <div class="d-flex flex-wrap align-items-center">
                    <strong class="mr-2">Linked to:</strong>

                    @if($conversation->client_id)
                        <span class="badge badge-light border mr-2 mb-1">
                            <i class="fa fa-user mr-1"></i>
                            Client #{{ $conversation->client_id }}
                            @if(\Illuminate\Support\Facades\Route::has('clients.show'))
                                <a href="{{ route('clients.show', $conversation->client_id) }}" class="ml-1">view</a>
                            @endif
                        </span>
                    @endif

                    @if($conversation->project_id)
                        <span class="badge badge-light border mr-2 mb-1">
                            <i class="fa fa-briefcase mr-1"></i>
                            Project #{{ $conversation->project_id }}
                            @if(\Illuminate\Support\Facades\Route::has('projects.show'))
                                <a href="{{ route('projects.show', $conversation->project_id) }}" class="ml-1">view</a>
                            @endif
                        </span>
                    @endif

                    @if($conversation->ticket_id)
                        <span class="badge badge-light border mr-2 mb-1">
                            <i class="fa fa-life-ring mr-1"></i>
                            Ticket #{{ $conversation->ticket_id }}
                            @if(\Illuminate\Support\Facades\Route::has('tickets.show'))
                                <a href="{{ route('tickets.show', $conversation->ticket_id) }}" class="ml-1">view</a>
                            @endif
                        </span>
                    @endif

                    @if($conversation->task_id)
                        <span class="badge badge-light border mr-2 mb-1">
                            <i class="fa fa-check-square mr-1"></i>
                            Task #{{ $conversation->task_id }}
                            @if(\Illuminate\Support\Facades\Route::has('tasks.show'))
                                <a href="{{ route('tasks.show', $conversation->task_id) }}" class="ml-1">view</a>
                            @endif
                        </span>
                    @endif

                    @if($conversation->invoice_id)
                        <span class="badge badge-light border mr-2 mb-1">
                            <i class="fa fa-file-invoice-dollar mr-1"></i>
                            Invoice #{{ $conversation->invoice_id }}
                            @if(\Illuminate\Support\Facades\Route::has('invoices.show'))
                                <a href="{{ route('invoices.show', $conversation->invoice_id) }}" class="ml-1">view</a>
                            @endif
                        </span>
                    @endif

                    @if(! $conversation->client_id && ! $conversation->project_id && ! $conversation->ticket_id && ! $conversation->task_id && ! $conversation->invoice_id)
                        <span class="text-muted">No entities linked yet.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Entity Links</div>
                <div class="card-body">
                    <form action="{{ route('titantalk.conversations.update-crm', $conversation) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Client ID</label>
                            <input type="number" name="client_id" class="form-control"
                                   value="{{ old('client_id', $conversation->client_id) }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Lead ID</label>
                            <input type="number" name="lead_id" class="form-control"
                                   value="{{ old('lead_id', $conversation->lead_id) }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Project ID</label>
                            <input type="number" name="project_id" class="form-control"
                                   value="{{ old('project_id', $conversation->project_id) }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Ticket ID</label>
                            <input type="number" name="ticket_id" class="form-control"
                                   value="{{ old('ticket_id', $conversation->ticket_id) }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Task ID</label>
                            <input type="number" name="task_id" class="form-control"
                                   value="{{ old('task_id', $conversation->task_id) }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Invoice ID</label>
                            <input type="number" name="invoice_id" class="form-control"
                                   value="{{ old('invoice_id', $conversation->invoice_id) }}">
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Save</button>
                    </form>

                    <hr>

                    <h6>Quick links</h6>
                    <div class="btn-group btn-group-sm flex-wrap" role="group">
                        @if($conversation->client_id && \Illuminate\Support\Facades\Route::has('clients.show'))
                            <a href="{{ route('clients.show', $conversation->client_id) }}"
                               class="btn btn-outline-secondary">
                                <i class="fa fa-user"></i> View Client
                            </a>
                        @endif

                        @if($conversation->project_id && \Illuminate\Support\Facades\Route::has('projects.show'))
                            <a href="{{ route('projects.show', $conversation->project_id) }}"
                               class="btn btn-outline-secondary">
                                <i class="fa fa-briefcase"></i> View Project
                            </a>
                        @endif

                        @if($conversation->ticket_id && \Illuminate\Support\Facades\Route::has('tickets.show'))
                            <a href="{{ route('tickets.show', $conversation->ticket_id) }}"
                               class="btn btn-outline-secondary">
                                <i class="fa fa-life-ring"></i> View Ticket
                            </a>
                        @endif

                        @if($conversation->task_id && \Illuminate\Support\Facades\Route::has('tasks.show'))
                            <a href="{{ route('tasks.show', $conversation->task_id) }}"
                               class="btn btn-outline-secondary">
                                <i class="fa fa-check-square"></i> View Task
                            </a>
                        @endif

                        @if($conversation->invoice_id && \Illuminate\Support\Facades\Route::has('invoices.show'))
                            <a href="{{ route('invoices.show', $conversation->invoice_id) }}"
                               class="btn btn-outline-secondary">
                                <i class="fa fa-file-invoice-dollar"></i> View Invoice
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Messages</div>
        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            @forelse($conversation->messages as $msg)
                <div class="mb-3">
                    <div>
                        <strong>{{ ucfirst($msg->sender) }}</strong>
                        <small class="text-muted">{{ $msg->created_at }}</small>
                    </div>
                    <div class="p-2 mt-1 {{ $msg->sender === 'bot' ? 'bg-light' : 'bg-white' }}"
                         style="border-radius: 4px;">
                        <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">
{{ $msg->text }}</pre>
                    </div>
                </div>
            @empty
                <p class="text-muted">No messages yet.</p>
            @endforelse
        </div>
    </div>
@endsection
