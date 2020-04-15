<?php

function seapi($credentials, $endpoint, $method = "GET", $item = "", $body = "", &$error = NULL, $timeout=180)
{
	$request = "{invalid}";
	$ch = curl_init();

	if (strtoupper($method) == "GET")
	{
		$request = "https://api.scaleengine.net/dev/v2/{$endpoint}/{$item}?{$body}";
		curl_setopt($ch, CURLOPT_URL, $request);
	}
	else
	{
		$request = "{https://api.scaleengine.net/dev/v2/{$endpoint}/";
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	}
	curl_setopt($ch, CURLOPT_USERPWD, $credentials);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	
	$response = curl_exec($ch); //Execute the API Call
	if (!$response)
	{
		$error = 'Failed to connect to ScaleEngine API: '.curl_error($ch);
		return (false);
	}
	$arr_response = json_decode($response, true);

	$arr_info = curl_getinfo($ch);
	if ($arr_info['http_code'] > 299)
	{
		$error = "API Error: {$response}";
		return (false);
	}

	if (isset($arr_response['data']))
	{
		$error = null;
		return $arr_response['data'];
	}
	else
	{
		$error = "Unknown API Error: {$response}";
		return (false);
	}
}
