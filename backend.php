<?php

header("Content-Type: application/json");

$data_file_path = "./data.json";

$api_response = array(
	'status' => 'error',
	'response' => 'nothing_returned'
);

if(file_exists($data_file_path)) {
    $data = file_get_contents($data_file_path);
    $data = json_decode($data, true);

	if(isset($_GET['get_storage'])) {
        // Gets a storages data
        $storage_id = $_GET['get_storage'];
        
        if(isset($data[$storage_id])) {
            $api_response['status'] = "ok";
            $api_response['response'] = json_encode($data[$storage_id]);
        }else{
            $api_response['status'] = "error";
            $api_response['response'] = "storage_not_found";
        }
    } elseif(isset($_GET['get_storages'])) {
        $api_response['status'] = "ok";
        $api_response['response'] = json_encode($data);
    } elseif(isset($_GET['set_storage'])) {
        // Sets a storages data or creates a new storage if ID doesn't already exist
        $storage_data = json_decode($_GET['set_storage'], true);

        if($storage_data !== null && $storage_data !== false) {
            if(isset($storage_data['name']) && isset($storage_data['description'])) {
                if(isset($storage_data['id'])) {
                    $data[$storage_data['id']] = [
                        'name' => $storage_data['name'],
                        'description' => $storage_data['description'],
                    ];

                    $api_response['status'] = "ok";
                    $api_response['response'] = "edited_storage";
                }else{
                    $new_storage_id = null;

                    // Generate new ID that isn't already existing
                    while($new_storage_id == null) {
                        $temp_id = uniqid();

                        if(!isset($data[$temp_id]))
                            $new_storage_id = $temp_id;
                    }

                    $data[$new_storage_id] = [
                        'name' => $storage_data['name'],
                        'description' => $storage_data['description'],
                    ];

                    $api_response['status'] = "ok";
                    $api_response['response'] = $new_storage_id;
                }
            }else{
                $api_response['status'] = "error";
                $api_response['response'] = "missing_parameters";
            }
        }else{
            $api_response['status'] = "error";
            $api_response['response'] = "invalid_parameters_not_json";
        }
    } elseif(isset($_GET['delete_storage'])) {
        // Deletes given storage
        $storage_id = $_GET['delete_storage'];

        if(isset($data[$storage_id])) {
            unset($data[$storage_id]);

            $api_response['status'] = "ok";
            $api_response['response'] = "deleted";
        }else{
            $api_response['status'] = "error";
            $api_response['response'] = "storage_not_found";
        }
	}else{
		$api_response['status'] = "error";
		$api_response['response'] = "invalid_action";
	}

    file_put_contents($data_file_path, json_encode($data, JSON_PRETTY_PRINT));
}else{
	$api_response['status'] = "error";
	$api_response['response'] = "data_file_is_missing";
}

echo(json_encode($api_response));

?>