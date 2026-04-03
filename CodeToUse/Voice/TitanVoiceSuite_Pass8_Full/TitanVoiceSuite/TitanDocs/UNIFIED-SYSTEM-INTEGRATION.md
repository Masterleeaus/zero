# Unified System Integration

Pass 3 connects the Twilio voice entrypoint to a call router before normal transcript handling. During closed hours the router offers voicemail or callback. During long waits it offers queue-or-callback logic. Otherwise it falls into the existing voice-command and AI conversation flow.
