@component('mail::message')
# @lang('email.hello')@if(!empty($notifiableName)){{ ' ' . $notifiableName }}@endif! <br>
# @lang('modules.service jobs.newTask')

@component('mail::text', ['text' => $content])

@endcomponent


@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
