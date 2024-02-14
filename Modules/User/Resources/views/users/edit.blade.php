@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('third_party_stylesheets')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet"/>
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
          rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuarios</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid mb-4">
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('patch')
            <div class="row">
                <div class="col-lg-12">
                    @include('utils.alerts')
                    <div class="form-group">
                        <button class="btn btn-primary">Actualizar Usuario <i class="bi bi-check"></i></button>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="name">Nombre <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="name" required value="{{ $user->name }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                                        <input class="form-control" type="email" name="email" required value="{{ $user->email }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="role">Rol <span class="text-danger">*</span></label>
                                <select class="form-control" name="role" id="role" required>
                                    @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                        @if (auth()->user()->hasRole(2) || $role->id !== 2)
                                        <option {{ $user->hasRole($role->name) ? 'selected' : '' }} value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="is_active">Estado <span class="text-danger">*</span></label>
                                <select class="form-control" name="is_active" id="is_active" required>
                                    <option value="1" {{ $user->is_active == 1 ? 'selected' : ''}}>Activo</option>
                                    <option value="2" {{ $user->is_active == 2 ? 'selected' : ''}}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="image">Imagen de Perfil <span class="text-danger">*</span></label>
                                <img style="width: 100px;height: 100px;" class="d-block mx-auto img-thumbnail img-fluid rounded-circle mb-2" src="{{ $user->getFirstMediaUrl('avatars') }}" alt="Imagen de Perfil">
                                <input id="image" type="file" name="image" >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('third_party_scripts')
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script
        src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script
        src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
@endsection

@push('page_scripts')
    <script src="{{ asset('js/dropzone.js') }}"></script>

    <script>

        Dropzone.options.fotoPerfilDropzone = {

            init: function() {

                this.element = document.getElementById('image');

            },

            url: '{{ route('dropzone.upload') }}',

            maxFilesize: 1, // MB
            maxFiles: 1,

            acceptedFiles: '.jpg,.png,.jpeg',

            addRemoveLinks: true,

            success: function(file, response){
                // Guardar nombre de archivo
                let imgName = response.name;

                $('form').append(
                    '<input type="hidden" name="image" value="' + imgName + '">'
                );

            },

            removedfile: function(file) {

                // Eliminar archivo de BD

                $.ajax({
                    type: 'POST',
                    url: '{{ route('dropzone.delete') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        file: file.name
                    }
                });

                $('input[name="image"][value="' + file.name + '"]').remove();

            }

        }

        // Mostrar imagen actual
        let imgUrl = "{{ $user->getFirstMediaUrl('avatars') }}";
        if(imgUrl) {
            document.getElementById('fotoActual').src = imgUrl;
        }

    </script>
@endpush



