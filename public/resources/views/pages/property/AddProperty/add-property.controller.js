angular
    .module('myApp.property.addProperty')
    .controller('AddPropertyCtrl',  function($scope, $http, config, $route, Properties, $window) {

        // Initialize the variable

        var d = new Date();
        var strDate = d.getDate() + "-" + (d.getMonth()+1) + "-" + d.getFullYear();
        $("#available").val(strDate);

        $scope.host = $window.location.protocol + "//" + $window.location.host + $window.location.pathname + "#!";

        $scope.selectAll = false;

        $scope.checkboxes =
            [{id: 'find_a_property', label: 'Find A Property', checked: false},
                {id: 'globrix', label: 'Globrix', checked: false},
                {id: 'gumtree', label: 'Gumtree', checked: false},
                {id: 'home_hunter', label: 'Home Hunter', checked: false},
                {id: 'homes24', label: 'Homes24', checked: false},
                {id: 'look_a_property', label: 'Look 4 A Property', checked: false},
                {id: 'movehut', label: 'Movehut', checked: false},
                {id: 'market', label: 'On the market (Agents mutual)', checked: false},
                {id: 'primelocation', label: 'Primelocation', checked: false},
                {id: 'property_finder', label: 'Property finder', checked: false},
                {id: 'property_index',  label: 'Property Index', checked: false},
                {id: 'propertylive',  label: 'Propertylive', checked: false},
                {id: 'rightmove', label: 'Rightmove', checked: false},
                {id: 'rightmove_overseas',  label: 'Rightmove Overseas', checked: false},
                {id: 'zoomf', label: 'Zoomf', checked: false},
                {id: 'zoomf', label: 'Zoopla (Think Property)', checked: false},
                {id: 'zoopla_overseas', label: 'Zoopla Overseas', checked: false}];

        $scope.cbChecked = function(){
            $scope.selectAll = true;
            angular.forEach($scope.checkboxes, function(v, k) {
                if(!v.checked){
                    $scope.selectAll = false;
                }
            });
        }

        $scope.toggleAll = function() {
            var bool = true;
            if ($scope.selectAll) {
                bool = false;
            }
            angular.forEach($scope.checkboxes, function(v, k) {
                v.checked = !bool;
                $scope.selectAll = !bool;
            });
        }

        $scope.available = strDate;
        $scope.for = "2";
        $scope.let_type = "1";
        $scope.service_type = "1";
        $scope.pets = "2";
        $scope.smoking = "2";
        $scope.category = "5";
        $scope.price_rent = "0.00";
        $scope.currency = "1";
        $scope.property_type = "1";
        $scope.contract = "Sole Agency";
        $scope.finder_fee = "0.00";
        $scope.finder_fee_unit = "P";
        $scope.listing_commission_unit = "P";
        $scope.selling_commission_unit = "P";
        $scope.management_fee = "0.00";
        $scope.management_fee_unit = "F";
        $scope.contry = "United Kingdom";
        $scope.beds = "0";
        $scope.baths = "1";
        $scope.receptions = "0";
        $scope.outbuildings = "0";
        $scope.branch = "1";
        $scope.negotiator = "1";
        $scope.agent_does_viewing = "1";
        $scope.streetview = "solid";
        $scope.agreedCommission = "0";
        $scope.council = "0";
        $scope.publish = "2";
        $scope.status = "To let";
        $scope.portal_publish = "2";
        $scope.portal_status = "1";
        $scope.portal_for = "1";
        $scope.portal_type = "1";

        $scope.tinymceOptions = {
            onChange: function(e) {
                // put logic here for keypress and cut/paste changes
            },
            inline: false,
            plugins : 'advlist autolink link image lists charmap print preview',
            skin: 'lightgray',
            theme : 'modern'
        };

        $scope.createProperty = function(isValid) {

            if( ! isValid )
            {

            } else {
                $scope.propertyData = new Properties();
                // Property Info
                $scope.propertyData.landlord = $scope.landlord;
                $scope.propertyData.for = $scope.for;
                $scope.propertyData.let_type = $scope.let_type;
                $scope.propertyData.service_type = $scope.service_type;
                $scope.propertyData.available = $scope.available;
                $scope.propertyData.furniture = $scope.furniture;
                $scope.propertyData.pets = $scope.pets;
                $scope.propertyData.smoking = $scope.smoking;
                $scope.propertyData.category = $scope.category;
                $scope.propertyData.property_type = $scope.property_type;
                $scope.propertyData.internal_reference = $scope.internal_reference;
                $scope.propertyData.student_let = $scope.student_let;

                $scope.propertyData.price_deposit = $scope.price_deposit;
                $scope.propertyData.deposit_unit = $scope.deposit_unit;

                $scope.propertyData.price_rent2 = $scope.price_rent2;
                $scope.propertyData.currency2 = $scope.currency2;


                $scope.propertyData.price_rent = $scope.price_rent;
                $scope.propertyData.currency = $scope.currency;
                $scope.propertyData.renewal_fee = $scope.renewal_fee;
                $scope.propertyData.price_qualifier = $scope.price_qualifier;
                $scope.propertyData.contract = $scope.contract;
                $scope.propertyData.finder_fee = $scope.finder_fee;
                $scope.propertyData.finder_fee_unit = $scope.finder_fee_unit;
                $scope.propertyData.listing_commission = $scope.listing_commission;
                $scope.propertyData.listing_commission_unit = $scope.listing_commission_unit;
                $scope.propertyData.selling_commission = $scope.selling_commission;
                $scope.propertyData.selling_commission_unit = $scope.selling_commission_unit;
                $scope.propertyData.management_fee = $scope.management_fee;
                $scope.propertyData.management_fee_unit = $scope.management_fee_unit;
                $scope.propertyData.addr_postcode = $scope.addr_postcode;
                $scope.propertyData.property_no = $scope.property_no;
                $scope.propertyData.property_name = $scope.property_name;
                $scope.propertyData.add1 = $scope.add1;
                $scope.propertyData.add2 = $scope.add2;
                $scope.propertyData.area = $scope.area;
                $scope.propertyData.county = $scope.county;
                $scope.propertyData.country = $scope.country;
                $scope.propertyData.beds = $scope.beds;
                $scope.propertyData.baths = $scope.baths;
                $scope.propertyData.receptions = $scope.receptions;
                $scope.propertyData.parking = $scope.parking;
                $scope.propertyData.livingspace = $scope.livingspace;
                $scope.propertyData.landsize = $scope.landsize;
                $scope.propertyData.outbuildings = $scope.outbuildings;
                $scope.propertyData.features = $scope.features;
                //Property description
                $scope.propertyData.tinymceModel = $scope.tinymceModel;
                // Property More info
                $scope.propertyData.branch = $scope.branch;
                $scope.propertyData.negotiator = $scope.negotiator;
                $scope.propertyData.agent_does_viewing = $scope.agent_does_viewing;
                $scope.propertyData.comments = $scope.comments;
                $scope.propertyData.DSSaccepted = $scope.DSSaccepted;
                $scope.propertyData.DSSrejected = $scope.DSSrejected;
                $scope.propertyData.councilTaxBand = $scope.councilTaxBand;
                $scope.propertyData.councilTaxAmount = $scope.councilTaxAmount;
                $scope.propertyData.gasmeterReading = $scope.gasmeterReading;
                $scope.propertyData.eletricMeterReading = $scope.eletricMeterReading;
                $scope.propertyData.period = $scope.period;
                $scope.propertyData.stva = $scope.stva;
                $scope.propertyData.tenure = $scope.tenure;
                $scope.propertyData.streetview = $scope.streetview;
                $scope.propertyData.agreedCommission = $scope.agreedCommission;
                $scope.propertyData.council = $scope.council;
                $scope.propertyData.council_band = $scope.council_band;
                $scope.propertyData.freeholder = $scope.freeholder;
                $scope.propertyData.freeholder_contact = $scope.freeholder_contact;
                $scope.propertyData.freeholder_address = $scope.freeholder_address;
                $scope.propertyData.occup_name = $scope.occup_name;
                $scope.propertyData.occup_email = $scope.occup_email;
                $scope.propertyData.occup_mobile = $scope.occup_mobile;
                // Property Phtos/Floorplans
                $scope.propertyData.floorplans_file = $("#floorplans_file").val();
                $scope.propertyData.what_rep = $scope.what_rep;
                $scope.propertyData.report_file = $scope.report_file;
                $scope.propertyData.epc_url = $scope.epc_url;
                $scope.propertyData.show_video_tour = $scope.show_video_tour;
                $scope.propertyData.video_tour = $scope.video_tour;
                $scope.propertyData.attachments_file = $("#attachments_file").val();
                // Property Publish
                $scope.propertyData.publish = $scope.publish;
                $scope.propertyData.status = $scope.status;
                $scope.propertyData.portal_publish = $scope.portal_publish;
                $scope.propertyData.portal_status = $scope.portal_status;
                $scope.propertyData.portal_for = $scope.portal_for;
                $scope.propertyData.new_home = $scope.new_home;
                $scope.propertyData.rm_add = $scope.rm_add;
                $scope.propertyData.vt_url = $scope.vt_url;
                $scope.propertyData.vt_url2 = $scope.vt_url2;
                $scope.propertyData.pb_url = $scope.pb_url;
                $scope.propertyData.admin_fee = $scope.admin_fee;
                $scope.propertyData.portal_summary = $scope.portal_summary;
                $scope.propertyData.portal_details = $scope.portal_details;
                $scope.propertyData.portals = $scope.checkboxes;
                $scope.propertyData.photographs_file = $("#photographs_file").val();

                $scope.propertyData.$save(function(data){
                    $window.location.href = $scope.host + "/property";
                });
            }
        };
    });