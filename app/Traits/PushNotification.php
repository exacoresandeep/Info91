<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Google\Auth\ApplicationDefaultCredentials;

trait PushNotification
{
    /**
     * Send push notification to the device
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return mixed
     */
    public function sendNotification($token, $title, $body, $data = [])
    {
       
        $fcmUrl = 'https://fcm.googleapis.com/v1/projects/info91-7ab79/messages:send';  // Replace with your actual FCM project URL

        $notification = [
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
           // 'topic' => 'all',
             'token' => $token,
        ];
        

        try {
            // Make the HTTP request to send the notification
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),  // Getting the access token
                'Content-Type' => 'application/json',  // Correct Content-Type
            ])->post($fcmUrl, ['message' => $notification]);
            
            return $response->json();
        } catch (Exception $e) {
            // Log the error in case of failure
            Log::error("Error in push notification: " . $e->getMessage());
            return false;
        }
    }
    public function sendNotificationToMultipleDevices(array $deviceTokens, $title, $body, $data = [])
    {
        $fcmUrl = 'https://fcm.googleapis.com/v1/projects/info91-7ab79/messages:send'; // FCM v1 URL
    
        // Prepare the message payload
        $message = [
            'message' => [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,  // Custom data for your app
                'topic' => 'all',
                'tokens' => $deviceTokens,  // List of device tokens
            ]
        ];
    
        try {
            // Make the HTTP request to send the notification
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(), // FCM access token
                'Content-Type' => 'application/json',  // Proper Content-Type
            ])->post($fcmUrl, $message);  // Send the payload
    
            // Return the response as JSON
            return $response->json();
        } catch (Exception $e) {
            // Log the error in case of failure
            Log::error("Error in push notification: " . $e->getMessage());
            return false;
        }
    }
    



    



    /**
     * Get Firebase access token
     *
     * @return string|null
     */
    private function getAccessToken()
    {
        // Get the key path from config
        $keyPath = config('services.firebase.key_path');  // Ensure this is defined in your config/services.php

        // Set the Google application credentials environment variable
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $keyPath);

        // Define required scopes
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        try {
            // Get the credentials
            $credentials = ApplicationDefaultCredentials::getCredentials($scopes);

            // Fetch the auth token
            $token = $credentials->fetchAuthToken();

            return $token['access_token'] ?? null;  // Return access token or null if it fails
        } catch (Exception $e) {
            // Log the error if something goes wrong while fetching the token
            Log::error("Error fetching Firebase access token: " . $e->getMessage());
            return null;
        }
    }
}
