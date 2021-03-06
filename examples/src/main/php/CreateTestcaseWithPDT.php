<?php

// WSDL caching. 0(false) = off. 1(true) = on 
ini_set('soap.wsdl_cache_enable',0); 
ini_set('soap.wsdl_cache_ttl',0); 

// login class for setting login parameters 
class login 
{ 
public $username; 
public $password; 
} 
// remoteCriteria class for setting searchCriteria parameters 
class remoteCriteria 
{ 
public $searchName; 
public $searchOperation; 
public $searchValue; 
} 
// getTestCaseTreesByCriteria class for setting getProjectsByCriteria parameters 
class getTestCaseTreesByCriteria 
{ 
public $searchCriterias; 
public $returnAllDataFlag; 
public $token; 
} 
// remoteTestcase class for setting remoteTestcase parameters 
class remoteTestcase 
{ 
public $id; 
public $name; 
public $description; 
public $comments; 
public $automated; 
public $externalId; 
public $priority; 
public $tag; 
#customProperties is not used in this example but required to fulfil soap request 
public $customProperties; 
} 
// remoteRepositryTreeTestcase class for setting remoteRepositoryTreeTestcase parameters 
class remoteRepositoryTreeTestcase 
{ 
public $remoteRepositoryId; 
public $testSteps; 
public $testcase; 
public $original; 
} 
// createNewTestcase class for setting createNewTestcase parameters 
class createNewTestcase 
{ 
public $remoteRepositoryTreeTestcase; 
public $token; 
} 

// URL for your WSDL file 
$url = ("http://localhost:81/flex/services/soap/zephyrsoapservice-v1?wsdl"); 
// Options to customize your SoapClient 
$options = array("exceptions"=> 1, 'soap_version'=> SOAP_1_1, 'trace'=> 1); 

// Initialized login parameters 
// username and password come from corresponing Zephyhr user 
$login_params = new login; 
$login_params->username = "test.manager"; 
$login_params->password = "test.manager"; 

// Search criteria to look for all repository trees//phases in release 2 
$sc = new remoteCriteria; 
$sc->searchName = "releaseId"; 
$sc->searchOperation = "EQUALS"; 
$sc->searchValue = "2"; 
// Search criteria to additionally look for all trees//phases named DevZone (in the above release 2) 
$sc2 = new remoteCriteria; 
$sc2->searchName = "name"; 
$sc2->searchOperation = "EQUALS"; 
$sc2->searchValue = "DevZone"; 

// Initialized getTestCaseTreesByCriteria parameters 
$tctbc_params = new getTestCaseTreesByCriteria; 
$tctbc_params->searchCriterias = array($sc, $sc2); 
$tctbc_params->returnAllDataFlag = False; 

// Create the SoapClient instance using $url variable 
// Try/catch for fault detection 
try { 
$client = new SoapClient($url, $options); 
} catch (SoapFault $E) { 
echo "Exception Error!\n"; 
echo $E->faultstring; 
} 

// Echo for client confirmation 
echo "Client Established. Running Login.\n"; 

// Call to WSDL method login which takes in $login_params, resulting token is saved in $session 
$return = $client->login($login_params); 
$session = $return->return; 

// Echo for login confirmation 
echo "The token for this session is: "; 
echo ($session); 
echo "\n\n"; 

// Finished initialization of $pbc_params from above, to add session token 
$tctbc_params->token = $session; 

// Call to WSDL method getProjectsByCriteria which takes in $pbc_params, returns all projects to $projects 
$tree = $client->getTestCaseTreesByCriteria($tctbc_params); 

foreach ($tree as $record) 
{ 
// saving id and releaseId in loop since there will only be 1 return in this sample 
$id = $record->id; 
$releaseId = $record->releaseId; 

// echo for getTestCaseTreesByCriteria confirmation 
echo "The name of the tree is: "; 
echo $record->name; 
echo "\n"; 
echo "The ID of the tree is: "; 
echo $id; 
echo "\n\n"; 
} 

// Initializing remoteTestcase and adding parameter information 
// releaseId taken from previously return testcase tree 
$testcase = new remoteTestcase; 
$testcase->name = "This Testcase was created via PHP API!"; 
$testcase->comments = "Created via API"; 
$testcase->automated = False; 
$testcase->externalId = "9999"; 
$testcase->priority = "1"; 
$testcase->tag = "API"; 
$testcase->releaseId = $releaseId; 

// Initializing remoteReopositoryTreeTestcase and adding parameter information 
// id taken from previously returned testcase tree 
$treeTestcase = new remoteRepositoryTreeTestcase; 
$treeTestcase->remoteRepositoryId = $id; 
$treeTestcase->testSteps = "<steps maxId=\"3\"><step id=\"1\" orderId=\"1\" detail=\"Test Step 1\" data=\"Test Data \" result=\"Excepted Results \" /> <step id=\"2\" orderId=\"2\" detail=\"Test Step T2 \" data=\"\" result=\"\" /> <step id=\"3\" orderId=\"3\" detail=\"Test Step T3 -Test\" data=\"\" result=\"\" /></steps>"; 
$treeTestcase->testcase = $testcase; 
$treeTestcase->original = True; 

// Initializing parameter information for creating new testcase 
$cntc_params = new createNewTestcase; 
$cntc_params->remoteRepositoryTreeTestcase = $treeTestcase; 
$cntc_params->token = $session; 

// Creating the new TC here 
$response = $client->createNewTestcase($cntc_params); 

// Returns identification information from returned list 
foreach ($response->return as $returned) 
{ 
echo "The key is: "; 
echo $returned->key; 
echo "\n"; 
echo "The value is: "; 
echo $returned->value; 
echo "\n"; 
} 

// Logout process 
$client->logout($session); 
echo "The session has logged out!"; 
?>