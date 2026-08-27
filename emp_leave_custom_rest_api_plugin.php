<?php
/*
    plugin name: emp_leave_custom_rest_api_plugin
    description: for creating this plugin for the handle the employees leave
    version: 0.1.1
    author: Het Agravat
    */

//when the plugin is activated then activation hook are run and call create_employee_leave_table this function
register_activation_hook(__FILE__, 'create_employee_leave_table');



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

add_action("rest_api_init", "emp_leave_custom_rest_api");

function emp_leave_custom_rest_api()
{

    //for the check get method in postman select the GET method with this url http://localhost:10004/wp-json/leave/v1/requests with none and click send button
    register_rest_route(
        'leave/v1',
        '/requests',
        array(
            'methods' => 'GET',
            'callback' => 'get_employee_leaves',
            'permission_callback' => '__return_true'
        )
    );

    //for the check POST method in postman select the POST method with this url http://localhost:10004/wp-json/leave/v1/requests with body->raw->json and click send button
    register_rest_route(
        'leave/v1',
        '/requests',
        array(
            'methods' => 'POST',
            'callback' => 'create_employee_leave',
            'permission_callback' => '__return_true'
        )
    );


    //this route for Show the leave only for the id's mentioned.

    register_rest_route(
        'leave/v1',
        '/requests/(?P<id>[0-9]+)',
        array(
            'methods' => 'GET',
            'callback' => 'get_leave_data',
            'permission_callback' => '__return_true'
        )
    );

    //for the check PUT method in postman select the PUT method with this url http://localhost:10004/wp-json/leave/v1/requests/1 with body->raw->json and click send button

    register_rest_route(
        'leave/v1',
        '/requests/(?P<id>[0-9]+)',
        array(
            'methods' => 'PUT',
            'callback' => 'update_leave_data',
            'permission_callback' => '__return_true'
        )
    );

    //for the check PATCH method in postman select the PATCH method with this url http://localhost:10004/wp-json/leave/v1/requests/1 with body->raw->json and click send button (same as PUT but for partial update)

    register_rest_route(
        'leave/v1',
        '/requests/(?P<id>[0-9]+)',
        array(
            'methods' => 'PATCH',
            'callback' => 'update_leave_data',
            'permission_callback' => '__return_true'
        )
    );

    //for the check DELETE method in postman select the DELETE method with this url http://localhost:10004/wp-json/leave/v1/requests/1 and click send button

    register_rest_route(
        'leave/v1',
        '/requests/(?P<id>[0-9]+)',
        array(
            'methods' => 'DELETE',
            'callback' => 'delete_leave_data',
            'permission_callback' => '__return_true'
        )
    );
}


function get_employee_leaves()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'employee_leaves';

    $leaves = $wpdb->get_results(
        "SELECT * FROM $table_name",
        ARRAY_A
    );

    return $leaves;
}


function create_employee_leave(WP_REST_Request $request)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'employee_leaves';

    $data = $request->get_json_params();

    // Required fields
    $required_fields = array(
        'employee_name',
        'employee_email',
        'leave_type',
        'from_date',
        'to_date',
        'reason'
    );

    $missing_fields = array();

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $missing_fields[] = $field;
        }
    }

    // Check required fields
    if (!empty($missing_fields)) {
        return new WP_Error(
            'missing_fields',
            'Required fields are missing.',
            array(
                'status' => 400,
                'fields' => $missing_fields
            )
        );
    }

    // Check email
    if (!is_email($data['employee_email'])) {
        return new WP_Error(
            'invalid_email',
            'Please provide a valid email address.',
            array(
                'status' => 400
            )
        );
    }

    // Insert into database
    $result = $wpdb->insert(
        $table_name,
        array(
            'employee_name'  => $data['employee_name'],
            'employee_email' => $data['employee_email'],
            'leave_type'     => $data['leave_type'],
            'from_date'      => $data['from_date'],
            'to_date'        => $data['to_date'],
            'reason'         => $data['reason'],
            'status'         => 'pending',
            'created_at'     => current_time('mysql'),
        )
    );

    // Database error
    if ($result === false) {
        return new WP_Error(
            'database_error',
            'Unable to create leave request.',
            array(
                'status' => 500
            )
        );
    }

    return array(
        'success' => true,
        'message' => 'Leave request created successfully',
        'id'      => $wpdb->insert_id,
        'data'    => $data,
    );
}


//request specific function like id specific i mean Show the leave only for the id's mentioned.
function get_leave_data(WP_REST_Request $request)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'employee_leaves';

    $id = $request->get_param('id');

    $leave = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ),
        ARRAY_A
    );
    if (!$leave) {
        return new WP_Error(
            'leave_not_found',
            'Leave request not found.',
            array(
                'status' => 404
            )
        );
    }
    return $leave;
}


//update function for update the leave data of the specific id mentioned (works for both PUT and PATCH method).
function update_leave_data(WP_REST_Request $request)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'employee_leaves';

    $id = $request->get_param('id');

    $data = $request->get_json_params();

    // Check whether leave exists
    $leave = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM $table_name WHERE id = %d",
            $id
        )
    );

    if (!$leave) {
        return new WP_Error(
            'leave_not_found',
            'Leave request not found.',
            array(
                'status' => 404
            )
        );
    }

    $wpdb->update(
        $table_name,
        array(
            'status' => $data['status'],
        ),
        array(
            'id' => $id,
        )
    );

    return array(
        'success' => true,
        'message' => 'Leave status updated successfully',
        'id'      => $id,
        'status'  => $data['status'],
    );
}


//delete function for delete the leave data of the specific id mentioned.
function delete_leave_data(WP_REST_Request $request)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'employee_leaves';

    $id = $request->get_param('id');

    $deleted = $wpdb->delete(
        $table_name,
        array(
            'id' => $id,
        ),
        array(
            '%d',
        )
    );

    if ($deleted === 0) {
        return new WP_Error(
            'leave_not_found',
            'Leave request not found.',
            array(
                'status' => 404
            )
        );
    }

    if ($deleted === false) {
        return new WP_Error(
            'database_error',
            'Unable to delete leave request.',
            array(
                'status' => 500
            )
        );
    }
    return array(
        'success' => $deleted !== false,
        'message' => 'Leave request deleted successfully',
        'id'      => $id,
    );
}



//html form 
function employee_leave_form()
{
    ob_start();
?>
    <div class="employee-leave-form">

        <h2>Employee Leave Request</h2>

        <form id="employee-leave-form">

            <div>
                <label for="employee_name">Employee Name</label>
                <input
                    type="text"
                    id="employee_name"
                    name="employee_name"
                    required>
            </div>
            <br>
            <div>
                <label for="employee_email">Employee Email</label>
                <input
                    type="email"
                    id="employee_email"
                    name="employee_email"
                    required>
            </div>
            <br>
            <div>
                <label for="leave_type">Leave Type</label>
                <select
                    id="leave_type"
                    name="leave_type"
                    required>
                    <option value="">Select Leave Type</option>
                    <option value="casual">Casual Leave</option>
                    <option value="sick">Sick Leave</option>
                    <option value="emergency">Emergency Leave</option>
                </select>
            </div>
            <br>
            <div>
                <label for="from_date">From Date</label>
                <input
                    type="date"
                    id="from_date"
                    name="from_date"
                    required>
            </div>
            <br>
            <div>
                <label for="to_date">To Date</label>
                <input
                    type="date"
                    id="to_date"
                    name="to_date"
                    required>
            </div>
            <br>
            <div>
                <label for="reason">Reason</label>
                <textarea
                    id="reason"
                    name="reason"
                    required></textarea>
            </div>
            <br>
            <button type="submit">
                Submit Leave
            </button>

        </form>

        <div id="leave-response"></div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('employee-leave-form').addEventListener('submit', function(event) {

            event.preventDefault();

            const employeeName = document.getElementById('employee_name').value;
            const employeeEmail = document.getElementById('employee_email').value;
            const leaveType = document.getElementById('leave_type').value;
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;
            const reason = document.getElementById('reason').value;

            const data = {
                employee_name: employeeName,
                employee_email: employeeEmail,
                leave_type: leaveType,
                from_date: fromDate,
                to_date: toDate,
                reason: reason
            };

            fetch('/wp-json/leave/v1/requests', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {

                    console.log('API Response:', result);

                    if (result.success) {

                        Swal.fire({
                            title: "Leave Submitted!",
                            text: "Your leave request ID is " + result.id,
                            icon: "success",
                            draggable: true
                        });
                    } else {

                        Swal.fire({
                            title: "Error!",
                            text: result.message || "Something went wrong.",
                            icon: "error",
                            draggable: true
                        })
                    }
                })
                .catch(error => {

                    console.error('Error:', error);

                    Swal.fire({
                        title: "Error!",
                        text: "Unable to connect to the server.",
                        icon: "error",
                        draggable: true
                    });

                });
        })
    </script>

<?php

    return ob_get_clean();
}

add_shortcode('employee_leave_form', 'employee_leave_form');
