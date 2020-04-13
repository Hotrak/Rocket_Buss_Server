@component('mail::message')
# Здравствуйте ,

The body of your message.

@component('mail::button', ['url' => 'http://localhost:8080/admin/schedule'])
Текст на кнопке
@endcomponent

Спасибо,<br>
{{ config('app.name') }}
@endcomponent
