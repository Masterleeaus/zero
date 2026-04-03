<div class="grid">
    @foreach (['telegram' => 'Telegram', 'whatsapp' => 'WhatsApp', 'messenger' => 'Messenger', 'voice' => 'Voice'] as $slug => $label)
        @php $channelModel = $chatbot->channels->firstWhere('channel', $slug); @endphp
        <div class="card">
            <strong>{{ $label }}</strong>
            <div class="muted" style="margin:8px 0 12px">
                {{ $channelModel ? 'Connected' : 'Ready to connect' }}
                @if($channelModel?->connected_at)
                    • {{ $channelModel->connected_at->diffForHumans() }}
                @endif
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ route('dashboard.chatbot.overlay.command.webhook-url', [$chatbot, $slug]) }}">Webhook URL</a>
                <span class="muted">{{ $channelModel?->webhooks?->count() ?? 0 }} payloads</span>
            </div>
        </div>
    @endforeach
</div>
