Send Bulk SMS
Send SMS through your application by making a HTTP POST request to the following endpoints:

Endpoints
Live: https://api.africastalking.com/version1/messaging/bulk
Sandbox: https://api.sandbox.africastalking.com/version1/messaging/bulk (coming soon)
Request parameters
In addition to the standard request headers, this endpoint also accepts json requests. The body of the request should contain the following fields:

Parameter
username String Required
Your Africa’s Talking application username.
phoneNumbers String Required
A list of recipients’ phone numbers.
message String Required
The message to be sent.
senderId String Required
Your registered short code or alphanumeric
enqueue Integer Optional
This is used for Bulk SMS clients that would like to deliver as many messages to the API before waiting for an acknowledgement from the Telcos. Possible values are 1 to enable and 0 to disable. If enabled, the API will store the messages in a queue and send them out asynchronously after responding to the request. The default value is 1
API response
The body of the response will be a JSON object containing the following fields:

Parameter
SMSMessageData Map
A Map detailing the eventual result of the sms request. It contains the following fields:
Message String: A summary of the total number of recipients the sms was sent to and the total cost incurred.
Recipients List: A list of recipients included in the original request. Each recipient is a Map with the following fields:
statusCode Integer: This corresponds to the status of the request. Possible values are:
100: Processed
101: Sent
102: Queued
401: RiskHold
402: InvalidSenderId
403: InvalidPhoneNumber
404: UnsupportedNumberType
405: InsufficientBalance
406: UserInBlacklist
407: CouldNotRoute
409: DoNotDisturbRejection
500: InternalServerError
501: GatewayError
502: RejectedByGateway
number String: The recipient’s phone number
cost String: Amount incurred to send this sms. The format of this string is: (3-digit Currency Code)(space)(Decimal Value) e.g KES 1.00
status String: A string indicating whether the sms was sent to this recipient or not. This does not indicate the delivery status of the sms to this recipient.
messageId String: The messageId received when the sms was sent.
Send SMS to Hashed Number
For Safaricom in Kenya, you can send SMSs using hashed phone numbers to the same API endpoint.

Request parameters
In addition to the standard request headers, the body of the request should contain the following fields:

Parameter
username String Required
Your Africa’s Talking application username.
message String Required
The message to be sent.
maskedNumber String Required
The string of the hashed number to be sent to.
telco String Required
The service provider.
phoneNumbers String Required
This value must be a blank list, i.e, []
senderId String Optional
Your registered short code or alphanumeric
API response
The body of the response will be a JSON object containing the following fields:

Parameter
SMSMessageData Map
A Map detailing the eventual result of the sms request. It contains the following fields:
Message String: A summary of the total number of recipients the sms was sent to and the total cost incurred.
Recipients List: A list of recipients included in the original request. Each recipient is a Map with the following fields:
statusCode Integer: This corresponds to the status of the request. Possible values are:
100: Processed
101: Sent
102: Queued
401: RiskHold
402: InvalidSenderId
403: InvalidPhoneNumber
404: UnsupportedNumberType
405: InsufficientBalance
406: UserInBlacklist
407: CouldNotRoute
409: DoNotDisturbRejection
500: InternalServerError
501: GatewayError
502: RejectedByGateway
number String: The recipient’s phone number
cost String: Amount incurred to send this sms. The format of this string is: (3-digit Currency Code)(space)(Decimal Value) e.g KES 1.00
status String: A string indicating whether the sms was sent to this recipient or not. This does not indicate the delivery status of the sms to this recipient.
messageId String: The messageId received when the sms was sent.
Explore Tutorials
To get started ASAP, try out any of these SMS tutorials in our interactive learning environment. You can also explore all SMS tutorials.

Messaging 101 - Sending an SMS
In this short tutorial, you'll learn how to send an SMS with the Africa's Talking API.

"curl -X POST \
    https://api.africastalking.com/version1/messaging/bulk \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -H 'apiKey: MyAppApiKey' \
    -d '{
    "username": "username",
    "message": "This is a sample message.",
    "senderId": "ABC",
    "phoneNumbers": [
        "+254711XXXYYY",
        "+254711YYYZZZ"
    ]
}'

curl -X POST \
    https://api.africastalking.com/version1/messaging/bulk \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -H 'apiKey: MyAppApiKey' \
    -d '{
    "username": "username",
    "message": "This is a sample message.",
    "maskedNumber": "XYZ",
    "telco": "Safaricom",
    "senderId": "ABC",
    "phoneNumbers": []
}'"

example response "{
    "SMSMessageData": {
        "Message": "Sent to 1/1 Total Cost: KES 0.8000",
        "Recipients": [{
            "statusCode": 101,
            "number": "+254711XXXYYY",
            "status": "Success",
            "cost": "KES 0.8000",
            "messageId": "ATPid_SampleTxnId123"
        }]
    }
}" 
