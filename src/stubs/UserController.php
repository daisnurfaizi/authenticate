<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Ijp\Auth\Repositories\UserRepository;
use Ijp\Auth\Service\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;


    public function __construct()
    {
        $this->userService = new UserService(new UserRepository(new User()));
    }
    // update user
    public function update(Request $request, $id)
    {
        return $this->userService->updateUser(data: $request, id: $id);
    }

    // get user by id
    public function show($id)
    {
        return $this->userService->getUserById(id: $id);
    }

    // get all users
    public function showAll(Request $request)
    {
        return $this->userService->getAllUser(request: $request);
    }
}
