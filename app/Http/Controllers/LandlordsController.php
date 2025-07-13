<?php 

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Landlord;

class LandlordsController extends Controller {

	protected $landlordData;

	public function __construct() 
	{
		$this->landlordData = array(
			'info' => array(),
			'internals' => array(),
			'banks' => array(),
			'attachments' => array(),
		);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$allLandlords = DB::table('landlords')
		    ->join('landlords_internals', 'landlords.id', '=', 'landlords_internals.landlords_id')
		    ->join('landlords_banks', 'landlords.id', '=', 'landlords_banks.landlords_id')
		    ->join('landlords_attachments', 'landlords.id', '=', 'landlords_attachments.landlords_id')

		    ->select('landlords.*', 'landlords_internals.*', 
		    	'landlords_banks.*', 'landlords_attachments.*')
		    ->get();
		
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
		$response_landlord = array();
        $landlord = Landlord::find($id);

        if (!$landlord) 
       	{
       		return $this->error('Landlord not found!');
       	} 
       	else 
       	{
       		array_push($this->landlordData['info'], $landlord->toArray());

			$landlord_internal = DB::table('landlords_internals')
			        		->where('landlords_id', $id)->first();

			array_push($this->landlordData['internals'], $landlord_internal->toArray());
			/*
			$response_landlord['internal_other_label'] = $landlord_internal->internal_other_label;
			$response_landlord['internal_other_status'] = $landlord_internal->internal_other_status;
			$response_landlord['internal_other_branch'] = $landlord_internal->internal_other_branch;
			$response_landlord['internal_other_negotiator'] = $landlord_internal->internal_other_negotiator;
			$response_landlord['internal_other_lead'] = $landlord_internal->internal_other_lead;
			$response_landlord['internal_comment'] = $landlord_internal->internal_comment;
			$response_landlord['internal_other_info'] = $landlord_internal->internal_other_info;
			*/
			$landlord_bank = DB::table('landlords_banks')
			        		->where('landlords_id', $id)->first();

			array_push($this->landlordData['banks'], $landlord_bank->toArray());
			/*
			$response_landlord['bank_body'] = $landlord_bank->bank_body;
			$response_landlord['bank_account_no'] = $landlord_bank->bank_account_no;
			$response_landlord['bank_sort_code'] = $landlord_bank->bank_sort_code;
			$response_landlord['bank_accunt_name'] = $landlord_bank->bank_accunt_name;
			$response_landlord['bank_branch_addr_first'] = $landlord_bank->bank_branch_addr_first;
			$response_landlord['bank_branch_addr_second'] = $landlord_bank->bank_branch_addr_second;
			$response_landlord['bank_branch_town'] = $landlord_bank->bank_branch_town;
			$response_landlord['bank_branch_city'] = $landlord_bank->bank_branch_city;
			$response_landlord['bank_branch_postcode'] = $landlord_bank->bank_branch_postcode;
			$response_landlord['bank_branch_country'] = $landlord_bank->bank_branch_country;
			*/
			$landlord_attachment = DB::table('landlords_attachments')
			        		->where('landlords_id', $id)->first();

			array_push($this->landlordData['attachments'], $landlord_attachment->toArray());
			/*
			$response_landlord['file_format'] = $landlord_attachment->file_format;
			$response_landlord['file_name'] = $landlord_attachment->file_name;
			*/
       	}
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
