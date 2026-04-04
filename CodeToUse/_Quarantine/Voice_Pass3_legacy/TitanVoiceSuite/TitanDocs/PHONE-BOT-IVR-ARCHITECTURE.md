# Phone Bot IVR Architecture

This pass adds business-hours routing, queue/callback offers, voicemail capture, call logging, callback scheduling, and menu endpoints.

## Components
- BusinessHoursService
- CallRouter
- IvrService
- QueueService
- VoicemailService
- CallbackService
- CallTransferService

## Routes
- api.v2.chatbot.voice.menu
- api.v2.chatbot.voice.voicemail
- api.v2.chatbot.voice.callback

## Storage
- ext_chatbot_call_logs
- ext_chatbot_callback_schedules
- ext_chatbot_business_hours
