<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Landlord;

class LandlordsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
        public function index()
        {
                $allLandlords = Landlord::with(['internal', 'bank', 'attachment'])->get();

                return response()->json($allLandlords);
        }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
        public function show($id)
        {
                $landlord = Landlord::with(['internal', 'bank', 'attachment'])->find($id);

                if (!$landlord)
                {
                        return $this->error('Landlord not found!');
                }

                return response()->json($landlord);
        }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
