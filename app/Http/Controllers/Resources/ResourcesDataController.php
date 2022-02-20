<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class ResourcesDataController extends Controller
{
	public function download(Request $request)
	{
		$file = strtolower($request->input('file'));
		$is_norfab_file = strpos($file, 'norfab/');
		$is_profaba_file = strpos($file, 'profaba/');
		
		$user = $request->user();
		if ((
			$is_norfab_file && !$user->hasRole('norfabUser') && !$user->hasRole('admin')
		) || (
			$is_profaba_file && !$user->hasRole('profabaUser') && !$user->hasRole('admin')
		)) {
			return abort(403);
		}

		return Storage::download($file);
	}
}
