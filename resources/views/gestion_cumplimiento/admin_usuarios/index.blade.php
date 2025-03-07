@extends('adminlte::page')

@section('title', 'Administrar Usuarios')

@section('content')
    <hr>
    <div class="card">
        <div class="card-header-title card-header bg-success text-white text-center">
            <h4 class="card-title-description">Administrar Usuarios</h4>
        </div>
        <div class="card-body text-center">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <button type="button" class="btn btn-secondary m-1" data-toggle="modal" data-target="#createUserModal">
                    Crear nuevo usuario
                </button>
                <button type="button" class="btn btn-secondary m-1" data-toggle="modal" data-target="#createRoleModal">
                    Crear nuevo rol
                </button>
                <button type="button" class="btn btn-secondary m-1" data-toggle="modal"
                    data-target="#createPermissionModal">
                    Crear un nuevo permiso
                </button>
                <button type="button" class="btn btn-secondary m-1" data-toggle="modal"
                    data-target="#createAuthorizationModal">
                    Crear nueva autorización
                </button>
            </div>
            <div class="divider"></div>
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close text-white" data-dismiss="alert" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close text-white" data-dismiss="alert" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif


            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="usersTable" class="table table-sm table-striped table-bordered text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>Num</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Puesto</th>
                            <th>Rol</th>
                            <th>Permiso</th>
                            <th>Autorización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td class="editable" data-id="{{ $user->id }}" data-column="name">{{ $user->user_name }}
                                </td>
                                <td class="editable" data-id="{{ $user->id }}" data-column="email">{{ $user->email }}
                                </td>
                                <td class="editable" data-id="{{ $user->id }}" data-column="puesto">{{ $user->puesto }}
                                </td>
                                <td>{{ $user->role_name }}</td>
                                <td>{{ $user->permission_name }}</td>
                                <td>{{ $user->authorization_name }}</td>
                                <td>
                                    <div class="d-flex flex-column flex-sm-row justify-content-center">
                                        <button type="button" class="btn btn-warning btn-sm m-1 edit-user-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->user_name }}"
                                            data-email="{{ $user->email }}" data-puesto="{{ $user->puesto }}"
                                            data-toggle="modal" data-target="#editUserModal">
                                            Editar
                                        </button>

                                        <button type="button" class="btn btn-secondary btn-sm m-1 role-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->user_name }}"
                                            data-email="{{ $user->email }}" data-role="{{ $user->role_id ?? 'Sin Rol' }}"
                                            data-toggle="modal" data-target="#roleModal">
                                            Rol
                                        </button>

                                        <button type="button" class="btn btn-secondary btn-sm m-1 area-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->user_name }}"
                                            data-email="{{ $user->email }}" data-toggle="modal" data-target="#areaModal">
                                            Permiso
                                        </button>

                                        <button type="button" class="btn btn-secondary btn-sm m-1 authorization-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->user_name }}"
                                            data-email="{{ $user->email }}" data-toggle="modal"
                                            data-target="#assignAuthorizationModal">
                                            Autorización
                                        </button>

                                        @if ($user->id != 1)
                                            <form action="{{ route('adminUsuarios.destroy', $user->id) }}" method="POST"
                                                class="d-inline delete-user-form">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm m-1 delete-user-btn">
                                                    Borrar
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-danger btn-sm m-1" disabled>
                                                Borrar
                                            </button>
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

    {{-- Modal --}}
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Crear Nuevo Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="create-user-form" action="{{ route('adminUsuarios.register') }}" method="post"
                        class="w-100">
                        @csrf


                        <div class="input-group mb-3">
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="{{ __('adminlte::adminlte.full_name') }}"
                                autofocus>

                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="input-group mb-3">
                            <input type="text" name="puesto"
                                class="form-control @error('puesto') is-invalid @enderror" value="{{ old('puesto') }}"
                                placeholder="Puesto">

                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-briefcase"></span>
                                </div>
                            </div>

                            @error('puesto')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>


                        <div class="input-group mb-3">
                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                placeholder="{{ __('adminlte::adminlte.email') }}">

                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        <div id="email-feedback" class="text-danger"></div>


                        <div class="input-group mb-3">
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="{{ __('adminlte::adminlte.password') }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                    data-target="#password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>

                        </div>
                        <div id="password-requirements" class="text-muted mt-1 d-none">
                            <small id="length" class="text-danger">La contraseña debe tener al menos 8
                                caracteres.</small><br>
                            <small id="uppercase" class="text-danger">La contraseña debe incluir una letra
                                mayúscula.</small><br>
                            <small id="number" class="text-danger">La contraseña debe incluir un número.</small><br>
                            <small id="special" class="text-danger">La contraseña debe incluir un carácter especial
                                (!@#$%^&*).</small>
                        </div>


                        <div class="input-group mb-3">
                            <input type="password" name="password_confirmation" id="password-confirm"
                                class="form-control" placeholder="{{ __('adminlte::adminlte.retype_password') }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                    data-target="#password-confirm">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>

                        </div>
                        <div id="password-feedback" class="mt-1"></div>


                        <div class="d-flex justify-content-center">
                            <button type="submit" id="submit-btn" class="btn btn-block btn-success w-25 text-center"
                                style="display: none;">
                                <span class="fas fa-user-plus"></span>
                                Crear nuevo usuario
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Rol -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createRoleModalLabel">Crear Nuevo Rol</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="createRoleForm" action="{{ route('adminRoles.create') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="roleName">Nombre del Rol</label>
                            <input type="text" name="name" id="roleName" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar Rol</button>
                    </form>

                    <div class="table-responsive  mt-2">
                        <table class="table table-sm  table-bordered text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nombre de Rol</th>
                                    <th>Fecha de Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->created_at }}</td>
                                        <td>
                                            <form action="{{ route('adminRoles.delete', $role->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                                            </form>
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



    <!-- Modal para Crear Permiso -->
    <div class="modal fade" id="createPermissionModal" tabindex="-1" aria-labelledby="createPermissionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPermissionModalLabel">Crear Nueva Permiso</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="createPermissionForm" action="{{ route('adminPermissions.create') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="permissionName">Nombre del Permiso</label>
                            <input type="text" name="name" id="permissionName" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar Permiso</button>
                    </form>

                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nombre Permiso</th>
                                    <th>Fecha de Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    <tr>
                                        <td>{{ $permission->name }}</td>
                                        <td>{{ $permission->created_at }}</td>
                                        <td>
                                            <form action="{{ route('adminPermissions.delete', $permission->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                                            </form>
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






    <!-- Modal para Editar -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit-user-form" action="{{ route('adminUsuarios.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="user_id" id="editUserId">


                        <div class="form-group">
                            <label for="editUserName">Nombre</label>
                            <input type="text" name="name" id="editUserName" class="form-control" required>
                        </div>


                        <div class="form-group">
                            <label for="editUserEmail">Email</label>
                            <input type="email" name="email" id="editUserEmail" class="form-control" required>
                        </div>


                        <div class="form-group">
                            <label for="editUserPuesto">Puesto</label>
                            <input type="text" name="puesto" id="editUserPuesto" class="form-control" required>
                        </div>
                        
                        <div class="container mt-3 p-3 bg-light rounded border">
                            <h4 class="text-center font-weight-bold mb-4">Asignar obligaciones a este usuario</h3>
                            <div class="row text-center container" id="switchContainer">
                                
                            </div>
                        </div>
                        <br>

                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para Rol -->
    <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Gestionar Rol</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true" class="text-white">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="roleForm" action="{{ route('roles.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="model_type" value="App\Models\User">


                        <input type="hidden" name="model_id" id="modelIdRoleInput">


                        <div class="form-group">
                            <label for="userNameEmailRoleInput">Usuario</label>
                            <input type="text" id="userNameEmailRoleInput" class="form-control" readonly>
                        </div>


                        <div class="form-group">
                            <label for="roleSelect">Seleccionar Rol</label>
                            <select id="roleSelect" name="role_id" class="form-control">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para Permiso -->
    <div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="areaModalLabel">Gestionar Permiso</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="areaForm" action="{{ route('permissions.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="model_type" value="App\Models\User">


                        <input type="hidden" name="model_id" id="modelIdInput">


                        <div class="form-group">
                            <label for="userNameEmailInput">Usuario</label>
                            <input type="text" id="userNameEmailInput" class="form-control" readonly>
                        </div>

                        <!-- Select para permisos -->
                        <div class="form-group">
                            <label for="areaSelect">Seleccionar Permiso</label>
                            <select id="areaSelect" name="permission_id" class="form-control">
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Autorización -->
    <div class="modal fade" id="createAuthorizationModal" tabindex="-1" aria-labelledby="createAuthorizationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createAuthorizationModalLabel">Crear Nueva Autorización</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="createAuthorizationForm" action="{{ route('adminAuthorizations.create') }}"
                        method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="authorizationName">Nombre de la Autorización</label>
                            <input type="text" name="name" id="authorizationName" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar Autorización</button>
                    </form>


                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nombre Autorización</th>
                                    <th>Fecha de Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($authorizations as $authorization)
                                    <tr>
                                        <td>{{ $authorization->name }}</td>
                                        <td>{{ $authorization->created_at }}</td>
                                        <td>
                                            <form action="{{ route('adminAuthorizations.delete', $authorization->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                                            </form>
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



    <!-- Modal para Asignar Autorización -->
    <div class="modal fade" id="assignAuthorizationModal" tabindex="-1" aria-labelledby="assignAuthorizationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignAuthorizationModalLabel">Asignar Autorización</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="assignAuthorizationForm" action="{{ route('authorizations.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="model_id" id="modelIdAuthorization">
                        <input type="hidden" name="model_type" value="App\Models\User">

                        <div class="form-group">
                            <label for="userNameAuthorization">Usuario</label>
                            <input type="text" id="userNameAuthorization" class="form-control" readonly>
                        </div>


                        <div class="form-group">
                            <label for="authorizationSelect">Seleccionar Autorización</label>
                            <select id="authorizationSelect" name="authorization_id" class="form-control">
                                @foreach ($authorizations as $authorization)
                                    <option value="{{ $authorization->id }}">{{ $authorization->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




@endsection

@section('js')

    <script>
        const getObligacionesUrl = "{{ url('get-obligaciones') }}";
        const getObligacionesUsuarioUrl = "{{ url('get-obligaciones-usuario') }}";
        const toggleObligacionUrl = "{{ url('toggle-obligacion') }}";
        const getColumnsUrl = "{{ url('get-columns') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.3/parsley.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="{{ asset('js/gestion_obligaciones/admin_users/admin_users.js') }}"></script>


@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('css/detalles/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@stop
