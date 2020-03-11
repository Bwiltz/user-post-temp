<?php

class NSS_ArrayToTable
{
	public $html;
    function __construct($data, $attr = "")
    {
		if(empty($data[0]))
		return;
		
		$html = "<div class='grassblade_table'><table ".$attr.">";
		$html .= "<tr>";
		foreach($data[0] as $header_field => $value) {
			$html .= "<th>".__($header_field, "grassblade")."</th>";
		}
		$html .= "</tr>";
		
		foreach($data as $k => $row) {
			$tr_class = ($k % 2 == 1)? 'tr_odd':'tr_even';
			$html .= "<tr class='".$tr_class."'>";
			foreach($row as $field) {
				$html .= "<td>".$field."</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</table></div>";
		$this->html = $html;
	}
	function show() {
		echo $this->html;
	}
	function get() {
		return $this->html;
	}
}
