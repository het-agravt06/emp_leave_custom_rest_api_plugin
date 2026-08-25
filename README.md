# emp_leave_custom_rest_api

#for the check in postman

#get method 
//for the check get method in postman select the GET method with this url http://localhost:10004/wp-json/leave/v1/requests with none and click send button

#post method 
//for the check POST method in postman select the POST method with this url http://localhost:10004/wp-json/leave/v1/requests with body->raw->json and click send button

#id specific get deatails
//http://localhost:10004/wp-json/leave/v1/requests/2 (2 is a id)

#put method check in postman 
http://localhost:10004/wp-json/leave/v1/requests/1 body->raw->json and click send button

{
    "name": "Het",
    "leave_type": "casual",
    "from_date": "2026-08-29",
    "to_date": "2026-09-02",
    "reason": "Going to hometown",
    "status": "approved"
}


#patch method check in postman
http://localhost:10004/wp-json/leave/v1/requests/1 with body->raw->json and click send button (same as PUT but for partial update)

{
    "status": "rejected"
}

#delete method in postman
http://localhost:10004/wp-json/leave/v1/requests/2 and click send button

