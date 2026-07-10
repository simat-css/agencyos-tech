<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserService
{
    public function createUser(array $data)
    {
        // Profile Photo Upload
        if (isset($data['profile_photo'])) {
            $data['profile_photo'] = $data['profile_photo']
                ->store('users', 'public');
        }

        // Password Hash
        $data['password'] = Hash::make($data['password']);

        // Remove role before create
        $role = $data['role'] ?? null;
        unset($data['role']);

        // Create User
        $user = User::create($data);

        // Assign Role
        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }


    public function updateUser(User $user, array $data)
    {
        // Upload New Photo
        if (isset($data['profile_photo'])) {

            if ($user->profile_photo) {
                Storage::disk('public')
                    ->delete($user->profile_photo);
            }

            $data['profile_photo'] = $data['profile_photo']
                ->store('users', 'public');
        }


        // Password Update
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }


        // Role Update
        $role = $data['role'] ?? null;
        unset($data['role']);


        $user->update($data);


        if ($role) {
            $user->syncRoles([$role]);
        }


        return $user;
    }


    public function deleteUser(User $user)
    {
        if ($user->profile_photo) {
            Storage::disk('public')
                ->delete($user->profile_photo);
        }

        return $user->delete();
    }
}