@extends('titantalk::layouts.master')

@section('title', 'Titan Talk – Dashboard')

@section('content')
    <div class="row mb-3">
        <div class="col-md-8">
            <h3 class="page-title">Titan Talk – Dashboard</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total conversations</h6>
                    <h3>{{ $totalConversations }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Conversations – last 7 days</h6>
                    <h3>{{ $conversationsLast7 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Messages today</h6>
                    <h3>{{ $messagesToday }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Conversations by channel</div>
                <div class="card-body">
                    @if(empty($byChannel))
                        <p class="text-muted mb-0">No conversations yet.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($byChannel as $channel => $count)
                                <li><strong>{{ $channel }}</strong>: {{ $count }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Conversations – last 30 days</div>
                <div class="card-body">
                    @if(empty($dailyConversations))
                        <p class="text-muted mb-0">No conversations yet.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Conversations</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($dailyConversations as $day => $count)
                                <tr>
                                    <td>{{ $day }}</td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
