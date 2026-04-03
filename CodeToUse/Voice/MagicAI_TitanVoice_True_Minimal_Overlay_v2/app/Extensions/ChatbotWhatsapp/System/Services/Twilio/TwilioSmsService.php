<?php

namespace App\Extensions\ChatbotWhatsapp\System\Services\Twillio;

use App\Extensions\Chatbot\System\Models\ChatbotChannel;
use Exception;
use Twilio\Rest\Client;

class TwilioSmsService
{
    public ChatbotChannel $chatbotChannel;

    public ?string $twilioPhone = null;

    public function sendText($message, $receiver)
    {
        $client = $this->client();

        $from = data_get($this->chatbotChannel['credentials'], 'sms_phone');

        try {
            $receiver = $this->receiverCheck($receiver);

            $message = $client->messages->create(
                $receiver,
                [
                    'from' => $from,
                    'body' => $message,
                ]
            );

            return [
                'properties' => $this->properties($message),
                'message'    => trans('SMS sent'),
                'status'     => true,
            ];
        } catch (Exception $exception) {
            return [
                'message' => $exception->getMessage(),
                'status'  => false,
            ];
        }
    }

    public function receiverCheck(string $receiver): string
    {
        // Ensure phone number format (remove any existing +)
        $receiver = preg_replace('/[^0-9+]/', '', $receiver);
        
        if (!str_starts_with($receiver, '+')) {
            $receiver = '+' . $receiver;
        }

        return $receiver;
    }

    public function properties($message): array
    {
        return [
            'body'                => $message->__get('body'),
            'numSegments'         => $message->__get('numSegments'),
            'direction'           => $message->__get('direction'),
            'from'                => $message->__get('from'),
            'to'                  => $message->__get('to'),
            'dateUpdated'         => $message->__get('dateUpdated'),
            'price'               => $message->__get('price'),
            'errorMessage'        => $message->__get('errorMessage'),
            'uri'                 => $message->__get('uri'),
            'accountSid'          => $message->__get('accountSid'),
            'numMedia'            => $message->__get('numMedia'),
            'status'              => $message->__get('status'),
            'messagingServiceSid' => $message->__get('messagingServiceSid'),
            'sid'                 => $message->__get('sid'),
            'dateSent'            => $message->__get('dateSent'),
            'dateCreated'         => $message->__get('dateCreated'),
            'errorCode'           => $message->__get('errorCode'),
            'priceUnit'           => $message->__get('priceUnit'),
            'apiVersion'          => $message->__get('apiVersion'),
            'subresourceUris'     => $message->__get('subresourceUris'),
        ];
    }

    public function client(): Client
    {
        $username = data_get($this->chatbotChannel['credentials'], 'sms_sid');

        $password = data_get($this->chatbotChannel['credentials'], 'sms_token');

        $this->twilioPhone = data_get($this->chatbotChannel['credentials'], 'sms_phone');

        return new Client($username, $password);
    }

    public function getChatbotChannel(): ChatbotChannel
    {
        return $this->chatbotChannel;
    }

    public function setChatbotChannel(ChatbotChannel $chatbotChannel): self
    {
        $this->chatbotChannel = $chatbotChannel;

        return $this;
    }
}
