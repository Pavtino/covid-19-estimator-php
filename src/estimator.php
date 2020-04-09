<?php
/**
 *
 * @author Mbalkam Martin <pavtino@gmail.com>
 * @package covid-19-estimator
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
 * @param JSON $data  is a json object 
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
        //Extract data from json object to associative array
      $data=json_decode($data,true);

      //Get the last JSON error
      $jsonError = json_last_error();

       //If an error exists.
       if($jsonError!= JSON_ERROR_NONE)
       {
         throw new Exception('Could not decode JSON!Verify if:<br/> there is not Unexpected control character found <br/>Or if it is Malformed JSON');
       }

      //get period type(days, weeks or months)
	  $periodeType=$data["periodType"];

      //number of periodType requested 
	  $timeToElapse=$data["timeToElapse"];

	  //number of current reported case of covid-19
	  $reportedCases=$data["reportedCases"];

      //population in a region
	  $population=$data["population"];

	  //total hospital beds in a country
	  $totalHospitalBeds=$data["totalHospitalBeds"];

	  //number of days requested
	  $nbDays=numberOfDays($periodeType,$timeToElapse);

      //get number of set in a period $nbDays
      $setOfday=(int)($nbDays/3);

	  //calculate of infected and severe infected 
	  $impact["currentlyInfected"]=$reportedCases*10;
	  $severeImpact["currentlyInfected"]=$reportedCases*50;
   
      //calculate of infections By Requested Time and servere  infections By Requested Time
	  $impact["infectionsByRequestedTime"]=$impact["currentlyInfected"]*pow(2,$setOfday);
	  $severeImpact["infectionsByRequestedTime"]=$severeImpact["currentlyInfected"]*pow(2,$setOfday);

     //calculate of  severe Cases By Requested Time impact and severe Cases By Requested Time severe impact
	  $impact["severeCasesByRequestedTime"]=$impact["infectionsByRequestedTime"]*0.15;
	  $severeImpact["severeCasesByRequestedTime"]=$severeImpact["infectionsByRequestedTime"]*0.15;

     //calculate of  hospital Beds By Requested Time impact and  hospital Beds By RequestedTime severe Impact
	  $impact["hospitalBedsByRequestedTime"]=$totalHospitalBeds*0.35-$impact["severeCasesByRequestedTime"];
	  $severeImpact["hospitalBedsByRequestedTime"]=$totalHospitalBeds*0.35-$severeImpact["severeCasesByRequestedTime"];

      //calculate of  cases For ICU By Requested Time impact and cases For ICU By RequestedTime severe impact
	  $impact["casesForICUByRequestedTime"]=$impact["infectionsByRequestedTime"]*0.05;
	  $severeImpact["casesForICUByRequestedTime"]=$severeImpact["infectionsByRequestedTime"]*0.05;

      //calculate of cases For Ventilators By Requested Time impact  and  cases For Ventilators By RequestedTime severe impact
	  $impact["casesForVentilatorsByRequestedTime"]=$impact["infectionsByRequestedTime"]*0.02;
	  $severeImpact["casesForVentilatorsByRequestedTime"]=$severeImpact["infectionsByRequestedTime"]*0.02;

      //calculate of  dollars In Flight impact 
	  $impact["dollarsInFlight"]=$impact["infectionsByRequestedTime"]*$data["region"]["avgDailyIncomePopulation"]*$data["region"]["avgDailyIncomeInUSD"]*$nbDays;

	   //calculate of  dollars In Flight severe impact 
	   $severeImpact["dollarsInFlight"]=$severeImpact["infectionsByRequestedTime"]*$data["region"]["avgDailyIncomePopulation"]*$data["region"]["avgDailyIncomeInUSD"]*$nbDays;

	   // contruction of output result with input data, impact and severe impact
	   $result=["data"=>$data,"impact"=>$impact,"severeImpact"=>$severeImpact];
    }
    catch(Exception $e){

        echo $e->getMessage();//display error and stop execution
    }
   

	return json_encode($result);
   

}
 /*In this section we get input data 
  *from file
  */
// path to our JSON file
$url = 'data.json'; 
// put the contents of the file into a variable
$data = file_get_contents($url); 

//put result of estimation into variable
 $outPutResults=covid19ImpactEstimator($data);
 //display results
  echo $outPutResults;
?>
