<?php

namespace ACPClient;

use Illuminate\Support\Arr;

class RESTClientResponse
{
	/**
	 * Parse to array
	 */
	public function __construct($content)
	{
		$data = json_decode($content, true);

		// add to self
		if ($data) {
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}
