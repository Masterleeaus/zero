@extends('titantalk::layouts.master')

@section('title', 'Titan Talk – Voice Bots')

@section('content')
    <div class="row mb-3">
        <div class="col-md-6">
            <h3 class="page-title">Titan Talk – Voice Bots</h3>
        </div>
        <div class="col-md-6 text-right">
            <a class="btn btn-success" href="{{ route('titantalk.voice-bots.create') }}">Create Voice Bot</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Provider</th>
                    <th>External ID</th>
                    <th>Active</th>
                    <th>Created</th>
                    <th width="150">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($bots as $bot)
                    <tr>
                        <td>{{ $bot->id }}</td>
                        <td>{{ $bot->name }}</td>
                        <td>{{ $bot->provider }}</td>
                        <td>{{ $bot->external_id }}</td>
                        <td>{{ $bot->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $bot->created_at }}</td>
                        <td>
                            <a href="{{ route('titantalk.voice-bots.edit', $bot) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('titantalk.voice-bots.destroy', $bot) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this voice bot?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No voice bots yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $bots->links() }}
        </div>
    </div>
@endsection
