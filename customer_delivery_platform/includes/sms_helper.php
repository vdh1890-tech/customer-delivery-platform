<?php
// includes/sms_helper.php

/**
 * Send SMS Order Confirmation
 * 
 * Uses Twilio API to send a standard SMS.
 */
function sendSMSOrderConfirmation($customerPhone, $orderId, $amount, $items = [])
{
    // 1. CONFIGURATION
    $sid = "AC636185e1362100024eb8e21a176535cc";
    $token = "97df8d3e8490acbd4ce159da6d9249c7";
    $twilio_number = "+17754029220";

    // 2. Format Phone Number
    if (strpos($customerPhone, '+') === false) {
        $customerPhone = "+91" . $customerPhone;
    }
    $to_number = $customerPhone;

    // 3. Format Items String (e.g., "M-Sand x2, Gravel x1")
    $itemSummary = "";
    if (!empty($items)) {
        $parts = [];
        foreach ($items as $item) {
            $parts[] = $item['name'] . " x" . $item['quantity'];
        }
        $itemSummary = "\nItems: " . implode(", ", $parts);
    }

    // 4. Construct Message
    $messageBody = "KR BLUE METALS\nInvoice: #$orderId\nPrice: Rs.$amount$itemSummary\n\nThank you! Your order is confirmed regarding the details above.";

    // 4. Send Request (Using cURL)
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    $data = [
        'From' => $twilio_number,
        'To' => $to_number,
        'Body' => $messageBody
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || $http_code >= 400) {
        error_log("SMS Error ($http_code): " . ($error ?: $response));
        return false;
    }
    return true;
}
?>