@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Roles & Permissions'))

@section('content')
    <div class="page-wrapper">
        <div class="container-xl">
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">{{ __('Roles & Permissions') }}</h2>
                    </div>
                    <div class="col-auto ms-auto">
                        <button
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#createRoleModal"
                        >
                            {{ __('New Role') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="page-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Permissions') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>
                                            <strong>{{ $role->name }}</strong>
                                        </td>
                                        <td>
                                            @forelse ($role->permissions as $perm)
                                                <span class="badge bg-azure-lt me-1">{{ $perm->name }}</span>
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button
                                                    class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editRoleModal-{{ $role->id }}"
                                                >
                                                    {{ __('Edit') }}
                                                </button>
                                                @if (! in_array($role->name, ['user', 'admin', 'super_admin']))
                                                    <form
                                                        action="{{ route('titan.admin.roles.destroy', $role) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('{{ __('Delete this role?') }}')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                        >
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Role Modal --}}
    <div
        class="modal modal-blur fade"
        id="createRoleModal"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form
                    action="{{ route('titan.admin.roles.store') }}"
                    method="POST"
                >
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Create Role') }}</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Role Name') }}</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                required
                            />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Permissions') }}</label>
                            <div class="row g-2">
                                @foreach ($permissions as $perm)
                                    <div class="col-4">
                                        <label class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $perm->name }}"
                                            />
                                            <span class="form-check-label">{{ $perm->name }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-link link-secondary"
                            data-bs-dismiss="modal"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary ms-auto"
                        >
                            {{ __('Create Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Role Modals --}}
    @foreach ($roles as $role)
        <div
            class="modal modal-blur fade"
            id="editRoleModal-{{ $role->id }}"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form
                        action="{{ route('titan.admin.roles.update', $role) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Edit Role: :name', ['name' => $role->name]) }}</h5>
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                            ></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-2">
                                @foreach ($permissions as $perm)
                                    <div class="col-4">
                                        <label class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $perm->name }}"
                                                @checked($role->permissions->contains('name', $perm->name))
                                            />
                                            <span class="form-check-label">{{ $perm->name }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-link link-secondary"
                                data-bs-dismiss="modal"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary ms-auto"
                            >
                                {{ __('Save Permissions') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
