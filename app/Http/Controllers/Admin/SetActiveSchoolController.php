<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Schools\App\Models\School;

class SetActiveSchoolController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);
        session(['active_school_id' => $request->school_id]);
        return redirect()->back();
    }
}
