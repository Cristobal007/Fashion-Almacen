<?php

namespace Modules\User\Http\Controllers;

use Modules\User\DataTables\UsersDataTable;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Upload\Entities\Upload;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class UsersController extends Controller
{
    public function index(UsersDataTable $dataTable) {
        abort_if(Gate::denies('access_user_management'), 403);

        return $dataTable->render('user::users.index');
    }


    public function create() {
        abort_if(Gate::denies('access_user_management'), 403);

        return view('user::users.create');
    }


    public function store(Request $request) {
        abort_if(Gate::denies('access_user_management'), 403);


        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255|confirmed'
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->is_active
        ]);

        $user->assignRole($request->role);

        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            $file->storeAs('temp/dropzone', $filename);

            $user->addMedia(storage_path('app/temp/dropzone/'.$filename))
                ->toMediaCollection('avatars');

        }

        toast("User Created & Assigned '$request->role' Role!", 'success');

        return redirect()->route('users.index');
    }


    public function edit(User $user) {
        abort_if(Gate::denies('access_user_management'), 403);

        return view('user::users.edit', compact('user'));
    }


    public function update(Request $request, User $user) {
        abort_if(Gate::denies('access_user_management'), 403);

        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'is_active' => $request->is_active,
        ]);

        $user->syncRoles($request->role);

        if ($request->hasFile('image')) {
            $user->clearMediaCollection('avatars');

            try {
                $user->addMediaFromRequest('image')->toMediaCollection('avatars');
            } catch (\Exception $e) {
                // Manejar la excepción si es necesario
                return $e->getMessage();
            }
        }



        toast("Usuario Actualizado y Asignado '$request->role' Role!", 'info');

        return redirect()->route('users.index');
    }



    public function destroy(User $user) {
        abort_if(Gate::denies('access_user_management'), 403);

        $user->delete();

        toast('Usuario Eliminado!', 'warning');

        return redirect()->route('users.index');
    }
}
