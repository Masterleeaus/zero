<?php

return {
    'module': 'sms',
    'capabilities': [
        {
            'key': 'sms.help.explain_page',
            'label': 'Sms: Explain this page',
            'risk': 'low',
            'requires': [],
            'handler': 'titanzero.intent.explain_page',
            'voice_phrases': [
                'what is this page',
                'explain this',
                'help me'
            ]
        },
        {
            'key': 'sms.sms-setting.send_test_message',
            'label': 'Sms: Send Test Message',
            'risk': 'low',
            'requires': [],
            'handler': 'sms-setting.send_test_message',
            'voice_phrases': [
                'send test message'
            ]
        },
        {
            'key': 'sms.sms-setting.test_message',
            'label': 'Sms: Test Message',
            'risk': 'low',
            'requires': [],
            'handler': 'sms-setting.test_message',
            'voice_phrases': [
                'test message'
            ]
        }
    ],
    'go_enabled': true,
    'zero_enabled': true
};
