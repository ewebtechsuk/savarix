<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicantRequest;
use App\Http\Requests\UpdateApplicantRequest;
use App\Http\Resources\ApplicantResource;
use App\Models\Applicant;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::query();

        if ($search = $request->get('q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $applicants = $query->paginate(20);

        return ApplicantResource::collection($applicants);
    }

    public function store(StoreApplicantRequest $request)
    {
        $applicant = Applicant::create($request->validated());

        return new ApplicantResource($applicant);
    }

    public function show(Applicant $applicant)
    {
        return new ApplicantResource($applicant);
    }

    public function update(UpdateApplicantRequest $request, Applicant $applicant)
    {
        $applicant->update($request->validated());

        return new ApplicantResource($applicant);
    }

    public function destroy(Applicant $applicant)
    {
        $applicant->delete();

        return response()->json([], 204);
    }
}
