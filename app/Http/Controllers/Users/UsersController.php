<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;;
use App\Http\Controllers\Controller;
use App\User;

class UsersController extends Controller
{

	/**
	 * Retrieves basic metadata from all users
	 */
	public function get(Request $request)
	{
		$user = User::where('id', '!=', auth()->id())
			->orderBy('name', 'asc')
			->get();
		return $user;
	}

	/**
	 * Updates user role
	 */
	public function updateRoleByUser(Request $request)
	{
		$user = User::where('email', $request->email)->first();

		switch($request->role) {
			case 'is_norfab_user':
				$role = 'norfabUser';
				if ($request->roleFlag) {
					$user->assignRole($role);
				} else {
					$user->removeRole($role);
				}
				break;
			case 'is_profaba_user':
				$role = 'profabaUser';
				if ($request->roleFlag) {
					$user->assignRole($role);
				} else {
					$user->removeRole($role);
				}
				break;
			default:
				$role = null;
				break;
		}
		
		return response()->json([
			'email' => $request->email,
			'role' => $request->role,
			'roleFlag' => $user->hasRole($role)
		]);
	}
}
