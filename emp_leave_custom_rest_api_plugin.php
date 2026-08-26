<?php 

//when the plugin is activated then activation hook are run and call create_employee_leave_table this function
register_activation_hook(__FILE__, 'create_employee_leave_table');

/*
plugin name: emp_leave_custom_rest_api_plugin
description: for creating this plugin for the handle the employees leave
version: 0.1.1
author: Het Agravat
*/

//creating a database 
function create_employee_leave_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'employee_leaves';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        employee_name VARCHAR(100) NOT NULL,
        employee_email VARCHAR(100) NOT NULL,
        leave_type VARCHAR(50) NOT NULL,
        from_date DATE NOT NULL,
        to_date DATE NOT NULL,
        reason TEXT,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) {$wpdb->get_charset_collate()};";

    dbDelta($sql);
}

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


    //this route for Show the leave only for the id's mentioned.

    register_rest_route(
        'leave/v1','/requests/(?P<id>[0-9]+)',
        array
        (
            'methods' => 'GET',
            'callback' => 'get_leave_data'
        )
    );

    //for the check PUT method in postman select the PUT method with this url http://localhost:10004/wp-json/leave/v1/requests/1 with body->raw->json and click send button

    register_rest_route(
        'leave/v1','/requests/(?P<id>[0-9]+)',
        array
        (
            'methods' => 'PUT',
            'callback' => 'update_leave_data'
        )
    );

    //for the check PATCH method in postman select the PATCH method with this url http://localhost:10004/wp-json/leave/v1/requests/1 with body->raw->json and click send button (same as PUT but for partial update)

    register_rest_route(
        'leave/v1','/requests/(?P<id>[0-9]+)',
        array
        (
            'methods' => 'PATCH',
            'callback' => 'update_leave_data'
        )
    );

    //for the check DELETE method in postman select the DELETE method with this url http://localhost:10004/wp-json/leave/v1/requests/1 and click send button

    register_rest_route(
        'leave/v1','/requests/(?P<id>[0-9]+)',
        array
        (
            'methods' => 'DELETE',
            'callback' => 'delete_leave_data'
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


//request specific function like id specific i mean Show the leave only for the id's mentioned.
function get_leave_data(WP_REST_Request $request){
    $id = $request->get_param('id');
    return array(
        'id' => $id,
        'message' => 'Leave found'
    );
}


//update function for update the leave data of the specific id mentioned (works for both PUT and PATCH method).
function update_leave_data(WP_REST_Request $request){
    $id = $request->get_param('id');
    $data = $request->get_json_params();

    return array(
        'success' => true,
        'id' => $id,
        'description' => 'leave request updated successfully',
        'data' => $data,
    );
}


//delete function for delete the leave data of the specific id mentioned.
function delete_leave_data(WP_REST_Request $request){
    $id = $request->get_param('id');

    return array(
        'success' => true,
        'id' => $id,
        'description' => 'leave request deleted successfully',
    );
}


?>