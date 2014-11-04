<?php
/**
 * Calculate a quantity by counting how many rows there are within a Gravity Forms List Field
 * Replace "4" below with your form ID
 */
add_filter("gform_pre_render_4","populate_quantity");
function populate_quantity($form){

	// Set defaults
	$quantity = 0;
	$listdata = array();
	$pages = 1;

	// Get page information within Form
	$current_page = GFFormDisplay::get_current_page($form["id"]);
	$pagination = $form["pagination"];

	// Check if it's a multi-page form
	if (count($pagination["pages"]) > 1) {
		$pages = count($pagination["pages"]);
	}

	// Check if we are on the very last page of the form
	if ($_POST && $current_page == $pages) {

		// Dump POST into a variable so we can use it
		$postdata = $_POST;

		// Loop through fields to collect neccesary data to be used later
		foreach ($form["fields"] as &$field) {

			// Find List field (field_id = 6)
			if ($field["type"] == "list") {

				$field_id = $field["id"];

				// Count how many columns there are in the list field
				$table_columns = $field["choices"];
				$table_columns = count($table_columns);

				// Assign "list field" POST data containing all table rows as a single array
				// "list field" is a Gravity Forms field type
				$listdata = $postdata["input_" . $field_id];

				// Split single array into countable "rows" by splitting it every n elements
				// n represents the number of table columns within the list field as defined above
				$chunks = array_chunk($listdata, $table_columns);

				// Count how many rows are returned. This is now the quantity!
				$quantity = count($chunks);
			}

		}


		// Loop through the data again but this time use our new values
		foreach ($form["fields"] as &$field) {

			// Find Product field (field_id = 14)
			if ($field["type"] == "product") {

				// Display the quantity to the user as a description
				$field["description"] = "x" . $quantity . " " . $field["label"] . "(s) counted.";

			}

			// Find Quantity field (field_id = 15)
			if ($field["type"] == "quantity") {

				// Assign quantity field content to our updated quantity count and ensure it's an Integer!
				$field["defaultValue"] = intval($quantity);

			}

		}

	}

	// Return altered form so no changes are displayed
	return $form;
}