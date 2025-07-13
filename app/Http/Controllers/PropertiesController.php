<?php 

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Property;
use App\Models\PropertyFeatures;
use App\Models\PropertyAttachments;
use App\Models\PropertyEpcs;
use App\Models\PropertyInternals;
use App\Models\PropertyMoreInfo;
use App\Models\PropertyPortals;
use App\Models\PropertyDescription;

class PropertiesController extends Controller {

	protected $propertyData;
	public $property_type_array = array(
		"1" => "A1 Commercial",
		"2" => "A3 Commercial",
		"3" => "Apartment",
		"4" => "Bedsit",
		"5" => "Commercial Shop",
		"6" => "Cottage",
		"7" => "Flat",
		"8" => "Flat share",
		"9" => "Hostel",
		"10" => "Hotel",
		"11" => "House",
		"12" => "House share",
		"13" => "Maisonette",
		"14" => "Office Space",
		"15" => "Olympic property",
		"16" => "Penthouse",
		"17" => "Studio",
		"18" => "Villa",
		"19" => "Warehouse conversion"
	);
        // Mapping of transaction type ids to their labels
        public $transaction_type = array(
                "1" => "Sale",
                "2" => "Let"
        );
	public $portal_status_label = array(
		"1" => "Available",
		"2" => "Under offer",
		"3" => "Sold stc",
		"4" => "Let agreed"
	);
	public $currency_label = array(
		"1" => "£",
		"2" => "$",
		"3" => "€"
	);

	public function __construct() 
	{
		$this->propertyData = array(
			'info' => array(),			
			'features' => array(),
			'description' => array(),
			'moreinfo' => array(),
			'internal' => array(),
			'epc' => array(),
		);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$allProperties = DB::table('properties')
		    ->leftJoin('properties_descriptions', 'properties.id', '=', 'properties_descriptions.property')
		    ->leftJoin('properties_epcs', 'properties.id', '=', 'properties_epcs.property')
		    ->leftJoin('properties_internals', 'properties.id', '=', 'properties_internals.property')
		    ->leftJoin('properties_moreinfos', 'properties.id', '=', 'properties_moreinfos.property')
		    ->leftJoin('properties_info_features', 'properties.id', '=', 'properties_info_features.property')
		    ->leftJoin('properties_portals', 'properties.id', '=', 'properties_portals.property')

		    ->select('properties.*', 'properties_descriptions.*', 'properties_epcs.*', 
		    	'properties_internals.*', 'properties_moreinfos.*', 'properties_info_features.*', 
		    	'properties_portals.*')
		    ->get();
		
		if (count($allProperties) <= 0){
			return $this->error('Properties not found!');
		}else{
			$new_properties = array();
			foreach($allProperties as $property){
				$property->address = $property->add1.", ".$property->add2.", ".$property->area.", ".$property->county.", ".$property->country;

				switch ($property->category){
					case 1:
						$property->category_label = "Residential";
						break;
					case 2:
						$property->category_label = "New Development";
						break;
					case 4:
						$property->category_label = "Commercial";
						break;
					case 5:
						$property->category_label = "Land";
						break;
					case 6:
						$property->category_label = "Overseas";
						break;
				}

				$property->property_type_label = $this->property_type_array[$property->property_type];
                                $property->transaction_type = $this->transaction_type[$property->for];
				$property->portal_status_label = $this->portal_status_label[$property->portal_status];
				$property->currency_label = $this->currency_label[$property->currency];

				$new_properties[] = $property;
			}
			return response()->json($new_properties);
		}



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
	public function store(Request $request)
	{			
		// Property Info
		$data = $request->only(['landlord', 'for', 'let_type', 'service_type', 'available', 'furniture', 'pets', 'smoking', 'category', 'property_type', 'internal_reference', 'student_let', 'price_deposit', 'deposit_unit', 'price_rent2', 'currency2', 'price_rent', 'currency', 'renewal_fee', 'price_qualifier', 'contract', 'finder_fee', 'finder_fee_unit', 'listing_commission', 'listing_commission_unit', 'selling_commission', 'selling_commission_unit', 'management_fee', 'management_fee_unit', 'addr_postcode', 'property_no', 'property_name', 'add1', 'add2', 'area', 'county', 'country', 'beds', 'baths', 'receptions', 'parking', 'livingspace', 'landsize', 'outbuildings']);
		

		foreach ($data as $key => $value) {
			# code...
			$info[$key] = $value;
		}
		
		$property = Property::create( $info );		
		
		// Property attachment files(photographs, floorplan, attachment)
		$attachments_files = $request->only(['photographs_file', 'floorplans_file', 'attachments_file']);
		
		$photograph_files = explode("|", trim($attachments_files['photographs_file']));
		$tmp_floorplan_file = trim($attachments_files['floorplans_file']);
		$tmp_attachment_file = trim($attachments_files['attachments_file']);

		foreach ($photograph_files as $key => $value) {
			if ($value != "") {
				$photFiles['property'] = $property->id;
				$photFiles["section"] = "photograph";
				$photFiles["file_name"] = $value;
				PropertyAttachments::create( $photFiles );
			}
		}
		
		if ($tmp_floorplan_file != "") {
			$floorplan_file["section"] = "floor";
			$floorplan_file['property'] = $property->id;
			$floorplan_file["file_name"] = $tmp_floorplan_file;
			PropertyAttachments::create( $floorplan_file );
		}

		if ($tmp_attachment_file != "") {
			$attachment_file["section"] = "attachment";
			$attachment_file['property'] = $property->id;
			$attachment_file["file_name"] =$tmp_attachment_file;
			PropertyAttachments::create( $attachment_file );
		}

		// Property Features
		$data = $request->only(['features']);

		foreach ($data['features'] as $key => $value) {
			$featuresData[$key] = $value;
		}

		$featuresData['property'] = $property->id;
		PropertyFeatures::create( $featuresData );		

		// Property Descriptions
		$descriptionData = $request->only(['tinymceModel']);
		$descriptionData['property'] = $property->id;
		PropertyDescription::create( $descriptionData );
		
		// Property More info
		$moreinfoData = $request->only(['branch', 'negotiator', 'agent_does_viewing', 'comments', 'DSSaccepted', 'DSSrejected', 'councilTaxBand', 'councilTaxAmount', 'gasmeterReading', 'eletricMeterReading', 'period', 'stva', 'tenure', 'streetview', 'agreedCommission', 'council', 'council_band', 'freeholder', 'freeholder_contact', 'freeholder_address', 'occup_name', 'occup_email', 'occup_mobile']);
		
		$moreinfoData['property'] = $property->id;
		PropertyMoreInfo::create( $moreinfoData );

		// Property EPCs
		$floorplanData = $request->only(['what_rep', 'report_file', 'epc_url', 'show_video_tour', 'video_tour']);
		$floorplanData['property'] = $property->id;
		PropertyEpcs::create( $floorplanData );

		// Property Internals
		$internalData = $request->only(['publish', 'status', 'portal_publish', 'portal_status', 'portal_for', 'portal_type', 'new_home', 'rm_add', 'vt_url', 'vt_url2', 'pb_url', 'admin_fee', 'portal_details', 'portal_summary']);
		$internalData['property'] = $property->id;
		PropertyInternals::create( $internalData );	
		
		// Property Portals
		$data = $request->only(['portals']);
		$portalData['property'] = $property->id;

		foreach ($data['portals'] as $key => $value) {
			$portalData[$value['id']] = $value['checked'];
		}
		$property = PropertyPortals::create( $portalData );
		
		return $this->show($property->id);		
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		
		if (!$this->getProperty($id)) 
			return $this->error('Property not found!');
		else
		    return response()->json($this->propertyData);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if (!$this->getProperty($id)) 
			return $this->error('Property not found!');
		else
		    return response()->json($this->propertyData);
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

	protected function getProperty($id) 
	{
        $property = Property::find($id);
        
        if (!$property) 
       	{
       		return false;
       	} 
       	else 
       	{		
			// Property info       		
			array_push($this->propertyData['info'], $property->toArray());

			// Property features
			$property_info_features = DB::table('properties_info_features')
		        						->where('property', $id)->get();
		    array_push($this->propertyData['features'], $property_info_features->toArray());

		    // Property description
			$property_descriptions = DB::table('properties_descriptions')
		        						->where('property', $id)->get();
		    array_push($this->propertyData['description'], $property_descriptions->toArray());

		    // Property more info	
			$property_descriptions = DB::table('properties_moreinfos')
		        						->where('property', $id)->get();
		    array_push($this->propertyData['moreinfo'], $property_descriptions->toArray());		

		    // Property internal
			$property_internals = DB::table('properties_internals')
		        						->where('property', $id)->get();
		    array_push($this->propertyData['internal'], $property_internals->toArray());	

		    // Property properties_epc
			$property_epc = DB::table('properties_epcs')
		        						->where('property', $id)->get();
		    array_push($this->propertyData['epc'], $property_epc->toArray());
       	}

       	return true;
	}

}
