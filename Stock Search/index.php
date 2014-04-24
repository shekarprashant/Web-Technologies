<?php 
	Header("Content-type:text/xml;charset=utf-8");
	function isBlank($data)
	{
		if (trim($data) != "")
			return false;
		return true;
	}

	function remChars($data)
	{
		$data = trim($data);
		$data = htmlspecialchars($data, ENT_QUOTES);
		return $data;
	}

	function getNumber($data, $decimals)
	{
		$data = remChars($data);

		if (is_numeric($data))
			return number_format($data, $decimals);
		return "";
	}

	function getChangeType($data)
	{
		$data = remChars($data);
		if (strlen($data) == 0)
			return "+";			
				
		if ($data[0] == "-")
			return "-";

		return "+";
	}

	function getChange($data)
	{
		$data = remChars($data);
		if (strlen($data) == 0)
			return "";			

		if ($data[0] == "-" || $data[0] == "+")
			$data = substr($data,1);
			
		//$data = number_format($data, 2);
		return ($data);
		
	}
	
	function getChangeinPercent($data)
	{
		$data = remChars($data);
		$len = strlen($data);			
		if ($len == 0)
			return "";			

		if ($data[0] == "-" || $data[0] == "+")
		{
			$data = substr($data,1);
			$len = $len - 1;
		}

		if ($data[$len - 1] == "%")
			$data = substr($data,0,-1);	
			
		//$data = number_format($data, 2);
		return ($data."%");
		
	}

	function getMarketCap($data)
	{
		$data = remChars($data);
		$len = strlen($data);
		if ($len == 0)
			return "";
		
		$last = $data[$len - 1];
		if (!is_numeric($last))
			$data = substr($data,0,-1);

		return (getNumber($data,1).$last);
		
	}
	
	function isUnavailable($data)
	{
		$UnavailableString = "Yahoo! Finance: RSS feed not found";
		$data = remChars($data);
		if (strcasecmp($data, $UnavailableString) == 0)
			return true;
		return false;
	}

	function array_as_xml_string($arr) 
	{
		if(!empty($arr)) 
		{ 
			$xml_output = "";
			foreach($arr as $k=>$v) 
			{
				$xml_output = $xml_output."<".$k . ">";
				
				if(is_array($v)) 
					$xml_output = $xml_output.array_as_xml_string($v);

				else
					$xml_output = $xml_output . $v;
					
				$xml_output = $xml_output."</".$k . ">\n";
			}
		}
		else 
			return "";
		
		return $xml_output;

	}
	
	/*PHP Code Start*/
	$error_xml = "<result>Symbol not found</result>";
	$xml_output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	
	if (!isset($_GET['symbol']) || isBlank($_GET['symbol']))
		$xml_output = $xml_output.$error_xml;

	else
	{ 
		$company = urlencode(remChars($_GET['symbol']));
		$url_stock_info = "http://query.yahooapis.com/v1/public/yql?q=Select%20Name%2C%20Symbol%2C%20LastTradePriceOnly%2C%20Change%2C%20ChangeinPercent%2C%20PreviousClose%2C%20DaysLow%2C%20DaysHigh%2C%20Open%2C%20YearLow%2C%20YearHigh%2C%20Bid%2C%20Ask%2C%20AverageDailyVolume%2C%20OneyrTargetPrice%2C%20MarketCapitalization%2C%20Volume%2C%20Open%2C%20YearLow%20from%20yahoo.finance.quotes%20where%20symbol%3D%22".$company."%22&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
		$url_news = "http://feeds.finance.yahoo.com/rss/2.0/headline?s=" . $company . "&region=US&lang=en-US";			
		$url_stock_chart = "http://chart.finance.yahoo.com/t?s=" . $company . "&amp;lang=en-US&amp;amp;width=300&amp;height=180";
		$xml_stock_info = new SimpleXMLElement($url_stock_info, NULL, TRUE);

		if (!isset($xml_stock_info->results->quote->Change) || isBlank($xml_stock_info->results->quote->Change))
			$xml_output = $xml_output.$error_xml;
			
		else 
		{
			$xml_results_quote = $xml_stock_info->results->quote;

			if (isset($xml_results_quote->Name))
				$xml_stock_info_data["Name"] = remChars($xml_results_quote->Name);
			else
				$xml_stock_info_data["Name"] = "";


			if (isset($xml_results_quote->Symbol))
				$xml_stock_info_data["Symbol"] = remChars($xml_results_quote->Symbol);
			else
				$xml_stock_info_data["Symbol"] = "";

			
			if (isset($xml_results_quote->Change))
			{
				$xml_stock_info_quote["ChangeType"] = getChangeType($xml_results_quote->Change);	
				$xml_stock_info_quote["Change"] = getChange($xml_results_quote->Change);							
			}
			else
			{
				$xml_stock_info_quote["ChangeType"] = getChangeType("");
				$xml_stock_info_quote["Change"] = "";				
			}
			
			if (isset($xml_results_quote->ChangeinPercent))
				$xml_stock_info_quote["ChangeinPercent"] = getChangeinPercent($xml_results_quote->ChangeinPercent);
			else
				$xml_stock_info_quote["ChangeinPercent"] = "";
			
			if (isset($xml_results_quote->LastTradePriceOnly))
				$xml_stock_info_quote["LastTradePriceOnly"] = getNumber($xml_results_quote->LastTradePriceOnly, 2);
			else
				$xml_stock_info_quote["LastTradePriceOnly"] = "";


			if (isset($xml_results_quote->PreviousClose))
				$xml_stock_info_quote["PreviousClose"] = getNumber($xml_results_quote->PreviousClose, 2);
			else
				$xml_stock_info_quote["PreviousClose"] = "";
				
			if (isset($xml_results_quote->DaysLow))
				$xml_stock_info_quote["DaysLow"] = getNumber($xml_results_quote->DaysLow, 2);
			else
				$xml_stock_info_quote["DaysLow"] = "";
				
			if (isset($xml_results_quote->DaysHigh))
				$xml_stock_info_quote["DaysHigh"] = getNumber($xml_results_quote->DaysHigh, 2);
			else
				$xml_stock_info_quote["DaysHigh"] = "";

			if (isset($xml_results_quote->Open))
				$xml_stock_info_quote["Open"] = getNumber($xml_results_quote->Open, 2);
			else
				$xml_stock_info_quote["Open"] = "";

			if (isset($xml_results_quote->YearLow))
				$xml_stock_info_quote["YearLow"] = getNumber($xml_results_quote->YearLow, 2);
			else
				$xml_stock_info_quote["YearLow"] = "";
				
			if (isset($xml_results_quote->YearHigh))
				$xml_stock_info_quote["YearHigh"] = getNumber($xml_results_quote->YearHigh, 2);
			else
				$xml_stock_info_quote["YearHigh"] = "";
						
			if (isset($xml_results_quote->Bid))
				$xml_stock_info_quote["Bid"] = getNumber($xml_results_quote->Bid, 2);
			else
				$xml_stock_info_quote["Bid"] = "";

			if (isset($xml_results_quote->Volume))
				$xml_stock_info_quote["Volume"] = getNumber($xml_results_quote->Volume, 0);
			else
				$xml_stock_info_quote["Volume"] = "";

			if (isset($xml_results_quote->Ask))
				$xml_stock_info_quote["Ask"] = getNumber($xml_results_quote->Ask, 2);
			else
				$xml_stock_info_quote["Ask"] = "";
				
			if (isset($xml_results_quote->AverageDailyVolume))
				$xml_stock_info_quote["AverageDailyVolume"] = getNumber($xml_results_quote->AverageDailyVolume, 0);
			else
				$xml_stock_info_quote["AverageDailyVolume"] = "";

			if (isset($xml_results_quote->OneyrTargetPrice))
				$xml_stock_info_quote["OneYearTargetPrice"] = getNumber($xml_results_quote->OneyrTargetPrice, 2);
			else
				$xml_stock_info_quote["OneYearTargetPrice"] = "";


			if (isset($xml_results_quote->MarketCapitalization))
				$xml_stock_info_quote["MarketCapitalization"] = getMarketCap($xml_results_quote->MarketCapitalization);
			else
				$xml_stock_info_quote["MarketCapitalization"] = "";
				
			$xml_stock_info_data["Quote"] = $xml_stock_info_quote;			

			$xml_news = new SimpleXMLElement($url_news, NULL, TRUE);
			$news_error_xml = "News Unavailable";			
			$news_output = "";
			if (!isset($xml_news->channel->item[0]->title) || isBlank($xml_news->channel->item[0]->title) || isUnavailable($xml_news->channel->item[0]->title))
				$news_output = $news_error_xml;
				
			else 
			{
				foreach($xml_news->channel->item as $item)
				{
					if (isset($item))
					{
						if (isset($item->title) && !isBlank($item->title))
						{
							$xml_stock_info_news["Title"] = remChars($item->title);
							if (isset($item->link) && !isBlank($item->link))
								$xml_stock_info_news["Link"] = remChars($item->link);
							else
								$xml_stock_info_news["Link"] = "";
						
							$news_output = $news_output."<Item>".array_as_xml_string($xml_stock_info_news)."</Item>";
						}
						
					}
			
				}				
			}
			$xml_stock_info_data["News"] = $news_output;
			$xml_stock_info_data["StockChartImageURL"] = remChars($url_stock_chart);
			$xml_output = $xml_output."<result>".array_as_xml_string($xml_stock_info_data)."</result>";
		}
		
	}	
	
	echo $xml_output;?>
