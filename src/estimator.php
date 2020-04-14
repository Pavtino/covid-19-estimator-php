<?php
/**
 *
 * @author Mbalkam Martin <pavtino@gmail.com>
 * @package covid-19
 * @version 1.0
 * @link http://www.github.com/Pavtino
 */

/**
 *
 * This function calculates the time requested
 * @param string  $periodType type of periode, it can be days,weeks or months
 * @param integer $timeToElapse it is the number of periodType
 * @return integer
 */
function numberOfDays($periodType,$timeToElapse)
{
  $days=0;
  switch($periodType) {
       case "days":
         $days = $timeToElapse;
         break;
       case "weeks":
         $days=$timeToElapse*7;
         break;
       case "months":
         $days=$timeToElapse*30;
         break;
       default:
         $days = $timeToElapse;
         break;
    }
    return $days;
}


/**
 *
 * This function is the main function, it makes an estimation of 
 * the  impact of covid-19 according to a determined period
 * @param string $data  is a json formatted data 
 * @return JSON
 */

function covid19ImpactEstimator($data)
{
	
    
    //it is an output variable, it give estimation for both impact and severe impact, it also integrate input data
    $result=array();

    //impact is an array for estimation
    $impact=array();

    //severeImpact is an array for severe estimation
    $severeImpact=array();

    try
    {
       
      //get period type(days, weeks or months)
	  $periodType=$data["periodType"];

      //number of periodType requested 
	  $timeToElapse=$data["timeToElapse"];

	  //number of current reported case of covid-19
	  $reportedCases=$data["reportedCases"];

      //population in a region
	  $population=$data["population"];

	  //total hospital beds in a country
	  $totalHospitalBeds=$data["totalHospitalBeds"];

	  //number of days requested
	  $nbDays=numberOfDays($periodType,$timeToElapse);

      //get number of set in a period $nbDays
      $setOfday=(int)($nbDays/3);

	  //calculate of infected and severe infected 
	  $impact["currentlyInfected"]=$reportedCases*10;
	  $severeImpact["currentlyInfected"]=$reportedCases*50;

	  //calculate of infections By Requested Time and servere  infections By Requested Time    
   
      $impact["infectionsByRequestedTime"]=$impact["currentlyInfected"]*pow(2,$setOfday);
	  $severeImpact["infectionsByRequestedTime"]=$severeImpact["currentlyInfected"]*pow(2,$setOfday);
     
      

     //calculate of  severe Cases By Requested Time impact and severe Cases By Requested Time severe impact
	  $impact["severeCasesByRequestedTime"]=(int)($impact["infectionsByRequestedTime"]*0.15);
	  $severeImpact["severeCasesByRequestedTime"]=(int)($severeImpact["infectionsByRequestedTime"]*0.15);

     //calculate of  hospital Beds By Requested Time impact and  hospital Beds By RequestedTime severe Impact
	  $impact["hospitalBedsByRequestedTime"]=(int)($totalHospitalBeds*0.35-$impact["severeCasesByRequestedTime"]);
	  $severeImpact["hospitalBedsByRequestedTime"]=(int)($totalHospitalBeds*0.35-$severeImpact["severeCasesByRequestedTime"]);

      //calculate of  cases For ICU By Requested Time impact and cases For ICU By RequestedTime severe impact
	  $impact["casesForICUByRequestedTime"]=(int)($impact["infectionsByRequestedTime"]*0.05);
	  $severeImpact["casesForICUByRequestedTime"]=(int)($severeImpact["infectionsByRequestedTime"]*0.05);

      //calculate of cases For Ventilators By Requested Time impact  and  cases For Ventilators By RequestedTime severe impact
	  $impact["casesForVentilatorsByRequestedTime"]=(int)($impact["infectionsByRequestedTime"]*0.02);
	  $severeImpact["casesForVentilatorsByRequestedTime"]=(int)($severeImpact["infectionsByRequestedTime"]*0.02);

      //calculate of  dollars In Flight impact 
	  $impact["dollarsInFlight"]=(int)(($impact["infectionsByRequestedTime"]*$data["region"]["avgDailyIncomePopulation"]*$data["region"]["avgDailyIncomeInUSD"])/$nbDays);

	   //calculate of  dollars In Flight severe impact 
	   $severeImpact["dollarsInFlight"]=(int)(($severeImpact["infectionsByRequestedTime"]*$data["region"]["avgDailyIncomePopulation"]*$data["region"]["avgDailyIncomeInUSD"])/$nbDays);

	   // contruction of output result with input data, impact and severe impact
	   $result=array("data"=>$data,"impact"=>$impact,"severeImpact"=>$severeImpact);
    }
    catch(Exception $e){

        echo $e->getMessage();//display error and stop execution
    }
   

	return $result;
   

}
 
 
// input data
$data = '{
 	"region": 
      {"name": "Africa",
      "avgAge": 19.7,
      "avgDailyIncomeInUSD": 5,
      "avgDailyIncomePopulation": 0.71
      },
    "periodType": "days",
    "timeToElapse": 58,
    "reportedCases": 674,
    "population": 66622705,
    "totalHospitalBeds": 1380614
}'; 

//Extract data from json object to associative array
      $data=json_decode($data,true);
      //Get the last JSON error
      $jsonError = json_last_error();

       //If an error exists.
       if($jsonError!= JSON_ERROR_NONE)
       {
         throw new Exception('Could not decode JSON!Verify if:<br/> there is not Unexpected control character found <br/>Or if it is Malformed JSON');
       }
//display results
echo json_encode(covid19ImpactEstimator($data));

?>