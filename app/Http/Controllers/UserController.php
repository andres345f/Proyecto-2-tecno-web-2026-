<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Inertia\Inertia;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $search = request('search');
        $role = request('role');

        $usuarios = $this->userService->listarUsuarios($search, $role);

        return Inertia::render('Usuario/Index', [
            'usuarios' => $usuarios,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Usuario/Create');
    }

    public function store(StoreUserRequest $request)
    {
        $this->userService->crearUsuario($request->validated());

        return redirect()->route('usuarios.index');
    }

    public function show(User $user)
    {
        return Inertia::render('Usuario/Show', [
            'usuario' => $user,
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Usuario/Edit', [
            'usuario' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->actualizarUsuario($user, $request->validated());

        return redirect()->route('usuarios.index');
    }

    public function destroy(User $user)
    {
        try {
            $this->userService->eliminarUsuario($user, auth()->id());
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('usuarios.index');
    }
}
