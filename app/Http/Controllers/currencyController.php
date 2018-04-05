<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// Important: Include the GuzzleClient
use GuzzleHttp\Client;

class currencyController extends Controller
{
    /**
    * Index route
    *
    * @return Response
    */

    public function index()
    {
        // Retrieve information about the bitcoin currency
        $bitcoinInfo = $this->getCryptoCurrencyInformation("bitcoin","EUR");

        // About the Ethereum currency but in Euros instead of United States Dollar
        $ethereumInfo = $this->getCryptoCurrencyInformation("ethereum", "EUR");

        // And so on with more than 1010 cryptocurrencies ...

        // Return a view as response (default.blade.php)
		$id = Auth::id(); 
			$bitcoinValue = DB::table('_bitupdates')->where('id',$id)->get();
			$bitcoinValue = $bitcoinValue->toArray();
			$bitcoinValue = json_decode(json_encode((array) $bitcoinValue), true);
			//dd($bitcoinValue);
			$bitcoinValue=$bitcoinValue[0]["bitcoin_EUR"];
			//dd($bitcoinInfo);
			if(!$bitcoinValue)
			{
				$bitcoinValue =$bitcoinInfo["price_eur"];
				
			}

        return view("currency", [
            "bitcoin" => $bitcoinInfo,
            "ethereum" => $ethereumInfo,
			"bitcoinValue" => $bitcoinValue
			
        ]);
    }

    /**
     * Retrieves the complete information providen by the coinmarketcap API from a single currency.
     * By default returns only the value in USD.
     * 
     * WARNING: do not use this code in production, it's just to explain how the API works and how
     * can the information be retrieved. See step 3 for final implementation. 
     *
     * @param string $currencyId Identifier of the currency
     * @param string $convertCurrency
     * @see https://coinmarketcap.com/api/
     * @return mixed 
     */
     private function getCryptoCurrencyInformation($currencyId, $convertCurrency = "EUR"){
        // Create a new Guzzle Plain Client
        $client = new Client();

        // Define the Request URL of the API with the providen parameters
        $requestURL = "https://api.coinmarketcap.com/v1/ticker/$currencyId/?convert=$convertCurrency";

        // Execute the request
        $singleCurrencyRequest = $client->request('GET', $requestURL);
        
        // Obtain the body into an array format.
        $body = json_decode($singleCurrencyRequest->getBody() , true)[0];

        // If there were some error on the request, throw the exception
        if(array_key_exists("error" , $body)){
            throw $this->createNotFoundException(sprintf('Currency Information Request Error: $s', $body["error"]));
        }

        // Returns the array with information about the desired currency
        return $body;
    }
}
