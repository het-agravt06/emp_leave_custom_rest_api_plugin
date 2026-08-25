<?php 
/*
plugin name: emp_leave_custom_rest_api_plugin
description: for creating this plugin for the handle the employees leave
version: 0.1.0
author: Het Agravat
*/

add_action("rest_api_init","emp_leave_custom_rest_api");

function emp_leave_custom_rest_api(){
    
    //for the check get method in postman select the GET method with this url http://localhost:10004/wp-json/leave/v1/requests with none and click send button
    register_rest_route(
        'leave/v1',
        '/requests',
        array(
            'methods' => 'GET',
            'callback' => 'get_employee_leaves'
        )
    );
    
    //for the check POST method in postman select the POST method with this url http://localhost:10004/wp-json/leave/v1/requests with body->raw->json and click send button
    register_rest_route(
        'leave/v1','/requests',
        array(
            'methods' => 'POST',
            'callback' => 'create_employee_leave'
        )
    );
}


function get_employee_leaves(){
    $leaves = array(
        array
        (
            'id' => 1,
            'name' => 'test',
            'leave_type' => 'casual'
        ),
        array
        (
            'id' => 2,
            'name' => 'test2',
            'leave_type' => 'emergency leave'
        )
    );

    return $leaves;
}


function create_employee_leave(WP_REST_Request $request){
    $data = $request->get_json_params();

    return array(
        'success' => true,
        'description' => 'leave request recived successfully',
        'data' => $data,
    );
}

?>